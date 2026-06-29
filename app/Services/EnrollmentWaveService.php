<?php

namespace App\Services;

use App\Helpers\RomanNumeralHelper;
use App\Models\AcademicYear;
use App\Models\EnrollmentWave;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class EnrollmentWaveService
{
    /**
     * Cari gelombang berstatus open dalam tahun ajaran active.
     */
    public function getOpenWaveForActiveYear(): ?EnrollmentWave
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        if (! $activeYear) {
            return null;
        }

        return EnrollmentWave::where('academic_year_id', $activeYear->id)
            ->where('status', 'open')
            ->first();
    }

    /**
     * Buat gelombang baru dengan status draft.
     * wave_number dihitung otomatis (max + 1).
     * Kuota awal diisi otomatis dari calculateInitialQuotas.
     */
    public function createWave(AcademicYear $year, array $data): EnrollmentWave
    {
        return DB::transaction(function () use ($year, $data) {
            $nextWaveNumber = (EnrollmentWave::where('academic_year_id', $year->id)->max('wave_number') ?? 0) + 1;

            $wave = EnrollmentWave::create([
                'academic_year_id' => $year->id,
                'name'             => $data['name'],
                'wave_number'      => $nextWaveNumber,
                'status'           => 'draft',
                'open_date'        => $data['open_date'] ?? null,
                'close_date'       => $data['close_date'] ?? null,
                'description'      => $data['description'] ?? null,
            ]);

            $quotas = $this->calculateInitialQuotas($year);

            foreach ($quotas as $majorId => $quota) {
                $wave->majors()->attach($majorId, ['quota' => $quota]);
            }

            return $wave;
        });
    }

    /**
     * Hitung kuota awal per jurusan:
     * Kuota_Total - jumlah siswa is_accepted=true pada gelombang sebelumnya.
     *
     * @return array<int, int>  [major_id => quota]
     */
    public function calculateInitialQuotas(AcademicYear $year): array
    {
        $majors = $year->majors()
            ->withPivot(['quota', 'is_active'])
            ->wherePivot('is_active', true)
            ->get();

        // Fallback: jika tidak ada yang is_active, ambil semua
        if ($majors->isEmpty()) {
            $majors = $year->majors()->withPivot(['quota', 'is_active'])->get();
        }

        $quotas = [];

        foreach ($majors as $major) {
            $totalQuota = (int) ($major->pivot->quota ?? 0);

            $accepted = Student::where('academic_year_id', $year->id)
                ->where('accepted_major_id', $major->id)
                ->where('is_accepted', true)
                ->count();

            $quotas[$major->id] = max(0, $totalQuota - $accepted);
        }

        return $quotas;
    }

    /**
     * Buka gelombang: validasi tidak ada gelombang lain yang open, lalu transisi ke open.
     */
    public function openWave(EnrollmentWave $wave): void
    {
        if (! $wave->canTransitionTo('open')) {
            throw new \InvalidArgumentException(
                "Transisi status dari {$wave->status} ke open tidak diizinkan."
            );
        }

        $existing = EnrollmentWave::where('academic_year_id', $wave->academic_year_id)
            ->where('status', 'open')
            ->where('id', '!=', $wave->id)
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException(
                "Gelombang {$existing->name} sedang open. Tutup gelombang tersebut terlebih dahulu."
            );
        }

        $wave->update(['status' => 'open']);
    }

    /**
     * Tutup gelombang: transisi ke closed.
     */
    public function closeWave(EnrollmentWave $wave): void
    {
        if (! $wave->canTransitionTo('closed')) {
            throw new \InvalidArgumentException(
                "Transisi status dari {$wave->status} ke closed tidak diizinkan."
            );
        }

        $wave->update(['status' => 'closed']);
    }

    /**
     * Umumkan hasil gelombang: transisi ke announced.
     */
    public function announceWave(EnrollmentWave $wave): void
    {
        if (! $wave->canTransitionTo('announced')) {
            throw new \InvalidArgumentException(
                "Transisi status dari {$wave->status} ke announced tidak diizinkan."
            );
        }

        $wave->update(['status' => 'announced']);
    }

    /**
     * Hapus gelombang: hanya diizinkan jika status draft.
     */
    public function deleteWave(EnrollmentWave $wave): void
    {
        if (! $wave->isDraft()) {
            throw new \InvalidArgumentException(
                'Hanya gelombang berstatus draft yang dapat dihapus.'
            );
        }

        $wave->delete();
    }

    /**
     * Perbarui kuota per jurusan: hanya diizinkan jika status draft.
     *
     * @param array<int, int> $quotas  [major_id => quota]
     */
    public function updateQuotas(EnrollmentWave $wave, array $quotas): void
    {
        if (! $wave->isDraft()) {
            throw new \InvalidArgumentException(
                'Kuota hanya dapat diubah sebelum gelombang dibuka.'
            );
        }

        foreach ($quotas as $majorId => $quota) {
            $wave->majors()->updateExistingPivot($majorId, ['quota' => $quota]);
        }
    }

    /**
     * Ambil kuota gelombang untuk jurusan tertentu.
     */
    public function getWaveQuota(EnrollmentWave $wave, int $majorId): int
    {
        $major = $wave->majors()->wherePivot('major_id', $majorId)->first();

        return $major ? (int) $major->pivot->quota : 0;
    }

    /**
     * Hitung jumlah siswa is_accepted=true pada gelombang dan jurusan tertentu.
     */
    public function getAcceptedCountInWave(EnrollmentWave $wave, int $majorId): int
    {
        return Student::where('enrollment_wave_id', $wave->id)
            ->where('accepted_major_id', $majorId)
            ->where('is_accepted', true)
            ->count();
    }

    /**
     * Generate nomor pendaftaran: SPMB-{end_year}-{Romawi}-{urutan 4 digit}.
     * Urutan dihitung per gelombang secara atomic.
     */
    public function generateRegistrationNumber(EnrollmentWave $wave): string
    {
        return DB::transaction(function () use ($wave) {
            $wave->loadMissing('academicYear');

            $lockName = sprintf('spmb_registration_wave_%d', $wave->id);
            if (! $this->acquireMySqlLock($lockName, 10)) {
                throw new \RuntimeException('Tidak dapat mengamankan nomor pendaftaran. Silakan coba lagi.');
            }

            try {
                $lockedWave = EnrollmentWave::whereKey($wave->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $endYear = $lockedWave->academicYear->end_year;
                $roman   = RomanNumeralHelper::toRoman($lockedWave->wave_number);
                $prefix  = "SPMB-{$endYear}-{$roman}-";

                $existingMax = Student::where('enrollment_wave_id', $lockedWave->id)
                    ->where('registration_number', 'like', $prefix . '%')
                    ->pluck('registration_number')
                    ->map(function (string $registrationNumber) use ($prefix) {
                        $sequence = substr($registrationNumber, strlen($prefix));

                        return ctype_digit($sequence) ? (int) $sequence : 0;
                    })
                    ->max() ?? 0;

                $sequence = $existingMax + 1;

                if (is_array($lockedWave->getAttributes()) && array_key_exists('registration_sequence', $lockedWave->getAttributes())) {
                    if ((int) $lockedWave->registration_sequence < $sequence) {
                        $lockedWave->forceFill(['registration_sequence' => $sequence])->save();
                    } else {
                        $lockedWave->increment('registration_sequence');
                        $lockedWave->refresh();
                        $sequence = (int) $lockedWave->registration_sequence;
                    }
                }

                $seq = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

                return $prefix . $seq;
            } finally {
                $this->releaseMySqlLock($lockName);
            }
        });
    }

    private function acquireMySqlLock(string $lockName, int $timeoutSeconds): bool
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return true;
        }

        $result = DB::selectOne('SELECT GET_LOCK(?, ?) AS lock_result', [$lockName, $timeoutSeconds]);

        return (int) ($result->lock_result ?? 0) === 1;
    }

    private function releaseMySqlLock(string $lockName): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::selectOne('SELECT RELEASE_LOCK(?) AS lock_result', [$lockName]);
    }
}

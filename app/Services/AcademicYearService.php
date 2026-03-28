<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    /**
     * Ambil tahun ajaran berstatus active.
     */
    public function getActive(): ?AcademicYear
    {
        return AcademicYear::where('status', 'active')->first();
    }

    /**
     * Ambil semua tahun ajaran diurutkan berdasarkan start_year DESC.
     */
    public function getAll(): Collection
    {
        return AcademicYear::orderBy('start_year', 'desc')->get();
    }

    /**
     * Resolve konteks tahun ajaran dari session.
     * Fallback ke tahun ajaran aktif jika session kosong atau id tidak valid.
     */
    public function resolveContext(Request $request): ?AcademicYear
    {
        $id = $request->session()->get('academic_year_context');

        if ($id) {
            $year = AcademicYear::find($id);
            if ($year) {
                return $year;
            }
        }

        return $this->getActive();
    }

    /**
     * Buat tahun ajaran baru dengan status 'draft'.
     * Setelah dibuat, salin konfigurasi jurusan dari tahun ajaran terakhir (jika ada).
     */
    public function create(array $data): AcademicYear
    {
        $data['status'] = 'draft';

        $newYear = AcademicYear::create($data);

        $lastYear = AcademicYear::where('id', '!=', $newYear->id)
            ->orderBy('end_year', 'desc')
            ->first();

        if ($lastYear) {
            $this->copyMajorConfig($lastYear, $newYear);
        }

        return $newYear;
    }

    /**
     * Aktifkan tahun ajaran: tutup semua yang active, lalu set $year menjadi active.
     */
    public function activate(AcademicYear $year): void
    {
        DB::transaction(function () use ($year) {
            AcademicYear::where('status', 'active')->update(['status' => 'closed']);
            $year->update(['status' => 'active']);
        });
    }

    /**
     * Tutup tahun ajaran.
     */
    public function close(AcademicYear $year): void
    {
        $year->update(['status' => 'closed']);
    }

    /**
     * Salin konfigurasi jurusan (quota, is_active) dari $source ke $target.
     */
    public function copyMajorConfig(AcademicYear $source, AcademicYear $target): void
    {
        $majors = $source->majors()->withPivot(['quota', 'is_active'])->get();

        foreach ($majors as $major) {
            $target->majors()->syncWithoutDetaching([
                $major->id => [
                    'quota'     => $major->pivot->quota,
                    'is_active' => $major->pivot->is_active,
                ],
            ]);
        }
    }

    /**
     * Ambil kuota jurusan untuk tahun ajaran tertentu.
     * Fallback ke Major::quota ?? 30 jika pivot tidak ditemukan.
     */
    public function getQuotaForMajor(AcademicYear $year, int $majorId): int
    {
        $pivot = $year->majors()
            ->wherePivot('major_id', $majorId)
            ->first();

        if ($pivot) {
            return (int) $pivot->pivot->quota;
        }

        return Major::find($majorId)?->quota ?? 30;
    }

    /**
     * Hitung jumlah siswa yang diterima pada jurusan tertentu di tahun ajaran tertentu.
     */
    public function getAcceptedCountForMajor(AcademicYear $year, int $majorId): int
    {
        return Student::where('academic_year_id', $year->id)
            ->where('accepted_major_id', $majorId)
            ->where('is_accepted', true)
            ->count();
    }
}

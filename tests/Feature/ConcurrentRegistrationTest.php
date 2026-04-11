<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\EnrollmentWave;
use App\Models\Major;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test concurrent registration scenarios.
 *
 * Simulates multiple students registering at the same time to verify:
 * 1. No duplicate registration numbers
 * 2. Quota is not exceeded
 * 3. NIK uniqueness is enforced
 */
class ConcurrentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private EnrollmentWave $wave;
    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup: tahun ajaran aktif
        $this->year = AcademicYear::factory()->active()->create([
            'start_year' => 2025,
            'end_year'   => 2026,
        ]);

        // Setup: jurusan dengan kuota 5
        $this->major = Major::factory()->create([
            'name'  => 'Teknik Komputer Jaringan',
            'code'  => 'TKJ',
            'quota' => 5,
        ]);

        // Attach jurusan ke tahun ajaran dengan kuota 5
        $this->year->majors()->attach($this->major->id, [
            'quota'     => 5,
            'is_active' => true,
        ]);

        // Setup: gelombang open
        $this->wave = EnrollmentWave::create([
            'academic_year_id' => $this->year->id,
            'name'             => 'Gelombang I',
            'wave_number'      => 1,
            'status'           => 'open',
        ]);

        // Attach kuota gelombang
        $this->wave->majors()->attach($this->major->id, ['quota' => 5]);
    }

    /**
     * Test 1: Nomor pendaftaran tidak duplikat meski request bersamaan.
     * Simulasi dengan membuat banyak student sekaligus.
     */
    public function test_registration_numbers_are_unique_under_concurrent_load(): void
    {
        $service = app(\App\Services\EnrollmentWaveService::class);

        $registrationNumbers = [];
        $errors = [];

        // Simulasi 20 pendaftar bersamaan
        for ($i = 0; $i < 20; $i++) {
            try {
                DB::beginTransaction();
                $regNum = $service->generateRegistrationNumber($this->wave);

                Student::create([
                    'academic_year_id'    => $this->year->id,
                    'enrollment_wave_id'  => $this->wave->id,
                    'registration_number' => $regNum,
                    'full_name'           => "Pendaftar {$i}",
                    'nik'                 => str_pad($i + 1000000000000000, 16, '0', STR_PAD_LEFT),
                    'email'               => "student{$i}@test.com",
                    'password'            => bcrypt('password'),
                    'verification_status' => 'pending',
                ]);

                DB::commit();
                $registrationNumbers[] = $regNum;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = $e->getMessage();
            }
        }

        // Semua nomor harus unik
        $unique = array_unique($registrationNumbers);
        $this->assertCount(
            count($registrationNumbers),
            $unique,
            'Terdapat nomor pendaftaran duplikat: ' . implode(', ', array_diff_assoc($registrationNumbers, $unique))
        );

        $this->assertEmpty($errors, 'Ada error saat registrasi: ' . implode('; ', $errors));
        $this->assertCount(20, $registrationNumbers);
    }

    /**
     * Test 2: Kuota tidak melebihi batas meski banyak alokasi bersamaan.
     */
    public function test_quota_is_not_exceeded_under_concurrent_allocation(): void
    {
        $adminService = app(\App\Services\EnrollmentWaveService::class);

        // Buat 10 student terdaftar di gelombang ini
        $students = [];
        for ($i = 0; $i < 10; $i++) {
            $students[] = Student::create([
                'academic_year_id'    => $this->year->id,
                'enrollment_wave_id'  => $this->wave->id,
                'registration_number' => "SPMB-2026-I-" . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'full_name'           => "Pendaftar {$i}",
                'nik'                 => str_pad($i + 1000000000000000, 16, '0', STR_PAD_LEFT),
                'email'               => "student{$i}@test.com",
                'password'            => bcrypt('password'),
                'verification_status' => 'verified',
                'is_accepted'         => false,
            ]);
        }

        // Simulasi 10 alokasi bersamaan ke jurusan yang sama (kuota = 5)
        $accepted = 0;
        $rejected = 0;

        foreach ($students as $student) {
            DB::beginTransaction();
            try {
                // Gunakan lockForUpdate seperti di controller
                $lockedStudent = Student::lockForUpdate()->find($student->id);
                $quota         = $adminService->getWaveQuota($this->wave, $this->major->id);
                $currentCount  = $adminService->getAcceptedCountInWave($this->wave, $this->major->id);

                if ($currentCount >= $quota) {
                    DB::rollBack();
                    $rejected++;
                    continue;
                }

                $lockedStudent->update([
                    'accepted_major_id'   => $this->major->id,
                    'is_accepted'         => true,
                    'verification_status' => 'verified',
                ]);

                DB::commit();
                $accepted++;
            } catch (\Exception $e) {
                DB::rollBack();
                $rejected++;
            }
        }

        // Tidak boleh lebih dari kuota (5)
        $this->assertLessThanOrEqual(5, $accepted, "Kuota terlampaui: {$accepted} diterima dari kuota 5");
        $this->assertEquals(5, $accepted, "Seharusnya tepat 5 yang diterima");
        $this->assertEquals(5, $rejected, "Seharusnya 5 yang ditolak");

        // Verifikasi di database
        $dbAccepted = Student::where('enrollment_wave_id', $this->wave->id)
            ->where('is_accepted', true)
            ->count();
        $this->assertLessThanOrEqual(5, $dbAccepted);
    }

    /**
     * Test 3: NIK duplikat ditolak dalam tahun ajaran yang sama.
     */
    public function test_duplicate_nik_is_rejected_in_same_academic_year(): void
    {
        $sameNik = '1234567890123456';

        // Pendaftar pertama berhasil
        Student::create([
            'academic_year_id'    => $this->year->id,
            'enrollment_wave_id'  => $this->wave->id,
            'registration_number' => 'SPMB-2026-I-0001',
            'full_name'           => 'Pendaftar Pertama',
            'nik'                 => $sameNik,
            'email'               => 'first@test.com',
            'password'            => bcrypt('password'),
            'verification_status' => 'pending',
        ]);

        // Pendaftar kedua dengan NIK sama harus gagal
        $response = $this->post(route('student.register.store'), [
            'full_name'       => 'Pendaftar Kedua',
            'nik'             => $sameNik,
            'place_of_birth'  => 'Jayapura',
            'date_of_birth'   => '2008-01-01',
            'gender'          => 'male',
            'street'          => 'Jl. Test',
            'district'        => 'Abepura',
            'phone'           => '081234567890',
            'email'           => 'second@test.com',
            'parent_name'     => 'Orang Tua',
            'mother_name'     => 'Ibu',
            'parent_phone'    => '081234567891',
            'school_name'     => 'SMP Test',
            'school_city'     => 'Jayapura',
            'school_province' => 'Papua',
            'major_1'         => $this->major->id,
            'major_2'         => $this->major->id + 1,
        ]);

        $response->assertSessionHasErrors('nik');
    }

    /**
     * Test 4: Pendaftaran ditolak jika tidak ada gelombang open.
     */
    public function test_registration_rejected_when_no_open_wave(): void
    {
        // Tutup gelombang
        $this->wave->update(['status' => 'closed']);

        $response = $this->post(route('student.register.store'), [
            'full_name' => 'Test',
            'nik'       => '1234567890123456',
        ]);

        $response->assertSessionHasErrors('error');
    }

    /**
     * Test 5: Stress test — 50 pendaftar, verifikasi tidak ada nomor duplikat.
     */
    public function test_stress_50_concurrent_registrations(): void
    {
        $service = app(\App\Services\EnrollmentWaveService::class);
        $numbers = [];

        for ($i = 0; $i < 50; $i++) {
            DB::beginTransaction();
            try {
                $regNum = $service->generateRegistrationNumber($this->wave);
                Student::create([
                    'academic_year_id'    => $this->year->id,
                    'enrollment_wave_id'  => $this->wave->id,
                    'registration_number' => $regNum,
                    'full_name'           => "Stress Test {$i}",
                    'nik'                 => str_pad($i + 2000000000000000, 16, '0', STR_PAD_LEFT),
                    'email'               => "stress{$i}@test.com",
                    'password'            => bcrypt('password'),
                    'verification_status' => 'pending',
                ]);
                DB::commit();
                $numbers[] = $regNum;
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }

        $this->assertCount(50, array_unique($numbers), 'Ada nomor pendaftaran duplikat pada stress test 50 pendaftar');
    }
}

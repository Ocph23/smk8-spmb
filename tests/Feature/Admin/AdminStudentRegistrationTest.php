<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\EnrollmentWave;
use App\Models\Major;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_student_with_final_registration_number(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->active()->create([
            'start_year' => 2025,
            'end_year'   => 2026,
        ]);

        $major1 = Major::factory()->create();
        $major2 = Major::factory()->create();

        $year->majors()->attach($major1->id, ['quota' => 30, 'is_active' => true]);
        $year->majors()->attach($major2->id, ['quota' => 30, 'is_active' => true]);

        $wave = EnrollmentWave::create([
            'academic_year_id' => $year->id,
            'name'             => 'Gelombang I',
            'wave_number'      => 1,
            'status'           => 'open',
            'registration_sequence' => 5,
        ]);

        $wave->majors()->attach($major1->id, ['quota' => 30]);
        $wave->majors()->attach($major2->id, ['quota' => 30]);

        Student::create([
            'academic_year_id'    => $year->id,
            'enrollment_wave_id'  => $wave->id,
            'registration_number' => 'SPMB-2026-I-0005',
            'full_name'           => 'Existing',
            'nik'                 => '1234567890123457',
            'email'               => 'existing@example.com',
            'password'            => bcrypt('password'),
            'verification_status' => 'pending',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.store'), [
                'full_name' => 'Siswa Admin',
                'nik' => '1234567890123456',
                'nisn' => '1234567890',
                'place_of_birth' => 'Jayapura',
                'date_of_birth' => '2008-01-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'street' => 'Jl. Contoh No. 1',
                'rt' => '001',
                'rw' => '002',
                'dusun' => 'Kampung Contoh',
                'district' => 'Abepura',
                'postal_code' => '99351',
                'school_name' => 'SMP Negeri 1 Jayapura',
                'school_city' => 'Jayapura',
                'school_province' => 'Papua',
                'parent_name' => 'Orang Tua',
                'mother_name' => 'Ibu',
                'parent_phone' => '081234567890',
                'email' => 'admin-student@example.com',
                'phone' => '081234567891',
                'major_1' => $major1->id,
                'major_2' => $major2->id,
                'major_3' => '',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ]);

        $response->assertRedirect();

        $student = Student::where('email', 'admin-student@example.com')->firstOrFail();

        $this->assertSame($year->id, $student->academic_year_id);
        $this->assertSame($wave->id, $student->enrollment_wave_id);
        $this->assertSame('male', $student->gender);
        $this->assertSame('Jl. Contoh No. 1', $student->street);
        $this->assertSame('001', $student->rt);
        $this->assertSame('002', $student->rw);
        $this->assertSame('Kampung Contoh', $student->dusun);
        $this->assertSame('Abepura', $student->district);
        $this->assertSame('99351', $student->postal_code);
        $this->assertStringStartsWith('SPMB-2026-I-', $student->registration_number);
        $this->assertStringNotContainsString('DRAFT-', $student->registration_number);
        $this->assertSame('SPMB-2026-I-0006', $student->registration_number);
    }

    public function test_registration_number_sequence_increments_per_wave(): void
    {
        $year = AcademicYear::factory()->active()->create([
            'start_year' => 2025,
            'end_year'   => 2026,
        ]);

        $wave = EnrollmentWave::create([
            'academic_year_id' => $year->id,
            'name'             => 'Gelombang I',
            'wave_number'      => 1,
            'status'           => 'open',
        ]);

        $service = app(\App\Services\EnrollmentWaveService::class);

        $first = $service->generateRegistrationNumber($wave);
        $second = $service->generateRegistrationNumber($wave);

        $this->assertSame('SPMB-2026-I-0001', $first);
        $this->assertSame('SPMB-2026-I-0002', $second);
        $this->assertDatabaseHas('enrollment_waves', [
            'id' => $wave->id,
            'registration_sequence' => 2,
        ]);
    }
}

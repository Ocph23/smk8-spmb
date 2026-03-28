<?php

namespace Tests\Feature\AcademicYear;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicYearService;
use Database\Seeders\HistoricalDataMigrationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature/Unit tests for Academic Year Management
 *
 * Covers:
 *   12.1 - Unit tests for AcademicYearService
 *   12.2 - Feature tests for student registration page
 *   12.3 - Feature tests for HandleInertiaRequests shared data
 *   12.4 - Feature tests for historical data migration
 */
class AcademicYearFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYearService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AcademicYearService();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private static int $yearSeq = 0;

    private function makeYearData(string $name = '', string $status = 'draft'): array
    {
        self::$yearSeq++;
        $start = 2000 + self::$yearSeq;
        return [
            'name'       => $name ?: "{$start}/" . ($start + 1),
            'start_year' => $start,
            'end_year'   => $start + 1,
            'status'     => $status,
        ];
    }

    private function createYear(string $status = 'draft'): AcademicYear
    {
        return AcademicYear::create($this->makeYearData('', $status));
    }

    private function createMajor(int $quota = 30): Major
    {
        static $mc = 0;
        $mc++;
        return Major::create([
            'name'  => 'Major ' . $mc . ' ' . uniqid(),
            'code'  => strtoupper(substr(uniqid(), 0, 5)) . $mc,
            'quota' => $quota,
        ]);
    }

    private function createStudent(AcademicYear $year): Student
    {
        static $sc = 0;
        $sc++;
        return Student::create([
            'academic_year_id'    => $year->id,
            'registration_number' => 'SPMB-' . $year->end_year . '-' . str_pad($sc, 4, '0', STR_PAD_LEFT),
            'full_name'           => 'Student ' . $sc,
            'nik'                 => str_pad((string)($sc * 1111 + rand(1, 99)), 16, '0', STR_PAD_LEFT),
            'place_of_birth'      => 'Jayapura',
            'date_of_birth'       => '2008-01-01',
            'gender'              => 'male',
            'religion'            => 'Islam',
            'address'             => 'Jl. Test No. ' . $sc,
            'phone'               => '08' . str_pad($sc, 10, '0', STR_PAD_LEFT),
            'email'               => 'student' . $sc . uniqid() . '@test.com',
            'parent_name'         => 'Parent ' . $sc,
            'parent_phone'        => '08' . str_pad($sc + 1000, 10, '0', STR_PAD_LEFT),
            'verification_status' => 'pending',
            'is_accepted'         => false,
        ]);
    }

    // =========================================================================
    // 12.1 — Unit tests for AcademicYearService
    // Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 3.1
    // =========================================================================

    /** @test */
    public function test_create_with_valid_data_returns_draft_academic_year(): void
    {
        $data = [
            'name'       => '2025/2026',
            'start_year' => 2025,
            'end_year'   => 2026,
        ];

        $year = $this->service->create($data);

        $this->assertInstanceOf(AcademicYear::class, $year);
        $this->assertEquals('draft', $year->status);
        $this->assertEquals('draft', $year->fresh()->status);
        $this->assertDatabaseHas('academic_years', ['id' => $year->id, 'status' => 'draft']);
    }

    /** @test */
    public function test_create_with_invalid_data_throws_or_fails(): void
    {
        // end_year <= start_year should fail validation at the controller level.
        // At the service level, the model/DB constraint should reject it.
        // We test via the HTTP endpoint to cover the full validation path.
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.academic-years.store'), [
            'name'       => '2026/2025',
            'start_year' => 2026,
            'end_year'   => 2025, // invalid: end <= start
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('academic_years', ['name' => '2026/2025']);
    }

    /** @test */
    public function test_activate_sets_status_to_active(): void
    {
        $year = $this->createYear('draft');

        $this->service->activate($year);

        $this->assertEquals('active', $year->fresh()->status);
    }

    /** @test */
    public function test_activate_closes_previously_active_year(): void
    {
        $first = $this->createYear('active');
        $second = $this->createYear('draft');

        $this->service->activate($second);

        $this->assertEquals('closed', $first->fresh()->status);
        $this->assertEquals('active', $second->fresh()->status);
    }

    /** @test */
    public function test_activate_ensures_only_one_active_year(): void
    {
        $years = [];
        for ($i = 0; $i < 3; $i++) {
            $years[] = $this->createYear('draft');
        }

        foreach ($years as $year) {
            $this->service->activate($year);
        }

        $activeCount = AcademicYear::where('status', 'active')->count();
        $this->assertEquals(1, $activeCount);
        $this->assertEquals('active', end($years)->fresh()->status);
    }

    /** @test */
    public function test_close_sets_status_to_closed(): void
    {
        $year = $this->createYear('active');

        $this->service->close($year);

        $this->assertEquals('closed', $year->fresh()->status);
        $this->assertEquals(0, AcademicYear::where('status', 'active')->count());
    }

    /** @test */
    public function test_copy_major_config_copies_all_majors_with_quota(): void
    {
        $source = $this->createYear('draft');
        $target = $this->createYear('draft');

        $majors = [];
        for ($i = 0; $i < 3; $i++) {
            $major = $this->createMajor(10 + $i * 5);
            $majors[] = $major;
            $source->majors()->attach($major->id, ['quota' => $major->quota, 'is_active' => true]);
        }

        $this->service->copyMajorConfig($source, $target);

        $targetMajorIds = $target->majors()->pluck('majors.id')->toArray();

        foreach ($majors as $major) {
            $this->assertContains($major->id, $targetMajorIds, "Target must contain major {$major->id}");

            $pivot = $target->majors()->wherePivot('major_id', $major->id)->first();
            $this->assertNotNull($pivot);
            $this->assertEquals($major->quota, $pivot->pivot->quota);
        }
    }

    // =========================================================================
    // 12.2 — Feature tests for student registration page
    // Requirements: 6.1, 6.2, 6.3
    // =========================================================================

    /** @test */
    public function test_registration_page_shows_active_academic_year_info(): void
    {
        $activeYear = $this->createYear('active');

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // The Home page receives academicYear.active via HandleInertiaRequests
        // and schedules are filtered by active year. Verify the Inertia response
        // includes the shared academicYear data.
        $response->assertInertia(fn ($page) =>
            $page->has('academicYear')
                 ->where('academicYear.active.id', $activeYear->id)
                 ->where('academicYear.active.name', $activeYear->name)
        );
    }

    /** @test */
    public function test_registration_page_shows_closed_message_when_no_active_year(): void
    {
        // Ensure no active year exists
        AcademicYear::where('status', 'active')->update(['status' => 'closed']);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // academicYear.active should be null when no active year
        $response->assertInertia(fn ($page) =>
            $page->has('academicYear')
                 ->where('academicYear.active', null)
        );
    }

    /** @test */
    public function test_student_dashboard_shows_registration_number_and_status(): void
    {
        $activeYear = $this->createYear('active');
        $student = $this->createStudent($activeYear);

        $response = $this->actingAs($student, 'student')
            ->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('student')
                 ->where('student.registration_number', $student->registration_number)
                 ->where('student.verification_status', $student->verification_status)
        );
    }

    // =========================================================================
    // 12.3 — Feature tests for HandleInertiaRequests shared data
    // Requirements: 4.1, 4.5
    // =========================================================================

    /** @test */
    public function test_admin_pages_share_academic_year_current_data(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('academicYear')
                 ->has('academicYear.current')
        );
    }

    /** @test */
    public function test_admin_pages_share_academic_year_all_data(): void
    {
        // Create 3 academic years
        for ($i = 0; $i < 3; $i++) {
            $this->createYear('draft');
        }

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('academicYear')
                 ->has('academicYear.all', 3)
        );
    }

    /** @test */
    public function test_admin_pages_share_academic_year_active_data(): void
    {
        $activeYear = $this->createYear('active');
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('academicYear')
                 ->where('academicYear.active.id', $activeYear->id)
        );
    }

    /** @test */
    public function test_public_pages_share_academic_year_active_data(): void
    {
        $activeYear = $this->createYear('active');

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('academicYear')
                 ->where('academicYear.active.id', $activeYear->id)
        );
    }

    // =========================================================================
    // 12.4 — Feature tests for historical data migration
    // Requirements: 7.1, 7.2, 7.3
    // =========================================================================

    /** @test */
    public function test_seeder_creates_one_academic_year_with_correct_name(): void
    {
        // Insert a student with registration_number 'SPMB-2026-0001'
        Student::create([
            'registration_number' => 'SPMB-2026-0001',
            'full_name'           => 'Historical Student',
            'nik'                 => '1234567890123456',
            'place_of_birth'      => 'Jayapura',
            'date_of_birth'       => '2008-01-01',
            'gender'              => 'male',
            'religion'            => 'Islam',
            'address'             => 'Jl. Historis',
            'phone'               => '081234567890',
            'email'               => 'historical@test.com',
            'parent_name'         => 'Parent',
            'parent_phone'        => '081234567891',
            'verification_status' => 'pending',
            'is_accepted'         => false,
        ]);

        $this->seed(HistoricalDataMigrationSeeder::class);

        $this->assertEquals(1, AcademicYear::count());
        $this->assertDatabaseHas('academic_years', ['name' => '2025/2026']);
    }

    /** @test */
    public function test_seeder_associates_all_students_to_historical_year(): void
    {
        // Insert 5 students without academic_year_id
        for ($i = 1; $i <= 5; $i++) {
            Student::create([
                'registration_number' => 'SPMB-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'full_name'           => 'Student ' . $i,
                'nik'                 => str_pad((string)($i * 1111111111111111), 16, '0', STR_PAD_LEFT),
                'place_of_birth'      => 'Jayapura',
                'date_of_birth'       => '2008-01-01',
                'gender'              => 'male',
                'religion'            => 'Islam',
                'address'             => 'Jl. Test ' . $i,
                'phone'               => '0812345678' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'email'               => 'student' . $i . '@historical.com',
                'parent_name'         => 'Parent ' . $i,
                'parent_phone'        => '0812345679' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'verification_status' => 'pending',
                'is_accepted'         => false,
                'academic_year_id'    => null,
            ]);
        }

        $this->seed(HistoricalDataMigrationSeeder::class);

        $nullCount = Student::whereNull('academic_year_id')->count();
        $this->assertEquals(0, $nullCount, 'All students must have a non-null academic_year_id after seeder');

        $year = AcademicYear::first();
        $this->assertNotNull($year);
        $this->assertEquals(5, Student::where('academic_year_id', $year->id)->count());
    }

    /** @test */
    public function test_seeder_associates_all_schedules_to_historical_year(): void
    {
        // Insert 3 schedules without academic_year_id
        for ($i = 1; $i <= 3; $i++) {
            Schedule::create([
                'academic_year_id' => null,
                'title'            => 'Schedule ' . $i,
                'start_date'       => now()->addDays($i)->format('Y-m-d'),
                'end_date'         => now()->addDays($i + 1)->format('Y-m-d'),
                'status'           => 'inactive',
            ]);
        }

        $this->seed(HistoricalDataMigrationSeeder::class);

        $nullCount = Schedule::whereNull('academic_year_id')->count();
        $this->assertEquals(0, $nullCount, 'All schedules must have a non-null academic_year_id after seeder');

        $year = AcademicYear::first();
        $this->assertNotNull($year);
        $this->assertEquals(3, Schedule::where('academic_year_id', $year->id)->count());
    }
}

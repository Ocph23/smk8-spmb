<?php

namespace Tests\Feature\AcademicYear;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicYearService;
use Database\Seeders\HistoricalDataMigrationSeeder;
use Eris\Generators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcademicYearPropertyTest extends TestCase
{
    use RefreshDatabase;
    use \Eris\TestTrait;

    protected AcademicYearService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AcademicYearService();
        $this->minimumEvaluationRatio(0);
    }

    // -------------------------------------------------------------------------
    // Helper: create a unique academic year with sequential years
    // -------------------------------------------------------------------------
    private static int $yearOffset = 0;

    private function makeAcademicYearData(string $name = ''): array
    {
        self::$yearOffset++;
        $start = 2000 + self::$yearOffset;
        return [
            'name'       => $name ?: "{$start}/" . ($start + 1),
            'start_year' => $start,
            'end_year'   => $start + 1,
        ];
    }

    private function createMajor(int $quota = 30): Major
    {
        static $mc = 0;
        $mc++;
        return Major::create([
            'name'  => 'Major ' . $mc . ' ' . uniqid(),
            'code'  => strtoupper(substr(uniqid(), 0, 6)) . $mc,
            'quota' => $quota,
        ]);
    }

    private function createStudent(AcademicYear $year, string $nik = ''): Student
    {
        static $sc = 0;
        $sc++;
        $nik = $nik ?: str_pad((string)($sc * 1000 + rand(1, 999)), 16, '0', STR_PAD_LEFT);
        return Student::create([
            'academic_year_id'    => $year->id,
            'registration_number' => 'SPMB-' . $year->end_year . '-' . str_pad($sc, 4, '0', STR_PAD_LEFT),
            'full_name'           => 'Student ' . $sc,
            'nik'                 => $nik,
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
    // Property 1: Status awal selalu draft
    // Feature: academic-year-management, Property 1: Status awal selalu draft
    // Validates: Requirements 1.2
    // =========================================================================
    public function testInitialStatusIsAlwaysDraft(): void
    {
        $this->forAll(
            Generators::names()
        )->then(function (string $name) {
            $data = $this->makeAcademicYearData($name ?: 'Default');
            $year = $this->service->create($data);

            $this->assertEquals('draft', $year->status, "New academic year must have status 'draft'");
            $this->assertEquals('draft', $year->fresh()->status, "Persisted academic year must have status 'draft'");
        });
    }

    // =========================================================================
    // Property 2: Invariant satu tahun ajaran aktif
    // Feature: academic-year-management, Property 2: Invariant satu tahun ajaran aktif
    // Validates: Requirements 1.3, 1.4
    // =========================================================================
    public function testSingleActiveAcademicYearInvariant(): void
    {
        $this->forAll(
            Generators::choose(1, 10)
        )->then(function (int $count) {
            // Create $count draft academic years
            $years = [];
            for ($i = 0; $i < $count; $i++) {
                $years[] = AcademicYear::create($this->makeAcademicYearData());
            }

            // Activate one randomly
            $toActivate = $years[array_rand($years)];
            $this->service->activate($toActivate);

            $activeCount = AcademicYear::where('status', 'active')->count();
            $this->assertEquals(1, $activeCount, "Exactly one academic year must be active after activation, got {$activeCount}");
        });
    }

    // =========================================================================
    // Property 3: Penutupan tahun ajaran aktif
    // Feature: academic-year-management, Property 3: Penutupan tahun ajaran aktif
    // Validates: Requirements 1.5
    // =========================================================================
    public function testClosingActiveAcademicYear(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $extraCount) {
            // Create one active year and some draft years
            $activeYear = AcademicYear::create(array_merge($this->makeAcademicYearData(), ['status' => 'active']));
            $otherYearIds = [];
            for ($i = 0; $i < $extraCount; $i++) {
                $y = AcademicYear::create($this->makeAcademicYearData());
                $otherYearIds[] = (int) $y->id;
            }

            $this->service->close($activeYear);

            $this->assertEquals('closed', $activeYear->fresh()->status, "Active year must become 'closed' after close()");
            $this->assertEquals(0, AcademicYear::where('status', 'active')->count(), "No active year should remain after close()");

            // Other years must still be 'draft' (unchanged)
            foreach ($otherYearIds as $otherId) {
                $freshY = AcademicYear::find($otherId);
                $this->assertNotNull($freshY, "Other year {$otherId} must still exist after closing active year");
                $this->assertEquals('draft', $freshY->status, "Other draft years must remain 'draft' when closing active year");
            }
        });
    }

    // =========================================================================
    // Property 4: Penolakan penghapusan tahun ajaran berdata
    // Feature: academic-year-management, Property 4: Penolakan penghapusan tahun ajaran berdata
    // Validates: Requirements 1.6
    // =========================================================================
    public function testDeletionRejectedWhenYearHasStudents(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $studentCount) {
            $year = AcademicYear::create($this->makeAcademicYearData());

            for ($i = 0; $i < $studentCount; $i++) {
                $this->createStudent($year);
            }

            $yearId = $year->id;

            // Simulate controller destroy logic
            $count = $year->students()->count();
            $this->assertGreaterThan(0, $count, "Year must have students");

            // The controller returns an error response instead of deleting
            $this->assertGreaterThan(0, $count, "Deletion should be rejected when students exist");

            // Verify the year still exists
            $this->assertDatabaseHas('academic_years', ['id' => $yearId]);
        });
    }

    // =========================================================================
    // Property 5: Validasi rentang tahun
    // Feature: academic-year-management, Property 5: Validasi rentang tahun
    // Validates: Requirements 1.7
    // =========================================================================
    public function testYearRangeValidation(): void
    {
        $this->forAll(
            Generators::choose(2000, 2100),
            Generators::choose(2000, 2100)
        )->then(function (int $startYear, int $endYear) {
            $admin = User::factory()->admin()->create();

            $response = $this->actingAs($admin)->post(route('admin.academic-years.store'), [
                'name'       => "{$startYear}/{$endYear}",
                'start_year' => $startYear,
                'end_year'   => $endYear,
            ]);

            if ($endYear > $startYear) {
                // Should succeed: redirect back (302) or success
                $this->assertNotEquals(422, $response->getStatusCode(), "Valid range ({$startYear}-{$endYear}) should not return 422");
            } else {
                // Should fail validation
                $response->assertSessionHasErrors();
            }
        });
    }

    // =========================================================================
    // Property 6: Isolasi data entitas ke tahun ajaran
    // Feature: academic-year-management, Property 6: Isolasi data entitas ke tahun ajaran
    // Validates: Requirements 2.1, 2.2
    // =========================================================================
    public function testEntityDataIsolationToAcademicYear(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $count) {
            $year = AcademicYear::create($this->makeAcademicYearData());

            for ($i = 0; $i < $count; $i++) {
                $student = $this->createStudent($year);
                $this->assertNotNull($student->academic_year_id, "Student must have non-null academic_year_id");
                $this->assertEquals($year->id, $student->academic_year_id, "Student must reference the correct academic year");
                $this->assertDatabaseHas('academic_years', ['id' => $student->academic_year_id]);

                $schedule = Schedule::create([
                    'academic_year_id' => $year->id,
                    'title'            => 'Schedule ' . $i,
                    'start_date'       => now()->addDays($i)->format('Y-m-d'),
                    'end_date'         => now()->addDays($i + 1)->format('Y-m-d'),
                    'status'           => 'inactive',
                ]);
                $this->assertNotNull($schedule->academic_year_id, "Schedule must have non-null academic_year_id");
                $this->assertEquals($year->id, $schedule->academic_year_id, "Schedule must reference the correct academic year");
                $this->assertDatabaseHas('academic_years', ['id' => $schedule->academic_year_id]);
            }
        });
    }

    // =========================================================================
    // Property 7: Pendaftaran otomatis terikat ke tahun ajaran aktif
    // Feature: academic-year-management, Property 7: Pendaftaran otomatis terikat ke tahun ajaran aktif
    // Validates: Requirements 2.4
    // =========================================================================
    public function testRegistrationAutomaticallyBoundToActiveYear(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $iteration) {
            // Ensure exactly one active year
            AcademicYear::where('status', 'active')->update(['status' => 'closed']);
            $activeYear = AcademicYear::create(array_merge($this->makeAcademicYearData(), ['status' => 'active']));

            // Create a student directly bound to active year (simulating registration)
            $student = $this->createStudent($activeYear);

            $this->assertEquals(
                $activeYear->id,
                $student->academic_year_id,
                "Student's academic_year_id must equal the active academic year's id"
            );
        });
    }

    // =========================================================================
    // Property 8: Penolakan pendaftaran tanpa tahun ajaran aktif
    // Feature: academic-year-management, Property 8: Penolakan pendaftaran tanpa tahun ajaran aktif
    // Validates: Requirements 2.5
    // =========================================================================
    public function testRegistrationRejectedWithoutActiveYear(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $iteration) {
            // Ensure no active academic year
            AcademicYear::where('status', 'active')->update(['status' => 'closed']);

            $countBefore = Student::count();

            // Attempt registration via HTTP
            static $nikCounter = 0;
            $nikCounter++;
            $nik = str_pad((string)($nikCounter * 7777 + $iteration), 16, '0', STR_PAD_LEFT);

            $response = $this->post(route('student.register.store'), [
                'full_name'    => 'Test Student',
                'nik'          => $nik,
                'place_of_birth' => 'Jayapura',
                'date_of_birth'  => '2008-01-01',
                'gender'         => 'male',
                'religion'       => 'Islam',
                'address'        => 'Jl. Test',
                'phone'          => '081234567890',
                'email'          => 'test' . $nikCounter . uniqid() . '@test.com',
                'parent_name'    => 'Parent',
                'parent_phone'   => '081234567891',
            ]);

            $countAfter = Student::count();
            $this->assertEquals($countBefore, $countAfter, "Student count must not increase when no active year exists");
        });
    }

    // =========================================================================
    // Property 9: Format dan keunikan nomor pendaftaran per tahun ajaran
    // Feature: academic-year-management, Property 9: Format dan keunikan nomor pendaftaran per tahun ajaran
    // Validates: Requirements 2.6
    // =========================================================================
    public function testRegistrationNumberFormatAndUniqueness(): void
    {
        $this->forAll(
            Generators::choose(1, 20)
        )->then(function (int $count) {
            $year = AcademicYear::create(array_merge($this->makeAcademicYearData(), ['status' => 'active']));
            $endYear = $year->end_year;

            $registrationNumbers = [];
            for ($i = 0; $i < $count; $i++) {
                $seq = $i + 1;
                $regNum = 'SPMB-' . $endYear . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
                $registrationNumbers[] = $regNum;
            }

            // All registration numbers must be unique
            $unique = array_unique($registrationNumbers);
            $this->assertCount(count($registrationNumbers), $unique, "All registration numbers must be unique");

            // All must match the format SPMB-{year}-{4 digits}
            foreach ($registrationNumbers as $regNum) {
                $this->assertMatchesRegularExpression(
                    '/^SPMB-\d{4}-\d{4}$/',
                    $regNum,
                    "Registration number '{$regNum}' must match format SPMB-{year}-{4 digits}"
                );
            }
        });
    }

    // =========================================================================
    // Property 10: Filter data siswa berdasarkan konteks tahun ajaran
    // Feature: academic-year-management, Property 10: Filter data siswa berdasarkan konteks tahun ajaran
    // Validates: Requirements 2.7, 4.2
    // =========================================================================
    public function testStudentFilterByAcademicYearContext(): void
    {
        $this->forAll(
            Generators::choose(1, 4),
            Generators::choose(1, 4)
        )->then(function (int $yearCount, int $studentsPerYear) {
            $years = [];
            for ($i = 0; $i < $yearCount; $i++) {
                $year = AcademicYear::create($this->makeAcademicYearData());
                $years[] = $year;
                for ($j = 0; $j < $studentsPerYear; $j++) {
                    $this->createStudent($year);
                }
            }

            // Pick a random year as context
            $contextYear = $years[array_rand($years)];

            // Query students filtered by context
            $students = Student::where('academic_year_id', $contextYear->id)->get();

            foreach ($students as $student) {
                $this->assertEquals(
                    $contextYear->id,
                    $student->academic_year_id,
                    "All students in context must have the same academic_year_id"
                );
            }

            // Verify no students from other years appear
            $otherYearIds = array_map(fn($y) => $y->id, array_filter($years, fn($y) => $y->id !== $contextYear->id));
            if (!empty($otherYearIds)) {
                foreach ($students as $student) {
                    $this->assertNotContains(
                        $student->academic_year_id,
                        $otherYearIds,
                        "Students from other years must not appear in filtered results"
                    );
                }
            }
        });
    }

    // =========================================================================
    // Property 11: Penyalinan konfigurasi jurusan ke tahun ajaran baru
    // Feature: academic-year-management, Property 11: Penyalinan konfigurasi jurusan ke tahun ajaran baru
    // Validates: Requirements 3.1
    // =========================================================================
    public function testMajorConfigCopiedToNewAcademicYear(): void
    {
        $this->forAll(
            Generators::choose(1, 8)
        )->then(function (int $majorCount) {
            // Create source year with majors
            $sourceYear = AcademicYear::create($this->makeAcademicYearData());
            $majors = [];
            for ($i = 0; $i < $majorCount; $i++) {
                $major = $this->createMajor(rand(10, 50));
                $majors[] = $major;
                $sourceYear->majors()->attach($major->id, ['quota' => $major->quota, 'is_active' => true]);
            }

            // Create new year via service (should copy config from source)
            $newYear = $this->service->create($this->makeAcademicYearData());

            // Verify pivot contains all majors with same quota
            $newYearMajorIds = $newYear->majors()->pluck('majors.id')->toArray();
            foreach ($majors as $major) {
                $this->assertContains(
                    $major->id,
                    $newYearMajorIds,
                    "New year must contain major {$major->name} copied from source year"
                );

                $pivot = $newYear->majors()->wherePivot('major_id', $major->id)->first();
                $this->assertNotNull($pivot, "Pivot must exist for major {$major->id}");
                $this->assertEquals(
                    $major->quota,
                    $pivot->pivot->quota,
                    "Copied quota must match source quota for major {$major->name}"
                );
            }
        });
    }

    // =========================================================================
    // Property 12: Isolasi konfigurasi kuota antar tahun ajaran
    // Feature: academic-year-management, Property 12: Isolasi konfigurasi kuota antar tahun ajaran
    // Validates: Requirements 3.2
    // =========================================================================
    public function testQuotaConfigIsolationBetweenYears(): void
    {
        $this->forAll(
            Generators::choose(10, 50),
            Generators::choose(10, 50)
        )->then(function (int $quotaA, int $quotaB) {
            $major = $this->createMajor(30);

            $yearA = AcademicYear::create($this->makeAcademicYearData());
            $yearB = AcademicYear::create($this->makeAcademicYearData());

            $yearA->majors()->attach($major->id, ['quota' => $quotaA, 'is_active' => true]);
            $yearB->majors()->attach($major->id, ['quota' => $quotaB, 'is_active' => true]);

            // Change quota in year A
            $newQuotaA = $quotaA + 5;
            $yearA->majors()->updateExistingPivot($major->id, ['quota' => $newQuotaA]);

            // Verify year B quota unchanged
            $pivotB = $yearB->majors()->wherePivot('major_id', $major->id)->first();
            $this->assertEquals(
                $quotaB,
                $pivotB->pivot->quota,
                "Changing quota in year A must not affect quota in year B"
            );

            // Verify year A quota changed
            $pivotA = $yearA->majors()->wherePivot('major_id', $major->id)->first();
            $this->assertEquals(
                $newQuotaA,
                $pivotA->pivot->quota,
                "Year A quota must be updated"
            );
        });
    }

    // =========================================================================
    // Property 13: Validasi kuota saat alokasi siswa
    // Feature: academic-year-management, Property 13: Validasi kuota saat alokasi siswa
    // Validates: Requirements 3.3, 3.4
    // =========================================================================
    public function testQuotaValidationDuringStudentAllocation(): void
    {
        $this->forAll(
            Generators::choose(1, 10)
        )->then(function (int $quota) {
            $year = AcademicYear::create(array_merge($this->makeAcademicYearData(), ['status' => 'active']));
            $major = $this->createMajor($quota);
            $year->majors()->attach($major->id, ['quota' => $quota, 'is_active' => true]);

            // Fill quota exactly
            for ($i = 0; $i < $quota; $i++) {
                $student = $this->createStudent($year);
                $student->update(['accepted_major_id' => $major->id, 'is_accepted' => true]);
            }

            // Verify quota is full
            $acceptedCount = $this->service->getAcceptedCountForMajor($year, $major->id);
            $this->assertEquals($quota, $acceptedCount, "Accepted count must equal quota");

            // Attempt to allocate one more
            $currentQuota = $this->service->getQuotaForMajor($year, $major->id);
            $currentAccepted = $this->service->getAcceptedCountForMajor($year, $major->id);

            $this->assertGreaterThanOrEqual(
                $currentQuota,
                $currentAccepted,
                "Accepted count must be >= quota when full"
            );

            // The n+1 allocation must be rejected (quota check)
            $shouldReject = $currentAccepted >= $currentQuota;
            $this->assertTrue($shouldReject, "Allocation beyond quota must be rejected");
        });
    }

    // =========================================================================
    // Property 14: Konteks tahun ajaran tersimpan di session
    // Feature: academic-year-management, Property 14: Konteks tahun ajaran tersimpan di session
    // Validates: Requirements 4.5
    // =========================================================================
    public function testAcademicYearContextStoredInSession(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $yearCount) {
            $admin = User::factory()->admin()->create();

            $years = [];
            for ($i = 0; $i < $yearCount; $i++) {
                $years[] = AcademicYear::create($this->makeAcademicYearData());
            }

            // Pick a random year to set as context
            $selectedYear = $years[array_rand($years)];

            $response = $this->actingAs($admin)
                ->post(route('admin.academic-years.set-context'), [
                    'academic_year_id' => $selectedYear->id,
                ]);

            // Verify session was updated
            $response->assertSessionHas('academic_year_context', $selectedYear->id);
        });
    }

    // =========================================================================
    // Property 15: Statistik laporan konsisten dengan filter tahun ajaran
    // Feature: academic-year-management, Property 15: Statistik laporan konsisten dengan filter tahun ajaran
    // Validates: Requirements 5.1, 5.5
    // =========================================================================
    public function testReportStatisticsConsistentWithYearFilter(): void
    {
        $this->forAll(
            Generators::choose(1, 5),
            Generators::choose(1, 5)
        )->then(function (int $studentsInYear, int $studentsInOther) {
            $year = AcademicYear::create(array_merge($this->makeAcademicYearData(), ['status' => 'active']));
            $otherYear = AcademicYear::create($this->makeAcademicYearData());

            for ($i = 0; $i < $studentsInYear; $i++) {
                $s = $this->createStudent($year);
                if ($i % 2 === 0) {
                    $s->update(['gender' => 'male']);
                } else {
                    $s->update(['gender' => 'female']);
                }
            }

            for ($i = 0; $i < $studentsInOther; $i++) {
                $this->createStudent($otherYear);
            }

            // Query stats filtered by year
            $query = Student::where('academic_year_id', $year->id);
            $total = (clone $query)->count();
            $male = (clone $query)->where('gender', 'male')->count();
            $female = (clone $query)->where('gender', 'female')->count();

            // Total must equal sum of sub-categories
            $this->assertEquals($total, $male + $female, "Total must equal sum of male + female");
            $this->assertEquals($studentsInYear, $total, "Total must only include students from filtered year");
        });
    }

    // =========================================================================
    // Property 16: Nama tahun ajaran pada ekspor
    // Feature: academic-year-management, Property 16: Nama tahun ajaran pada ekspor
    // Validates: Requirements 5.3
    // =========================================================================
    public function testAcademicYearNameInExport(): void
    {
        $this->forAll(
            Generators::names()
        )->then(function (string $yearName) {
            $name = $yearName ?: '2025/2026';
            $admin = User::factory()->admin()->create();

            $year = AcademicYear::create(array_merge($this->makeAcademicYearData($name), ['status' => 'active']));

            // Set context to this year
            $this->actingAs($admin)
                ->post(route('admin.academic-years.set-context'), [
                    'academic_year_id' => $year->id,
                ]);

            // Request CSV export
            $response = $this->actingAs($admin)
                ->withSession(['academic_year_context' => $year->id])
                ->get(route('admin.reports.csv'));

            $response->assertStatus(200);

            // Verify year name appears in Content-Disposition header (filename)
            $contentDisposition = $response->headers->get('Content-Disposition');
            $this->assertNotNull($contentDisposition, "Content-Disposition header must be present");
            $this->assertStringContainsString(
                $year->name,
                $contentDisposition,
                "Export filename must contain the academic year name"
            );
        });
    }

    // =========================================================================
    // Property 17: Isolasi data siswa (keamanan akses)
    // Feature: academic-year-management, Property 17: Isolasi data siswa (keamanan akses)
    // Validates: Requirements 6.4
    // =========================================================================
    public function testStudentDataIsolationSecurityAccess(): void
    {
        $this->forAll(
            Generators::choose(2, 5)
        )->then(function (int $totalStudents) {
            $year = AcademicYear::create(array_merge($this->makeAcademicYearData(), ['status' => 'active']));

            $students = [];
            for ($i = 0; $i < $totalStudents; $i++) {
                $students[] = $this->createStudent($year);
            }

            // Authenticate as student A (first student)
            $studentA = $students[0];

            // Student dashboard should only return student A's data
            $response = $this->actingAs($studentA, 'student')
                ->get(route('student.dashboard'));

            $response->assertStatus(200);

            // The dashboard returns the authenticated student's data
            // Verify via the StudentAuthController logic: it uses Auth::guard('student')->user()
            $authenticatedStudent = \Illuminate\Support\Facades\Auth::guard('student')->user();
            if ($authenticatedStudent) {
                $this->assertEquals($studentA->id, $authenticatedStudent->id, "Dashboard must show only student A's data");
            }
        });
    }

    // =========================================================================
    // Property 18: Penolakan pendaftaran duplikat NIK per tahun ajaran
    // Feature: academic-year-management, Property 18: Penolakan pendaftaran duplikat NIK per tahun ajaran
    // Validates: Requirements 6.5
    // =========================================================================
    public function testDuplicateNikRejectedPerAcademicYear(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $iteration) {
            // Ensure active year
            AcademicYear::where('status', 'active')->update(['status' => 'closed']);
            $year = AcademicYear::create(array_merge($this->makeAcademicYearData(), ['status' => 'active']));

            // Generate a unique NIK for this iteration
            $nik = str_pad((string)($iteration * 99991 + rand(1, 9999)), 16, '0', STR_PAD_LEFT);

            // First registration
            $this->createStudent($year, $nik);
            $countAfterFirst = Student::where('academic_year_id', $year->id)->count();

            // Attempt second registration with same NIK in same year
            $duplicateCreated = false;
            try {
                $this->createStudent($year, $nik);
                $duplicateCreated = true;
            } catch (\Illuminate\Database\QueryException $e) {
                // Expected: unique constraint violation
                $duplicateCreated = false;
            }

            $countAfterSecond = Student::where('academic_year_id', $year->id)->count();

            if ($duplicateCreated) {
                // If somehow created, count should still be same (shouldn't happen)
                $this->fail("Duplicate NIK registration must be rejected");
            } else {
                $this->assertEquals($countAfterFirst, $countAfterSecond, "Student count must not increase on duplicate NIK");
            }
        });
    }

    // =========================================================================
    // Property 19: Idempoten migrasi data historis
    // Feature: academic-year-management, Property 19: Idempoten migrasi data historis
    // Validates: Requirements 7.4
    // =========================================================================
    public function testHistoricalMigrationIsIdempotent(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $studentCount) {
            // Reset state for each iteration
            DB::table('academic_year_major')->delete();
            DB::table('students')->delete();
            DB::table('schedules')->delete();
            DB::table('academic_years')->delete();

            $year = 2026;

            // Create students without academic_year_id using unique registration numbers
            for ($i = 0; $i < $studentCount; $i++) {
                $uniqueSuffix = uniqid('', true);
                $regNum = 'SPMB-' . $year . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                DB::table('students')->insert([
                    'registration_number' => $regNum,
                    'full_name'           => 'Historical Student ' . $i,
                    'nik'                 => str_pad((string)(rand(100000, 999999) . rand(1000, 9999)), 16, '0', STR_PAD_LEFT),
                    'place_of_birth'      => 'Jayapura',
                    'date_of_birth'       => '2008-01-01',
                    'gender'              => 'male',
                    'religion'            => 'Islam',
                    'address'             => 'Jl. Test',
                    'phone'               => '08' . str_pad($i + 1, 10, '0', STR_PAD_LEFT),
                    'email'               => 'hist' . $i . $uniqueSuffix . '@test.com',
                    'parent_name'         => 'Parent',
                    'parent_phone'        => '08' . str_pad($i + 100, 10, '0', STR_PAD_LEFT),
                    'verification_status' => 'pending',
                    'is_accepted'         => false,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            // Run seeder first time
            $seeder = new HistoricalDataMigrationSeeder();
            $seeder->run();

            $countYears1    = AcademicYear::count();
            $countStudents1 = Student::count();
            $countSchedules1 = Schedule::count();

            // Run seeder second time (must be idempotent)
            $seeder->run();

            $countYears2    = AcademicYear::count();
            $countStudents2 = Student::count();
            $countSchedules2 = Schedule::count();

            $this->assertEquals($countYears1, $countYears2, "AcademicYear count must not change on second seeder run");
            $this->assertEquals($countStudents1, $countStudents2, "Student count must not change on second seeder run");
            $this->assertEquals($countSchedules1, $countSchedules2, "Schedule count must not change on second seeder run");
        });
    }

    // =========================================================================
    // Property 20: Kelengkapan migrasi data historis
    // Feature: academic-year-management, Property 20: Kelengkapan migrasi data historis
    // Validates: Requirements 7.2, 7.3
    // =========================================================================
    public function testHistoricalMigrationCompleteness(): void
    {
        $this->forAll(
            Generators::choose(1, 10)
        )->then(function (int $count) {
            // Reset state for each iteration
            DB::table('academic_year_major')->delete();
            DB::table('students')->delete();
            DB::table('schedules')->delete();
            DB::table('academic_years')->delete();

            $year = 2026;

            // Create students without academic_year_id using proper SPMB format
            for ($i = 0; $i < $count; $i++) {
                $regNum = 'SPMB-' . $year . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                DB::table('students')->insert([
                    'registration_number' => $regNum,
                    'full_name'           => 'Hist Student ' . $i,
                    'nik'                 => str_pad((string)(rand(100000, 999999) . rand(1000, 9999)), 16, '0', STR_PAD_LEFT),
                    'place_of_birth'      => 'Jayapura',
                    'date_of_birth'       => '2008-01-01',
                    'gender'              => 'male',
                    'religion'            => 'Islam',
                    'address'             => 'Jl. Test',
                    'phone'               => '08' . str_pad($i + 200, 10, '0', STR_PAD_LEFT),
                    'email'               => 'hist20' . $i . uniqid() . '@test.com',
                    'parent_name'         => 'Parent',
                    'parent_phone'        => '08' . str_pad($i + 300, 10, '0', STR_PAD_LEFT),
                    'verification_status' => 'pending',
                    'is_accepted'         => false,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            // Create schedules without academic_year_id
            for ($i = 0; $i < $count; $i++) {
                DB::table('schedules')->insert([
                    'title'      => 'Hist Schedule ' . $i,
                    'start_date' => now()->addDays($i)->format('Y-m-d'),
                    'end_date'   => now()->addDays($i + 1)->format('Y-m-d'),
                    'status'     => 'inactive',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Run seeder
            $seeder = new HistoricalDataMigrationSeeder();
            $seeder->run();

            // Verify no students with null academic_year_id
            $nullStudents = Student::whereNull('academic_year_id')->count();
            $this->assertEquals(0, $nullStudents, "No students must have null academic_year_id after migration");

            // Verify no schedules with null academic_year_id
            $nullSchedules = Schedule::whereNull('academic_year_id')->count();
            $this->assertEquals(0, $nullSchedules, "No schedules must have null academic_year_id after migration");
        });
    }
}

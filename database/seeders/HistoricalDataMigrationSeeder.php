<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Database\Seeder;

class HistoricalDataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempoten: skip jika sudah ada data tahun ajaran
        if (AcademicYear::exists()) {
            return;
        }

        // Tentukan tahun dari registration_number student pertama (format SPMB-{tahun}-xxxx)
        $firstStudent = Student::whereNotNull('registration_number')
            ->where('registration_number', 'LIKE', 'SPMB-%')
            ->orderBy('registration_number')
            ->first();

        $year = $firstStudent
            ? (int) explode('-', $firstStudent->registration_number)[1]
            : now()->year;

        $startYear = $year - 1;
        $endYear   = $year;

        // Buat tahun ajaran historis dengan status closed
        $academicYear = AcademicYear::create([
            'name'        => "{$startYear}/{$endYear}",
            'start_year'  => $startYear,
            'end_year'    => $endYear,
            'status'      => 'closed',
            'description' => 'Tahun ajaran historis (migrasi otomatis)',
        ]);

        // Attach semua Major ke academicYear dengan quota dari major dan is_active = true
        foreach (Major::all() as $major) {
            $academicYear->majors()->attach($major->id, [
                'quota'     => $major->quota,
                'is_active' => true,
            ]);
        }

        // Update semua Student yang belum punya academic_year_id
        Student::whereNull('academic_year_id')
            ->update(['academic_year_id' => $academicYear->id]);

        // Update semua Schedule yang belum punya academic_year_id
        Schedule::whereNull('academic_year_id')
            ->update(['academic_year_id' => $academicYear->id]);
    }
}

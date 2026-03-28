<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        $year = now()->year;
        return [
            'academic_year_id'    => null,
            'registration_number' => 'SPMB-' . $year . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
            'full_name'           => $this->faker->name(),
            'nik'                 => $this->faker->unique()->numerify('################'),
            'nisn'                => $this->faker->numerify('##########'),
            'place_of_birth'      => $this->faker->city(),
            'date_of_birth'       => $this->faker->date('Y-m-d', '-15 years'),
            'gender'              => $this->faker->randomElement(['male', 'female']),
            'religion'            => 'Islam',
            'address'             => $this->faker->address(),
            'phone'               => $this->faker->phoneNumber(),
            'email'               => $this->faker->unique()->safeEmail(),
            'parent_name'         => $this->faker->name(),
            'parent_phone'        => $this->faker->phoneNumber(),
            'verification_status' => 'pending',
            'is_accepted'         => false,
        ];
    }

    public function forAcademicYear(AcademicYear $academicYear): static
    {
        static $yearCounters = [];
        $yearId = $academicYear->id;
        if (!isset($yearCounters[$yearId])) {
            $yearCounters[$yearId] = 0;
        }
        $yearCounters[$yearId]++;
        $seq = $yearCounters[$yearId];

        return $this->state([
            'academic_year_id'    => $academicYear->id,
            'registration_number' => 'SPMB-' . $academicYear->end_year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT),
        ]);
    }
}

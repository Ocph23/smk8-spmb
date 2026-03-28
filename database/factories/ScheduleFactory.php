<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('now', '+1 month');
        $end   = $this->faker->dateTimeBetween($start, '+2 months');
        return [
            'academic_year_id' => null,
            'title'            => $this->faker->sentence(3),
            'description'      => $this->faker->sentence(),
            'start_date'       => $start->format('Y-m-d'),
            'end_date'         => $end->format('Y-m-d'),
            'status'           => 'inactive',
        ];
    }

    public function forAcademicYear(AcademicYear $academicYear): static
    {
        return $this->state(['academic_year_id' => $academicYear->id]);
    }
}

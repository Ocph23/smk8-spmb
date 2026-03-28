<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = $this->faker->numberBetween(2020, 2030);
        return [
            'name'        => "{$startYear}/" . ($startYear + 1),
            'start_year'  => $startYear,
            'end_year'    => $startYear + 1,
            'status'      => 'draft',
            'description' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function closed(): static
    {
        return $this->state(['status' => 'closed']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }
}

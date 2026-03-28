<?php

namespace Database\Factories;

use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Major>
 */
class MajorFactory extends Factory
{
    protected $model = Major::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        return [
            'name'        => 'Jurusan ' . $counter . ' ' . $this->faker->word(),
            'code'        => strtoupper($this->faker->unique()->lexify('???')) . $counter,
            'description' => $this->faker->sentence(),
            'quota'       => $this->faker->numberBetween(10, 50),
        ];
    }
}

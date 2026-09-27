<?php

namespace Database\Factories;

use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Faculty> */
class FacultyFactory extends Factory
{
    protected $model = Faculty::class;

    public function definition(): array
    {
        return ['name' => fake()->name(), 'department' => 'CAE', 'is_active' => true];
    }
}

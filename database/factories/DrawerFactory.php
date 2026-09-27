<?php

namespace Database\Factories;

use App\Models\Drawer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Drawer> */
class DrawerFactory extends Factory
{
    protected $model = Drawer::class;

    public function definition(): array
    {
        return ['number' => fake()->unique()->numberBetween(1, 65000), 'label' => fake()->words(3, true), 'is_active' => true];
    }
}

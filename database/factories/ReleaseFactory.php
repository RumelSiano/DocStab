<?php

namespace Database\Factories;

use App\Models\DocumentRequest;
use App\Models\Release;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Release> */
class ReleaseFactory extends Factory
{
    protected $model = Release::class;

    public function definition(): array
    {
        return ['request_id' => DocumentRequest::factory(), 'released_by' => User::factory()->staff(), 'claimant_name' => fake()->name(), 'is_representative' => false, 'acknowledged_at' => now()];
    }
}

<?php

namespace Database\Factories;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DocumentRequest> */
class DocumentRequestFactory extends Factory
{
    protected $model = DocumentRequest::class;

    public function definition(): array
    {
        return ['requestor_id' => User::factory(), 'created_by' => fn (array $attributes): int => $attributes['requestor_id'], 'tracking_reference' => 'CDT-'.Str::ulid(), 'purpose' => fake()->sentence()];
    }
}

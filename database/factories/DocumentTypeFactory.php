<?php

namespace Database\Factories;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DocumentType> */
class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->words(3, true), 'is_active' => true];
    }
}

<?php

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\FacultyAssignment;
use App\Models\RequestDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FacultyAssignment> */
class FacultyAssignmentFactory extends Factory
{
    protected $model = FacultyAssignment::class;

    public function definition(): array
    {
        return ['request_document_id' => RequestDocument::factory(), 'faculty_id' => Faculty::factory(), 'status' => 'pending'];
    }
}

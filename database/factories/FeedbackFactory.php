<?php

namespace Database\Factories;

use App\Models\DocumentRequest;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Feedback> */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return ['request_id' => DocumentRequest::factory()->state(['completed_at' => now()]), 'service_rating' => 4, 'processing_rating' => 5];
    }
}

<?php

namespace Database\Factories;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RequestDocument> */
class RequestDocumentFactory extends Factory
{
    protected $model = RequestDocument::class;

    public function definition(): array
    {
        return ['request_id' => DocumentRequest::factory(), 'document_type_id' => DocumentType::factory(), 'status' => 'awaiting_dropoff'];
    }
}

<?php

namespace Database\Factories;

use App\Models\DocumentEvent;
use App\Models\RequestDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DocumentEvent> */
class DocumentEventFactory extends Factory
{
    protected $model = DocumentEvent::class;

    public function definition(): array
    {
        return ['request_document_id' => RequestDocument::factory(), 'actor_id' => User::factory()->staff(), 'action' => 'receive', 'from_status' => 'awaiting_dropoff', 'to_status' => 'received'];
    }
}

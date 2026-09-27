<?php

namespace App\Http\Resources;

use App\Models\FacultyAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource->only(['id', 'request_id', 'document_type_id', 'drawer_id', 'status', 'received_at', 'ready_at', 'claimed_at', 'created_at', 'updated_at']) + [
            'document_type' => $this->whenLoaded('type', fn () => $this->type->name),
            'type' => $this->whenLoaded('type'),
            'drawer' => $this->whenLoaded('drawer'),
            'faculty' => $this->whenLoaded('assignments', fn () => $this->assignments->map(fn (FacultyAssignment $assignment): array => $assignment->only(['id', 'request_document_id', 'faculty_id', 'status', 'routed_at', 'returned_at', 'created_at', 'updated_at']) + ['name' => $assignment->faculty->name])),
            'events' => $this->whenLoaded('events'),
        ];
    }
}

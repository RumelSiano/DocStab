<?php

namespace App\Http\Resources;

use App\Services\Tracking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource->only(['id', 'requestor_id', 'created_by', 'tracking_reference', 'purpose', 'notes', 'completed_at', 'created_at', 'updated_at']) + [
            'needed_date' => $this->needed_date?->format('Y-m-d'),
            'requestor' => $this->whenLoaded('requestor'),
            'documents' => RequestDocumentResource::collection($this->whenLoaded('documents')),
            'status' => $this->whenLoaded('documents', fn (): string => Tracking::status($this->documents->pluck('status')->all())),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'stub' => $this->whenLoaded('stub'),
            'feedback' => $this->whenLoaded('feedback'),
            'releases' => $this->whenLoaded('releases'),
        ];
    }
}

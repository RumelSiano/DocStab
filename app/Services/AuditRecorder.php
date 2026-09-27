<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\RequestDocument;
use App\Models\User;

class AuditRecorder
{
    /** @param array<string, mixed> $details */
    public function record(User $actor, string $action, ?int $requestId = null, array $details = []): void
    {
        AuditLog::create(['actor_id' => $actor->id, 'actor_role' => $actor->role, 'request_id' => $requestId, 'action' => $action, 'details' => $details]);
    }

    public function documentEvent(User $actor, RequestDocument $document, string $action, string $from, ?int $drawerId = null, ?string $notes = null): void
    {
        $document->events()->create(['actor_id' => $actor->id, 'action' => $action, 'from_status' => $from, 'to_status' => $document->status, 'drawer_id' => $drawerId, 'notes' => $notes]);
        $this->record($actor, $action, $document->request_id, ['document_id' => $document->id, 'from' => $from, 'to' => $document->status]);
    }
}

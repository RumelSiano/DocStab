<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\Feedback;
use App\Models\RequestDocument;
use App\Models\User;

class DashboardSummary
{
    /** @return array<string, mixed> */
    public function forUser(User $user): array
    {
        $batches = DocumentRequest::query()->visibleTo($user);
        $ids = (clone $batches)->select('id');
        $total = (clone $batches)->count();
        $completed = (clone $batches)->whereNotNull('completed_at')->count();
        $counts = RequestDocument::query()->whereIn('request_id', $ids)->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        $ready = (clone $batches)->whereHas('documents', fn ($query) => $query->where('status', 'ready'))->count();
        $types = RequestDocument::query()->join('document_types as t', 't.id', '=', 'request_documents.document_type_id')->whereIn('request_documents.request_id', $ids)->selectRaw('t.id, t.name, COUNT(*) as count')->groupBy('t.id', 't.name')->get();
        $ratings = Feedback::query()->whereIn('request_id', $ids)->selectRaw('AVG(service_rating) as service, AVG(processing_rating) as processing')->first();

        return ['total_batches' => $total, 'active_batches' => $total - $completed, 'ready_for_collection_batches' => $ready, 'completed_batches' => $completed, 'documents_by_status' => $counts, 'documents_by_type' => $types, 'feedback_averages' => $ratings];
    }
}

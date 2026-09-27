<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Resources\DocumentRequestResource;
use App\Models\DocumentRequest;
use App\Models\Feedback;
use App\Services\RequestWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view-feedback');
        $page = Feedback::query()->with('request.requestor:id,name')->orderByDesc('id')->paginate(25);

        return response()->json($page->through(fn (Feedback $feedback): array => $feedback->only(['id', 'request_id', 'service_rating', 'processing_rating', 'comments', 'created_at', 'updated_at']) + ['tracking_reference' => $feedback->request->tracking_reference, 'requestor_name' => $feedback->request->requestor->name]));
    }

    public function store(StoreFeedbackRequest $request, DocumentRequest $documentRequest, RequestWorkflow $workflow): JsonResponse
    {
        $workflow->feedback($request->user(), $documentRequest, $request->validated());

        return response()->json((new DocumentRequestResource($documentRequest->refresh()->loadDetails()))->resolve($request), 201);
    }
}

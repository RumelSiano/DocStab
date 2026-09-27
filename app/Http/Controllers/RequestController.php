<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentRequestResource;
use App\Models\DocumentRequest;
use App\Services\RequestWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', DocumentRequest::class);
        $request->validate(['search' => 'nullable|string|max:100', 'page' => 'sometimes|integer|min:1']);
        $page = DocumentRequest::query()->visibleTo($request->user())->with(['requestor:id,name', 'documents.type'])
            ->when($request->filled('search'), fn ($query) => $query->where('tracking_reference', 'like', '%'.$request->input('search').'%'))
            ->orderByDesc('id')->paginate(20);
        $page->through(fn (DocumentRequest $batch): array => (new DocumentRequestResource($batch))->resolve($request));

        return response()->json($page);
    }

    public function show(Request $request, DocumentRequest $documentRequest): JsonResponse
    {
        Gate::authorize('view', $documentRequest);

        return response()->json((new DocumentRequestResource($documentRequest->loadDetails()))->resolve($request));
    }

    public function store(StoreDocumentRequest $request, RequestWorkflow $workflow): JsonResponse
    {
        $batch = $workflow->submit($request->user(), $request->validated());

        return response()->json((new DocumentRequestResource($batch->refresh()->loadDetails()))->resolve($request), 201);
    }
}

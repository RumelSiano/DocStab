<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransitionDocumentRequest;
use App\Http\Resources\DocumentRequestResource;
use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Services\RequestWorkflow;
use Illuminate\Http\JsonResponse;

class DocumentTransitionController extends Controller
{
    public function store(TransitionDocumentRequest $request, DocumentRequest $documentRequest, RequestDocument $document, RequestWorkflow $workflow): JsonResponse
    {
        $workflow->transition($request->user(), $documentRequest, $document, $request->validated());

        return response()->json((new DocumentRequestResource($documentRequest->refresh()->loadDetails()))->resolve($request));
    }
}

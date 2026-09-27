<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReleaseRequest;
use App\Http\Resources\DocumentRequestResource;
use App\Models\DocumentRequest;
use App\Services\RequestWorkflow;
use Illuminate\Http\JsonResponse;

class ReleaseController extends Controller
{
    public function store(StoreReleaseRequest $request, DocumentRequest $documentRequest, RequestWorkflow $workflow): JsonResponse
    {
        $workflow->release($request->user(), $documentRequest, $request->validated());

        return response()->json((new DocumentRequestResource($documentRequest->refresh()->loadDetails()))->resolve($request));
    }
}

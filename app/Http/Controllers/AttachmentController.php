<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\DocumentRequest;
use App\Services\RequestWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, DocumentRequest $documentRequest, RequestWorkflow $workflow): JsonResponse
    {
        $attachment = $workflow->upload($request->user(), $documentRequest, $request->file('file'));

        return response()->json($attachment->only(['id', 'original_name']), 201);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $attachment->load('request');
        Gate::authorize('view', $attachment);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name, ['X-Content-Type-Options' => 'nosniff']);
    }
}

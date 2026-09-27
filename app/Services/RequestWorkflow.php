<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequestWorkflow
{
    public function __construct(private AuditRecorder $audit) {}

    /** @param array<string, mixed> $data */
    public function submit(User $actor, array $data): DocumentRequest
    {
        $owner = $actor->role === 'requestor' ? $actor->id : ($data['requestor_id'] ?? null);
        abort_unless($owner, 422, 'Choose a requestor account.');

        return DB::transaction(function () use ($actor, $data, $owner): DocumentRequest {
            $batch = DocumentRequest::create(['requestor_id' => $owner, 'created_by' => $actor->id, 'tracking_reference' => 'CDT-'.Str::ulid(),
                'purpose' => $data['purpose'] ?? null, 'notes' => $data['notes'] ?? null, 'needed_date' => $data['needed_date'] ?? null]);
            foreach ($data['documents'] as $row) {
                $document = $batch->documents()->create(['document_type_id' => $row['document_type_id'], 'status' => 'awaiting_dropoff']);
                foreach (array_unique($row['faculty_ids']) as $facultyId) {
                    $document->assignments()->create(['faculty_id' => $facultyId, 'status' => 'pending']);
                }
            }
            $batch->stub()->create(['reference' => (string) Str::uuid()]);
            $this->audit->record($actor, 'request_submitted', $batch->id);

            return $batch;
        });
    }

    /** @param array<string, mixed> $data */
    public function transition(User $actor, DocumentRequest $batch, RequestDocument $document, array $data): void
    {
        DB::transaction(function () use ($actor, $batch, $document, $data): void {
            $batch = DocumentRequest::query()->lockForUpdate()->findOrFail($batch->id);
            $document = $batch->documents()->lockForUpdate()->findOrFail($document->id);
            $from = $document->status;
            switch ($data['action']) {
                case 'receive':
                    abort_unless($from === 'awaiting_dropoff', 409, 'Only pending documents can be received.');
                    $document->status = 'received';
                    $document->received_at = now();
                    break;
                case 'route':
                    abort_unless($from === 'received', 409, 'Receive the physical document first.');
                    abort_unless($document->assignments()->exists(), 422, 'No faculty assigned.');
                    $document->assignments()->update(['status' => 'routed', 'routed_at' => now()]);
                    $document->status = 'processing';
                    break;
                case 'return':
                    abort_unless($from === 'processing', 409, 'Document must be processing.');
                    $changed = $document->assignments()->where('faculty_id', $data['faculty_id'] ?? 0)->where('status', 'routed')->update(['status' => 'returned', 'returned_at' => now()]);
                    abort_unless($changed, 409, 'Select a routed faculty assignment.');
                    break;
                case 'ready':
                    abort_unless(in_array($from, ['received', 'processing'], true), 409, 'Document cannot be marked ready.');
                    abort_if($document->assignments()->where('status', '!=', 'returned')->exists(), 409, 'Confirm all faculty returns first.');
                    abort_unless($data['drawer_id'] ?? null, 422, 'Choose a storage drawer.');
                    $document->status = 'ready';
                    $document->drawer_id = $data['drawer_id'];
                    $document->ready_at = now();
                    break;
                case 'move':
                    abort_unless($from === 'ready' && ($data['drawer_id'] ?? null), 409, 'Only ready documents can change storage drawer.');
                    $document->drawer_id = $data['drawer_id'];
                    break;
            }
            $document->save();
            $batch->touch();
            $this->audit->documentEvent($actor, $document, $data['action'], $from, $document->drawer_id, $data['notes'] ?? null);
        });
    }

    /** @param array<string, mixed> $data */
    public function release(User $actor, DocumentRequest $batch, array $data): void
    {
        DB::transaction(function () use ($actor, $batch, $data): void {
            $batch = DocumentRequest::query()->lockForUpdate()->findOrFail($batch->id);
            $documents = $batch->documents()->whereIn('id', $data['document_ids'])->orderBy('id')->lockForUpdate()->get();
            abort_unless($documents->count() === count($data['document_ids']) && $documents->every(fn (RequestDocument $document): bool => $document->status === 'ready'), 409, 'Every selected document must belong to this batch and be ready.');
            $release = $batch->releases()->create(['released_by' => $actor->id, 'claimant_name' => $data['claimant_name'],
                'claimant_identifier' => $data['claimant_identifier'] ?? null, 'is_representative' => $data['is_representative'],
                'authorization_reference' => $data['authorization_reference'] ?? null, 'acknowledged_at' => now()]);
            foreach ($documents as $document) {
                $drawerId = $document->drawer_id;
                $release->items()->create(['request_document_id' => $document->id, 'drawer_id' => $drawerId]);
                $document->update(['status' => 'claimed', 'drawer_id' => null, 'claimed_at' => now()]);
                $this->audit->documentEvent($actor, $document, 'released', 'ready', $drawerId);
            }
            $batch->update(['completed_at' => $batch->documents()->where('status', '!=', 'claimed')->exists() ? null : now()]);
        });
    }

    /** @param array<string, mixed> $data */
    public function feedback(User $actor, DocumentRequest $batch, array $data): void
    {
        DB::transaction(function () use ($actor, $batch, $data): void {
            $batch = DocumentRequest::query()->lockForUpdate()->findOrFail($batch->id);
            abort_unless($batch->completed_at, 409, 'Feedback is available after completion.');
            abort_if($batch->feedback()->exists(), 409, 'Feedback already submitted.');
            $batch->feedback()->create($data);
            $this->audit->record($actor, 'feedback_submitted', $batch->id);
        });
    }

    public function upload(User $actor, DocumentRequest $batch, UploadedFile $file): Attachment
    {
        $path = $file->store('requests/'.$batch->id, 'local');
        abort_unless($path, 500, 'File could not be stored.');
        try {
            return DB::transaction(function () use ($actor, $batch, $file, $path): Attachment {
                $batch = DocumentRequest::query()->lockForUpdate()->findOrFail($batch->id);
                abort_if($batch->completed_at, 409, 'Completed requests cannot accept new attachments.');
                $attachment = $batch->attachments()->create(['uploaded_by' => $actor->id, 'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
                    'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
                $this->audit->record($actor, 'attachment_uploaded', $batch->id, ['attachment_id' => $attachment->id]);

                return $attachment;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }
}

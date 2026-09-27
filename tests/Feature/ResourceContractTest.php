<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\FacultyAssignment;
use App\Models\RequestDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResourceContractTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_detail_contains_loaded_workflow_data_without_storage_paths_or_credentials(): void
    {
        $owner = User::factory()->create();
        $batch = DocumentRequest::factory()->for($owner, 'requestor')->create();
        $document = RequestDocument::factory()->for($batch, 'request')->create();
        $assignment = FacultyAssignment::factory()->for($document, 'document')->create();
        Attachment::factory()->for($batch, 'request')->create();
        $this->actingAs($owner)->getJson('/api/requests/'.$batch->id)->assertOk()
            ->assertJsonPath('status', 'awaiting_dropoff')
            ->assertJsonPath('documents.0.faculty.0.faculty_id', $assignment->faculty_id)
            ->assertJsonMissingPath('attachments.0.path')
            ->assertJsonMissingPath('requestor.password')
            ->assertJsonMissingPath('requestor.remember_token');
    }

    public function test_request_list_queries_do_not_grow_per_document(): void
    {
        $owner = User::factory()->create();
        $batch = DocumentRequest::factory()->for($owner, 'requestor')->create();
        RequestDocument::factory()->for($batch, 'request')->create();
        $this->actingAs($owner);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/requests')->assertOk();
        $firstCount = count(DB::getQueryLog());
        RequestDocument::factory()->count(10)->for($batch, 'request')->create();
        DB::flushQueryLog();
        $this->getJson('/api/requests')->assertOk()->assertJsonCount(11, 'data.0.documents');
        $this->assertSame($firstCount, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }

    public function test_profile_saves_program_and_rejects_privileged_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patchJson('/api/me', ['name' => 'Updated name', 'program' => 'BS Accountancy', 'role' => 'admin', 'is_active' => false])->assertOk()->assertJsonPath('program', 'BS Accountancy')->assertJsonPath('role', 'requestor');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'program' => 'BS Accountancy', 'role' => 'requestor', 'is_active' => true]);
    }

    public function test_validated_document_rows_reject_unexpected_status_fields(): void
    {
        $user = User::factory()->create();
        $type = DocumentType::factory()->create();
        $this->actingAs($user)->postJson('/api/requests', ['documents' => [['document_type_id' => $type->id, 'faculty_ids' => [], 'status' => 'claimed']]])->assertUnprocessable()->assertJsonValidationErrors('documents.0');
        $this->assertDatabaseCount('requests', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_request_permissions_follow_roles_ownership_and_active_status(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $staff = User::factory()->staff()->create();
        $admin = User::factory()->administrator()->create();
        $disabled = User::factory()->administrator()->disabled()->create();
        $batch = DocumentRequest::factory()->for($owner, 'requestor')->create();
        foreach ([[$owner, true, false, true], [$other, false, false, false], [$staff, true, true, false], [$admin, true, true, false], [$disabled, false, false, false]] as [$user,$view,$process,$feedback]) {
            $gate = Gate::forUser($user);
            $this->assertSame($view, $gate->allows('view', $batch));
            $this->assertSame($view, $gate->allows('upload', $batch));
            $this->assertSame($process, $gate->allows('process', $batch));
            $this->assertSame($feedback, $gate->allows('feedback', $batch));
            $this->assertSame($user->id === $admin->id, $gate->allows('manage-users'));
            $this->assertSame($user->id === $admin->id, $gate->allows('manage-catalogs'));
        }
        $attachment = Attachment::factory()->for($batch, 'request')->create();
        $attachment->load('request');
        $this->assertTrue(Gate::forUser($owner)->allows('view', $attachment));
        $this->assertFalse(Gate::forUser($other)->allows('view', $attachment));
    }

    public function test_scoped_document_binding_returns_404_for_another_batch(): void
    {
        $batch = DocumentRequest::factory()->create();
        $otherDocument = RequestDocument::factory()->create();
        $this->actingAs(User::factory()->staff()->create())->postJson('/api/requests/'.$batch->id.'/documents/'.$otherDocument->id.'/transition', ['action' => 'receive'])->assertNotFound();
        $this->assertDatabaseHas('request_documents', ['id' => $otherDocument->id, 'status' => 'awaiting_dropoff']);
        $this->assertDatabaseCount('document_events', 0);
    }

    public function test_staff_cannot_manage_accounts_and_does_not_receive_admin_accounts(): void
    {
        $staff = User::factory()->staff()->create();
        User::factory()->administrator()->create();
        $requestor = User::factory()->create();
        $this->actingAs($staff)->getJson('/api/users')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $requestor->id);
        $this->postJson('/api/admin/users', ['name' => 'Attempt'])->assertForbidden();
        $this->assertDatabaseCount('users', 3);
    }
}

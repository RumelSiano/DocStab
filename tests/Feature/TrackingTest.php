<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\Drawer;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role = 'requestor'): User
    {
        return match ($role) {
            'sta' => User::factory()->staff()->create(),
            'admin' => User::factory()->administrator()->create(),
            default => User::factory()->create(),
        };
    }

    private function batch(User $owner, int $count = 1, bool $faculty = true): array
    {
        $type = DocumentType::factory()->create()->id;
        $f = Faculty::factory()->create()->id;

        return $this->actingAs($owner)->postJson('/api/requests', ['documents' => array_fill(0, $count, ['document_type_id' => $type, 'faculty_ids' => $faculty ? [$f] : []])])->assertCreated()->json();
    }

    public function test_full_workflow_partial_release_feedback_and_private_files(): void
    {
        Storage::fake('local');
        $owner = $this->account();
        $sta = $this->account('sta');
        $other = $this->account();
        $batch = $this->batch($owner, 2);
        $id = $batch['id'];
        $file = UploadedFile::fake()->createWithContent('reference.pdf', "%PDF-1.4\nreference");
        $attachment = $this->postJson("/api/requests/$id/attachments", ['file' => $file])->assertCreated()->json('id');
        $this->get("/api/attachments/$attachment/download")->assertOk();
        $this->actingAs($other)->getJson("/api/requests/$id")->assertForbidden();
        $this->get("/api/attachments/$attachment/download")->assertForbidden();
        $this->actingAs($owner)->postJson("/api/requests/$id/documents/{$batch['documents'][0]['id']}/transition", ['action' => 'receive'])->assertForbidden();
        $this->postJson("/api/requests/$id/feedback", ['service_rating' => 5, 'processing_rating' => 4])->assertStatus(409);
        $drawer = Drawer::factory()->create(['number' => 13, 'label' => 'Routing'])->id;
        $this->actingAs($sta);
        foreach ($batch['documents'] as $doc) {
            $url = "/api/requests/$id/documents/{$doc['id']}/transition";
            $this->postJson($url, ['action' => 'route'])->assertStatus(409);
            $this->postJson($url, ['action' => 'receive'])->assertOk();
            $this->postJson($url, ['action' => 'route'])->assertOk();
            $this->postJson($url, ['action' => 'ready', 'drawer_id' => $drawer])->assertStatus(409);
            $this->postJson($url, ['action' => 'return', 'faculty_id' => $doc['faculty'][0]['faculty_id']])->assertOk();
            $this->postJson($url, ['action' => 'ready', 'drawer_id' => $drawer])->assertOk();
        }
        $release = ['document_ids' => [$batch['documents'][0]['id']], 'claimant_name' => 'Owner', 'is_representative' => false, 'acknowledged' => true];
        $this->postJson("/api/requests/$id/releases", $release)->assertOk()->assertJsonPath('status', 'partially_claimed');
        $this->postJson("/api/requests/$id/releases", $release)->assertStatus(409);
        $release['document_ids'] = [$batch['documents'][1]['id']];
        $release['is_representative'] = true;
        $this->postJson("/api/requests/$id/releases", $release)->assertUnprocessable();
        $release['authorization_reference'] = 'Signed letter 123';
        $this->postJson("/api/requests/$id/releases", $release)->assertOk()->assertJsonPath('status', 'completed');
        $this->assertDatabaseHas('release_items', ['request_document_id' => $batch['documents'][0]['id'], 'drawer_id' => $drawer]);
        $this->actingAs($owner)->postJson("/api/requests/$id/feedback", ['service_rating' => 5, 'processing_rating' => 4])->assertCreated();
        $this->postJson("/api/requests/$id/feedback", ['service_rating' => 5, 'processing_rating' => 4])->assertStatus(409);
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPath('completed_batches', 1);
    }

    public function test_auth_roles_and_disabled_accounts(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/register', ['name' => 'New user', 'email' => 'new@example.test', 'password' => 'LongPassword123!', 'password_confirmation' => 'LongPassword123!', 'role' => 'admin'])->assertCreated()->assertJsonPath('role', 'requestor')->assertJsonMissingPath('password');
        $this->postJson('/api/admin/catalogs/document_types', ['name' => 'Test'])->assertForbidden();
        $this->postJson('/api/logout')->assertNoContent();
        $this->postJson('/api/login', ['email' => 'new@example.test', 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/api/login', ['email' => 'new@example.test', 'password' => 'LongPassword123!'])->assertOk();
        User::where('email', 'new@example.test')->update(['is_active' => false]);
        $this->actingAs(User::where('email', 'new@example.test')->first())->getJson('/api/me')->assertForbidden();
    }

    public function test_admin_catalogs_and_self_protection(): void
    {
        $admin = $this->account('admin');
        $this->actingAs($admin);
        $id = $this->postJson('/api/admin/catalogs/document_types', ['name' => 'Certificate'])->assertCreated()->json('id');
        $this->patchJson("/api/admin/catalogs/document_types/$id", ['name' => 'Certificate', 'is_active' => false])->assertOk();
        $this->patchJson('/api/admin/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'admin', 'is_active' => false])->assertUnprocessable();
        $this->getJson('/api/history')->assertOk()->assertJsonCount(2, 'data');
        $this->actingAs($this->account())->postJson('/api/requests', ['documents' => [['document_type_id' => $id, 'faculty_ids' => []]]])->assertUnprocessable();
    }

    public function test_release_cannot_mix_batches_and_upload_rejects_executable(): void
    {
        Storage::fake('local');
        $owner = $this->account();
        $a = $this->batch($owner, 1, false);
        $b = $this->batch($owner, 1, false);
        $this->postJson('/api/requests/'.$a['id'].'/attachments', ['file' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;')])->assertUnprocessable();
        $this->actingAs($this->account('sta'))->postJson('/api/requests/'.$a['id'].'/releases', ['document_ids' => [$b['documents'][0]['id']], 'claimant_name' => 'Test', 'is_representative' => false, 'acknowledged' => true])->assertStatus(409);
        $this->assertDatabaseCount('releases', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackendSecurityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_password_recovery_delivers_a_working_link_and_resets_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token, $user) {
            $token = $notification->token;
            $this->get($notification->toMail($user)->actionUrl)->assertSee('Reset your CDTrack password');

            return true;
        });
        $this->postJson('/api/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'ChangedPassword123!', 'password_confirmation' => 'ChangedPassword123!'])->assertOk();
        $this->assertTrue(Hash::check('ChangedPassword123!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_forged_reset_token_does_not_change_password(): void
    {
        $user = User::factory()->create();
        $original = $user->password;
        $this->postJson('/api/reset-password', ['email' => $user->email, 'token' => 'forged', 'password' => 'ChangedPassword123!', 'password_confirmation' => 'ChangedPassword123!'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame($original, $user->fresh()->password);
    }

    public function test_api_mutation_requires_a_csrf_token(): void
    {
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $this->postJson('/api/login', ['email' => 'test@example.test', 'password' => 'password'])->assertStatus(419);
        $token = 'valid-csrf-test-token';
        $this->withSession(['_token' => $token])->withHeader('X-CSRF-TOKEN', $token)->postJson('/api/login', ['email' => 'test@example.test', 'password' => 'password'])->assertUnprocessable();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/api/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertTooManyRequests();
    }

    public function test_completed_batch_rejects_upload_and_removes_uncommitted_file(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $id = DocumentRequest::factory()->for($user, 'requestor')->create(['completed_at' => now()])->id;
        $this->actingAs($user)->postJson('/api/requests/'.$id.'/attachments', ['file' => UploadedFile::fake()->createWithContent('reference.pdf', "%PDF-1.4\nreference")])->assertStatus(409);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('attachments', 0);
    }

    public function test_requestor_lists_and_counts_exclude_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        foreach ([$owner, $other] as $user) {
            DocumentRequest::factory()->for($user, 'requestor')->create();
        }
        $this->actingAs($owner)->getJson('/api/requests')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.requestor_id', $owner->id);
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPath('total_batches', 1);
    }
}

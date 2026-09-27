<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendTest extends TestCase
{
    public function test_portal_serves_the_session_based_frontend(): void
    {
        $this->get('/')->assertOk()->assertViewIs('portal')
            ->assertSee('app/portal.js')->assertSee('csrf-token')->assertSee('data-api=', false);
    }

    public function test_reset_link_preserves_and_escapes_email_for_the_frontend(): void
    {
        $email = '<script>alert(1)</script>@example.test';
        $this->get(route('password.reset', ['token' => 'sample-token', 'email' => $email]))
            ->assertOk()->assertViewIs('portal')->assertSee('Reset your CDTrack password')
            ->assertSee('data-reset-token="sample-token"', false)
            ->assertSee(e($email), false)->assertDontSee($email, false);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class SessionHijackTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_session_replayed_on_another_tenant_is_logged_out(): void
    {
        $this->actingAs($this->ownerA)->get('/alpha/dashboard')->assertOk();

        // Replay the SAME session principal (owner A, tenant alpha) against
        // tenant B's prefix — withSession re-injects the login key, exactly
        // like a replayed cookie would.
        $guardName = \Illuminate\Support\Facades\Auth::guard('web')->getName();
        $response = $this->withSession([$guardName => $this->ownerA->id])->get('/beta/dashboard');

        $response->assertRedirect(url('/beta/login'));
        $this->assertGuest();
    }

    public function test_tenant_b_user_visiting_alpha_is_redirected_to_login(): void
    {
        $this->actingAs($this->ownerB)->get('/beta/dashboard')->assertOk();

        $guardName = \Illuminate\Support\Facades\Auth::guard('web')->getName();
        $response = $this->withSession([$guardName => $this->ownerB->id])->get('/alpha/dashboard');

        $response->assertRedirect(url('/alpha/login'));
        $this->assertGuest();
    }
}

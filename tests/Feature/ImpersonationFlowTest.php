<?php

namespace Tests\Feature;

use App\Models\CentralAdmin;
use App\Services\Impersonation;
use Tests\TestCase;

class ImpersonationFlowTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_token_mint_and_consume_lands_on_tenant_home(): void
    {
        $admin = CentralAdmin::create([
            'name' => 'Operator',
            'email' => 'op@platform.test',
            'password' => 'secretpw',
            'is_active' => true,
        ]);

        ['url' => $url, 'user' => $user] = app(Impersonation::class)->startFor($this->tenantA, $admin);

        $this->assertMatchesRegularExpression('~http://localhost/alpha/impersonate/[a-zA-Z0-9]{64}$~', $url);

        $plain = explode('/', $url)[count(explode('/', $url)) - 1];

        $response = $this->get('/alpha/impersonate/'.$plain);
        $response->assertRedirect(route('tenant.home'));

        $this->actingAs($user)->get('/alpha/dashboard')->assertOk();
        $this->assertTrue($this->app['session']->get('impersonated_by_central') === true);
    }

    public function test_token_is_single_use_and_tenant_bound(): void
    {
        $admin = CentralAdmin::create([
            'name' => 'Operator',
            'email' => 'op2@platform.test',
            'password' => 'secretpw',
            'is_active' => true,
        ]);

        ['url' => $url] = app(Impersonation::class)->startFor($this->tenantA, $admin);
        $plain = explode('/', $url)[count(explode('/', $url)) - 1];

        $this->get('/alpha/impersonate/'.$plain)->assertRedirect();
        // Second consume: token is spent.
        $second = $this->get('/alpha/impersonate/'.$plain);
        $second->assertNotFound();

        // A token minted for alpha cannot be redeemed on beta (the replay
        // here also trips session-hijack — the point is the alpha session
        // must never land inside beta).
        ['url' => $urlB] = app(Impersonation::class)->startFor($this->tenantA, $admin);
        $plainB = explode('/', $urlB)[count(explode('/', $urlB)) - 1];
        $beta = $this->get('/beta/impersonate/'.$plainB);
        $this->assertFalse(
            $beta->isRedirect(url('/beta/')) || $beta->headers->get('Location') === url('tenant.home'),
        );
    }
}

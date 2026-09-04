<?php

namespace Tests\Feature;

use App\Models\CentralAdmin;
use App\Services\Impersonation;
use Tests\TestCase;

class ImpersonationReturnToCentralTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_central_admin_can_return_to_admin_panel_after_impersonating(): void
    {
        $admin = CentralAdmin::create([
            'name' => 'Operator',
            'email' => 'op-return@platform.test',
            'password' => 'secretpw',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'central');

        $before = $this->get('/admin');
        $this->assertSame(200, $before->getStatusCode(), 'admin unreachable before impersonating');

        ['url' => $url] = app(Impersonation::class)->startFor($this->tenantA, $admin);
        $plain = explode('/', $url)[count(explode('/', $url)) - 1];

        $consume = $this->get('/alpha/impersonate/'.$plain);
        $consume->assertRedirect('http://localhost/alpha');

        $after = $this->get('/admin');
        $this->assertSame(200, $after->getStatusCode(), 'admin unreachable after impersonating — regression');
    }
}

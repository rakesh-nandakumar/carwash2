<?php

namespace Tests\Feature;

use App\Services\CurrentContext;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_no_tenant_bound_inside_simulated_web_request_returns_zero_rows(): void
    {
        $count = CurrentContext::simulateWebRequest(function () {
            return \App\Models\Job::query()->count();
        });

        $this->assertSame(0, $count);
    }

    public function test_console_with_no_tenant_bound_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        \App\Models\Job::query()->count();
    }

    public function test_run_without_tenant_passes_through(): void
    {
        $count = app(CurrentContext::class)->runWithoutTenant(function () {
            return \App\Models\User::query()->count();
        });

        $this->assertSame(2, $count);
    }

    public function test_run_for_tenant_scopes_query(): void
    {
        app(CurrentContext::class)->runForTenant($this->tenantA->id, function () {
            $this->assertSame(1, \App\Models\User::query()->count());
            $this->assertSame($this->ownerA->id, \App\Models\User::first()->id);
        });
    }

    public function test_create_without_tenant_throws_even_with_explicit_tenant_id(): void
    {
        $this->expectException(\RuntimeException::class);
        app(CurrentContext::class)->runWithoutTenant(function () {
            \App\Models\Customer::create([
                'tenant_id' => $this->tenantA->id,
                'customer_code' => 'X-1',
                'full_name' => 'Ghost',
                'phone' => '0770000000',
            ]);
        });
    }

    public function test_scope_without_tenant_scope_escape_hatch_works(): void
    {
        $ids = \App\Models\User::query()->withoutTenantScope()->pluck('id')->all();
        $this->assertCount(2, $ids);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Services\TenantModules;
use Tests\TestCase;

class ModuleEnforcementTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_super_admin_is_403_when_module_disabled(): void
    {
        // Disable INVENTORY (item_master + categories) for tenant A.
        \App\Models\TenantModule::updateOrCreate(
            ['tenant_id' => $this->tenantA->id, 'module_key' => \App\Support\ModuleCatalog::INVENTORY],
            ['is_enabled' => false],
        );
        TenantModules::flush($this->tenantA->id);
        // users.role = 'super_admin' bypasses hasPermission() via isAdmin() —
        // the module gate must NOT be bypassable by it.
        $superAdmin = $this->withRoleUser($this->tenantA, 'super_admin');

        $response = $this->actingAs($superAdmin)->get('/alpha/inventory');
        $response->assertForbidden();

        $response = $this->actingAs($superAdmin)->get('/alpha/categories');
        $response->assertForbidden();

        // Other modules still work for the same user.
        $this->actingAs($superAdmin)->get('/alpha/dashboard')->assertOk();
    }

    public function test_sidebar_uses_can_access(): void
    {
        \App\Models\TenantModule::updateOrCreate(
            ['tenant_id' => $this->tenantA->id, 'module_key' => \App\Support\ModuleCatalog::BILLING],
            ['is_enabled' => false],
        );
        TenantModules::flush($this->tenantA->id);

        $body = $this->actingAs($this->ownerA)->get('/alpha/dashboard')->getContent();

        $this->assertStringNotContainsString('route("invoices.index")', $body);
        $this->assertStringNotContainsString('route(&#039;invoices.index&#039;)', $body);
    }

    private function withRoleUser(Tenant $tenant, string $roleSlug): \App\Models\User
    {
        return app(\App\Services\CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant, $roleSlug) {
            $business = \App\Models\Business::first();
            $user = \App\Models\User::create([
                'business_id' => $business->id,
                'name' => 'Super '.$tenant->name,
                'email' => "super-{$roleSlug}@{$tenant->slug}.test",
                'password' => bcrypt('password'),
                'role' => $roleSlug,
                'active' => true,
            ]);
            $role = \App\Models\Role::query()->withoutTenantScope()
                ->where('tenant_id', $tenant->id)->where('slug', $roleSlug)->first();
            $user->roles()->attach($role->id);
            $user->flushPermissionCache();

            return $user;
        });
    }
}

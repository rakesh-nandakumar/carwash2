<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrentContext;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Hash;

/**
 * Shared builders for tenancy tests — two tenants, each with identical demo
 * data, plus the permissions/roles machinery the app's authorisation relies on.
 */
trait CreatesTenancyFixtures
{
    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $ownerA;

    protected User $ownerB;

    protected function setUpTenancy(): void
    {
        $this->tenantA = Tenant::create(['name' => 'Alpha Wash', 'slug' => 'alpha', 'status' => 'active']);
        $this->tenantB = Tenant::create(['name' => 'Beta Wash', 'slug' => 'beta', 'status' => 'active']);

        app(CurrentContext::class)->runForTenant($this->tenantA->id, function () {
            app(PermissionService::class)->syncDefaultRoles($this->tenantA->id);
        });
        app(CurrentContext::class)->runForTenant($this->tenantB->id, function () {
            app(PermissionService::class)->syncDefaultRoles($this->tenantB->id);
        });

        app(\Database\Seeders\SettingsSeeder::class)->run($this->tenantA->id);
        app(\Database\Seeders\SettingsSeeder::class)->run($this->tenantB->id);

        foreach ([$this->tenantA, $this->tenantB] as $tenant) {
            foreach (\App\Support\ModuleCatalog::keys() as $moduleKey) {
                \App\Models\TenantModule::create([
                    'tenant_id' => $tenant->id,
                    'module_key' => $moduleKey,
                    'is_enabled' => true,
                ]);
            }
            \App\Services\TenantModules::flush($tenant->id);
        }

        $this->ownerA = $this->makeUser($this->tenantA, 'owner-a@fixtures.test', 'AAA-0001');
        $this->ownerB = $this->makeUser($this->tenantB, 'owner-b@fixtures.test', 'BBB-0001');
    }

    private function makeUser(Tenant $tenant, string $email, string $businessCode): User
    {
        return app(CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant, $email, $businessCode) {
            $business = Business::create([
                'name' => $tenant->name,
                'code' => $businessCode,
            ]);

            $branch = \App\Models\Branch::create([
                'business_id' => $business->id,
                'name' => $tenant->name.' Branch',
                'code' => $businessCode.'-B',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'name' => $tenant->name.' Owner',
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'owner',
                'active' => true,
            ]);

            $role = \App\Models\Role::query()->withoutTenantScope()
                ->where('tenant_id', $tenant->id)->where('slug', 'owner')->first();
            $user->roles()->attach($role->id);
            $user->flushPermissionCache();

            return $user;
        });
    }
}
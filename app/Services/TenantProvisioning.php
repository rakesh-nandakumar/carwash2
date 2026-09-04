<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Support\ModuleCatalog;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Everything a brand-new tenant needs before its business can actually log in
 * and use it: a default business/branch, its own private copy of the
 * business-scoped system roles (see RoleSeeder), every business module
 * enabled by default, its settings rows seeded, and one full-admin user —
 * created here rather than left for the tenant to self-register, since
 * master control is the only place tenants are ever created (see
 * Central\TenantController).
 */
class TenantProvisioning
{
    public function provision(Tenant $tenant, string $adminEmail, ?string $adminName = null): User
    {
        return DB::transaction(function () use ($tenant, $adminEmail, $adminName) {
            return app(CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant, $adminEmail, $adminName) {
                $business = $this->createDefaultBusiness($tenant);

                app(RoleSeeder::class)->seedRolesForBusiness($business);

                $this->enableDefaultModules($tenant);

                // Without this a new tenant has no `settings` rows at all, so its
                // app falls back to hardcoded defaults and master control's
                // Settings tab has nothing to update (see App\Services\Settings).
                app(SettingsSeeder::class)->run($tenant->id);

                return $this->createAdminUser($tenant, $business, $adminEmail, $adminName);
            });
        });
    }

    private function createDefaultBusiness(Tenant $tenant): Business
    {
        $business = Business::create([
            'name' => $tenant->name,
            'code' => Str::upper(Str::slug($tenant->slug, '')).'-'.$tenant->id,
        ]);

        Branch::create([
            'business_id' => $business->id,
            'name' => 'Main Branch',
            'code' => 'MAIN',
        ]);

        return $business;
    }

    private function enableDefaultModules(Tenant $tenant): void
    {
        foreach (ModuleCatalog::keys() as $moduleKey) {
            TenantModule::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'module_key' => $moduleKey],
                ['is_enabled' => true],
            );
        }
    }

    private function createAdminUser(Tenant $tenant, Business $business, string $email, ?string $name): User
    {
        $fullAdminRole = Role::query()
            ->where('tenant_id', $tenant->id)
            ->where('business_id', $business->id)
            ->where('is_full_admin', true)
            ->firstOrFail();

        $user = User::create([
            'tenant_id' => $tenant->id,
            'business_id' => $business->id,
            'name' => $name ?: "{$tenant->name} Admin",
            'email' => $email,
            // Impersonation (see App\Services\Impersonation) is the intended
            // way in — a random, never-communicated password keeps direct
            // login unusable for the tenant's most privileged account.
            'password' => Hash::make(Str::random(40)),
            'role' => 'super_admin',
            'active' => true,
        ]);

        $user->roles()->attach($fullAdminRole->id);

        return $user;
    }
}

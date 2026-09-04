<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Support\ModuleCatalog;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Everything a brand-new tenant needs before its business can actually log in
 * and use it: its own private copy of the baseline system roles, every
 * business module enabled by default, its settings rows seeded, and one
 * super_admin user — created here rather than left for the tenant to
 * self-register, since master control is the only place tenants are ever
 * created (see Central\TenantController).
 */
class TenantProvisioning
{
    public function provision(Tenant $tenant, string $adminEmail, ?string $adminName = null): User
    {
        return DB::transaction(function () use ($tenant, $adminEmail, $adminName) {
            app(PermissionService::class)->syncDefaultRoles($tenant->id);

            $this->enableDefaultModules($tenant);

            // Without this a new tenant has no `settings` rows at all, so its
            // app falls back to hardcoded defaults and master control's
            // Settings tab has nothing to update (see App\Services\Settings).
            app(SettingsSeeder::class)->run($tenant->id);

            return $this->createAdminUser($tenant, $adminEmail, $adminName);
        });
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

    private function createAdminUser(Tenant $tenant, string $email, ?string $name): User
    {
        $superAdminRole = Role::query()
            ->withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('slug', 'super_admin')
            ->firstOrFail();

        $user = User::create([
            'tenant_id' => $tenant->id,
            'business_id' => $this->firstBusinessId($tenant),
            'name' => $name ?: "{$tenant->name} Admin",
            'email' => $email,
            // Impersonation (see App\Services\Impersonation) is the intended
            // way in — a random, never-communicated password keeps direct
            // login unusable for the tenant's most privileged account.
            'password' => Hash::make(Str::random(40)),
            'role' => 'super_admin',
            'active' => true,
        ]);

        $user->roles()->attach($superAdminRole->id);

        return $user;
    }

    private function firstBusinessId(Tenant $tenant): ?int
    {
        $id = DB::table('businesses')
            ->where('tenant_id', $tenant->id)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }
}

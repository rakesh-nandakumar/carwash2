<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Services\CurrentContext;
use App\Services\PermissionService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(PermissionService::class);

        // The permission catalog is global (code, not tenant data) —
        // run it with no tenant bound.
        app(CurrentContext::class)->runWithoutTenant(function () use ($service) {
            $service->syncDefaultPermissions();
        });

        // Roles are per-tenant: each tenant gets its own private copy of the
        // 7 system roles and their grants.
        foreach (Tenant::query()->get() as $tenant) {
            app(CurrentContext::class)->runForTenant($tenant->id, function () use ($service, $tenant) {
                $service->syncDefaultRoles($tenant->id);
            });
        }
    }
}

<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\TenantModule;
use App\Support\ModuleCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Per-tenant module licensing — orthogonal to role/permission (see
 * PermissionMiddleware and the sidebar's canAccess(), the two enforcement
 * call sites). A tenant's own super_admin still can't reach a module the
 * platform operator hasn't enabled for that tenant; that's the point of this
 * being a separate check from hasPermission(), not folded into it.
 */
class TenantModules
{
    /**
     * @return Collection<int, string> enabled catalog module keys
     */
    public static function enabledKeysFor(int $tenantId): Collection
    {
        return Cache::remember(
            "tenant:{$tenantId}:enabled_modules",
            now()->addHour(),
            fn () => TenantModule::query()
                ->where('tenant_id', $tenantId)
                ->where('is_enabled', true)
                ->pluck('module_key'),
        );
    }

    public static function flush(int $tenantId): void
    {
        Cache::forget("tenant:{$tenantId}:enabled_modules");
    }

    /**
     * The permission-module key a permission slug belongs to (carwash uses
     * `{action}_{module}` names where both halves contain underscores, so the
     * name cannot be split on a dot like the reference does). Resolved via
     * the `permissions.module` column instead, cached globally — permissions
     * are a global table (see PermissionService), so this key stays global
     * while `settings:all:*` and `tenant:*:enabled_modules` stay tenant-keyed.
     */
    public static function moduleKeyForPermission(string $slug): ?string
    {
        $map = Cache::rememberForever(
            'perm_module_map',
            fn () => Permission::query()->pluck('module', 'slug')->all(),
        );

        return $map[$slug] ?? null;
    }

    public static function flushPermissionModuleMap(): void
    {
        Cache::forget('perm_module_map');
    }

    /**
     * Whether the CURRENT tenant context has the given fine-grained module
     * key's licensed feature group enabled. Core module keys (not covered by
     * any catalog module) always pass. Central admins bypass entirely,
     * mirroring TenantScope — but NOT console execution: the enforcement call
     * sites (PermissionMiddleware, User::canAccess) must report real
     * licensing state, and a test exercising them runs in console.
     */
    public static function isEnabled(string $fineModuleKey): bool
    {
        if (Auth::guard('central')->check()) {
            return true;
        }

        $catalogKey = ModuleCatalog::catalogKeyFor($fineModuleKey);
        if ($catalogKey === null) {
            return true; // core — always enabled
        }

        $tenantId = app(CurrentContext::class)->tenantId();
        if ($tenantId === null) {
            return false;
        }

        return self::enabledKeysFor($tenantId)->contains($catalogKey);
    }
}

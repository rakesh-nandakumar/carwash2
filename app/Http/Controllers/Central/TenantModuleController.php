<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAdmin;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Services\AuditService;
use App\Services\CurrentContext;
use App\Services\TenantModules;
use App\Support\ModuleCatalog;
use Illuminate\Http\Request;

/**
 * Per-tenant module licensing — which feature groups (see
 * App\Support\ModuleCatalog) a tenant's own users can reach at all, enforced
 * regardless of role (PermissionMiddleware + sidebar canAccess()).
 */
class TenantModuleController extends Controller
{
    public function index(Tenant $tenant)
    {
        $modules = $this->modulesFor($tenant);

        return view('central.tenants.modules', compact('tenant', 'modules'));
    }

    public function update(Request $request, Tenant $tenant, string $moduleKey)
    {
        if (! array_key_exists($moduleKey, ModuleCatalog::definitions())) {
            abort(404, 'Unknown module.');
        }

        $data = $request->validate(['enabled' => ['required', 'boolean']]);

        TenantModule::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'module_key' => $moduleKey],
            ['is_enabled' => $data['enabled'], 'granted_by' => $request->user('central')->id],
        );

        TenantModules::flush($tenant->id);

        // Log module change in central context only
        app(AuditService::class)->log(
            'tenant.module_changed',
            "Module '{$moduleKey}' " . ($data['enabled'] ? 'enabled' : 'disabled') . " for tenant '{$tenant->name}'",
            'info',
            'central_admin',
            $request->user('central')->email,
            [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'module_key' => $moduleKey,
                'enabled' => $data['enabled'],
            ]
        );

        return back()->with('success', 'Module '.$moduleKey.($data['enabled'] ? ' enabled.' : ' disabled.'));
    }

    private function modulesFor(Tenant $tenant): array
    {
        $enabled = TenantModule::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_enabled', true)
            ->pluck('module_key');

        return collect(ModuleCatalog::definitions())->map(fn (array $definition, string $key) => [
            'key' => $key,
            'name' => $definition['name'],
            'description' => $definition['description'],
            'enabled' => $enabled->contains($key),
        ])->values()->all();
    }
}

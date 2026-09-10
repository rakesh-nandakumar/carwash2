<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\CurrentContext;
use App\Services\TenantModules;
use App\Support\ModuleCatalog;
use App\Support\TenantStatus;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * How master control provisions and manages tenant rows. A tenant's own
 * users/settings/modules are reachable from here for the platform operator.
 */
class TenantController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::query()
            ->withCount(['users'])
            ->when($request->filled('q'), fn ($q, $v) => $q->where('name', 'like', "%{$v}%")->orWhere('slug', 'like', "%{$v}%"))
            ->orderBy('name')
            ->get();

        return view('central.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('central.tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:63', 'alpha_dash', 'lowercase', Rule::unique('tenants', 'slug'), new \App\Rules\ReservedSlug],
            'status' => ['nullable', 'string', Rule::in([TenantStatus::TRIAL, TenantStatus::ACTIVE, TenantStatus::SUSPENDED, TenantStatus::CANCELLED])],
            'trial_ends_at' => ['nullable', 'date'],
            'admin_email' => ['required', 'string', 'email', 'max:255'],
            'admin_name' => ['nullable', 'string', 'max:150'],
            'till_name' => ['required', 'string', 'max:255'],
            'till_code' => ['required', 'string', 'max:50'],
            'till_opening_balance' => ['required', 'numeric', 'min:0'],
            'till_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'status' => $data['status'] ?? TenantStatus::ACTIVE,
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
            'created_by' => $request->user('central')->id,
        ]);

        $tillConfig = [
            'name' => $data['till_name'],
            'code' => $data['till_code'],
            'opening_balance' => $data['till_opening_balance'],
            'description' => $data['till_description'] ?? null,
        ];

        app(\App\Services\TenantProvisioning::class)->provision($tenant, $data['admin_email'], $data['admin_name'] ?? null, $tillConfig);

        return redirect()->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant provisioned. It is now reachable at /'.$tenant->slug.'/login (access via impersonation).');
    }

    public function show(Tenant $tenant)
    {
        $tenant->loadCount(['users', 'auditLogs']);

        $modules = $this->modulesFor($tenant);
        $owner = $this->ownerAdmin($tenant);
        $settingsStats = collect(SettingsSeeder::definitions())->count();

        return view('central.tenants.show', compact('tenant', 'modules', 'owner', 'settingsStats'));
    }

    public function tills(Tenant $tenant)
    {
        $tills = app(CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant) {
            return \App\Models\Till::with('currentUser')
                ->orderBy('name')
                ->get();
        });

        return view('central.tenants.tills', compact('tenant', 'tills'));
    }

    public function createTill(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);

        app(CurrentContext::class)->runForTenant($tenant->id, function () use ($data) {
            \App\Models\Till::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'opening_balance' => $data['opening_balance'],
                'is_active' => true,
            ]);
        });

        return back()->with('success', 'Till created successfully.');
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'status' => ['sometimes', 'string', Rule::in([TenantStatus::TRIAL, TenantStatus::ACTIVE, TenantStatus::SUSPENDED, TenantStatus::CANCELLED])],
            'trial_ends_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $tenant->update([...$data, 'updated_by' => $request->user('central')->id]);

        return back()->with('success', 'Tenant updated.');
    }

    public function suspend(Request $request, Tenant $tenant)
    {
        abort_if($tenant->isSuspended(), 422, 'Tenant is already suspended.');

        $tenant->update([
            'status' => TenantStatus::SUSPENDED,
            'updated_by' => $request->user('central')->id,
        ]);

        return back()->with('success', 'Tenant suspended.');
    }

    public function resume(Request $request, Tenant $tenant)
    {
        abort_unless($tenant->isSuspended(), 422, 'Only a suspended tenant can be resumed.');

        $tenant->update([
            'status' => TenantStatus::ACTIVE,
            'updated_by' => $request->user('central')->id,
        ]);

        return back()->with('success', 'Tenant resumed.');
    }

    /**
     * Regenerates the tenant admin's password (never-communicated by design —
     * impersonation is the access path, see TenantProvisioning). Defaults to
     * "password" so operators can hand over a known one; the caller may
     * override it. The password is returned here and nowhere else.
     */
    public function resetAdminPassword(Request $request, Tenant $tenant)
    {
        $admin = $this->ownerAdmin($tenant);
        abort_if($admin === null, 422, 'This tenant has no administrator user yet.');

        $data = $request->validate([
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ]);

        $password = $data['password'] ?? 'password';

        app(CurrentContext::class)->runForTenant($tenant->id, function () use ($admin, $password): void {
            $admin->update(['password' => $password]);
        });

        return back()->with('success', "Password reset to \"{$password}\" for {$admin->email}.");
    }

    /**
     * This tenant's full audit trail — the master-control view of everything
     * that happened inside the tenant AND every central action on it.
     */
    public function auditLogs(Request $request, Tenant $tenant)
    {
        $logs = \App\Models\AuditLog::query()
            ->withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->with('user:id,name,email')
            ->latest('created_at')
            ->paginate(20);

        return view('central.tenants.audit', compact('tenant', 'logs'));
    }

    private function modulesFor(Tenant $tenant): array
    {
        $enabled = TenantModules::enabledKeysFor($tenant->id);

        return collect(ModuleCatalog::definitions())->map(fn (array $definition, string $key) => [
            'key' => $key,
            'name' => $definition['name'],
            'description' => $definition['description'],
            'enabled' => $enabled->contains($key),
        ])->values()->all();
    }

    /**
     * The tenant's administrator user — role_user → roles.slug = 'super_admin',
     * falling back to users.role === 'super_admin', then 'owner'.
     */
    private function ownerAdmin(Tenant $tenant): ?\App\Models\User
    {
        return \App\Models\User::query()
            ->withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereHas('roles', fn ($qq) => $qq->where('slug', 'super_admin'))
                    ->orWhereIn('role', ['super_admin', 'owner']);
            })
            ->orderBy('id')
            ->first();
    }
}

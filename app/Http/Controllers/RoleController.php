<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionEscalationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $businessId = auth()->user()->business_id;

        $roles = Role::withCount('users')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('business_id', $businessId)
            ->orderBy('name')
            ->paginate(20);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view('roles.create', compact('permissions'));
    }

    public function store(
        Request $request,
        PermissionEscalationService $escalation
    ) {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'exists:permissions,id',
            ],
        ]);

        $permissions = Permission::whereIn(
            'id',
            $validated['permissions'] ?? []
        )->get();

        $escalation->ensureCanGrantPermissions(
            auth()->user(),
            $permissions->pluck('slug')->all()
        );

        $role = Role::create([
            'tenant_id' => auth()->user()->tenant_id,
            'business_id' => auth()->user()->business_id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . uniqid(),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'is_active' => true,
            'is_full_admin' => false,
        ]);

        $role->permissions()->sync(
            $permissions->pluck('id')
        );

        $this->clearRoleUsersCache($role);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        abort_unless(
            $role->tenant_id === auth()->user()->tenant_id
                && $role->business_id === auth()->user()->business_id,
            404
        );

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $selected = $role->permissions
            ->pluck('id')
            ->all();

        return view(
            'roles.edit',
            compact('role', 'permissions', 'selected')
        );
    }

    public function update(
        Request $request,
        Role $role,
        PermissionEscalationService $escalation
    ) {
        $this->authorize('update', $role);

        abort_unless(
            $role->tenant_id === auth()->user()->tenant_id
                && $role->business_id === auth()->user()->business_id,
            404
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'exists:permissions,id',
            ],
        ]);

        $permissions = Permission::whereIn(
            'id',
            $validated['permissions'] ?? []
        )->get();

        $escalation->ensureCanGrantPermissions(
            auth()->user(),
            $permissions->pluck('slug')->all()
        );

        if (
            $role->is_full_admin
            && ! auth()->user()->isFullAdmin()
        ) {
            abort(403);
        }

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync(
            $permissions->pluck('id')
        );

        $role->users->each(
            fn ($user) => $user->clearPermissionCache()
        );

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        abort_unless(
            $role->tenant_id === auth()->user()->tenant_id
                && $role->business_id === auth()->user()->business_id,
            404
        );

        if ($role->is_system || $role->is_full_admin) {
            return back()->with(
                'error',
                'System or Full Administrator roles cannot be deleted.'
            );
        }

        if ($role->users()->exists()) {
            return back()->with(
                'error',
                'This role is assigned to users and cannot be deleted.'
            );
        }

        $role->delete();

        return back()->with(
            'success',
            'Role deleted successfully.'
        );
    }

    private function clearRoleUsersCache(Role $role): void
    {
        $role->load('users');

        $role->users->each(
            fn ($user) => $user->clearPermissionCache()
        );
    }
}
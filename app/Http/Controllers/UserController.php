<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermissionOverride;
use App\Services\PermissionEscalationService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $businessId = auth()->user()->business_id;
        $search = $request->get('search');

        $query = User::with('roles')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('business_id', $businessId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $businessId = auth()->user()->business_id;

        $roles = Role::where('tenant_id', auth()->user()->tenant_id)
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view(
            'users.create',
            compact('roles', 'permissions')
        );
    }

    public function store(
        Request $request,
        PermissionEscalationService $escalation
    ) {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],
            'permission_overrides' => 'nullable|array',
            'permission_overrides.*' => 'nullable|in:allow,deny',
            'active' => 'boolean',
        ]);

        $actor = auth()->user();

        if (! empty($validated['roles'])) {
            abort_unless(
                $actor->hasPermissionTo('roles.assign'),
                403,
                'You do not have permission to assign roles.'
            );
        }

        if (! empty($validated['permission_overrides'])) {
            abort_unless(
                $actor->hasPermissionTo('users.manage_permissions'),
                403,
                'You do not have permission to manage user permissions.'
            );
        }

        $tenantId = $actor->tenant_id;
        $businessId = $actor->business_id;

        /*
         * Only allow roles belonging to the current tenant/business.
         */
        $roles = Role::where('tenant_id', $tenantId)
            ->where('business_id', $businessId)
            ->whereIn('id', $validated['roles'] ?? [])
            ->where('is_active', true)
            ->with('permissions')
            ->get();

        foreach ($roles as $role) {
            $escalation->ensureCanAssignRole(
                $actor,
                $role
            );
        }

        $user = DB::transaction(function () use (
            $validated,
            $tenantId,
            $businessId,
            $roles,
            $escalation,
            $actor
        ) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'staff',
                // Required tenant/business ownership.
                'tenant_id' => $tenantId,
                'business_id' => $businessId,
                'active' => $validated['active'] ?? true,
            ]);

            $user->roles()->sync(
                $roles->pluck('id')
            );

            $this->syncOverrides(
                $user,
                $validated['permission_overrides'] ?? [],
                $escalation,
                $actor
            );

            // Log role assignment
            $this->auditService->log('user.roles_assigned', "Roles assigned to user {$user->name}", 'warning', 'tenant_user', $actor->email, [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'roles' => $roles->pluck('name')->toArray(),
            ]);

            // Log permission changes if overrides were provided
            if (!empty($validated['permission_overrides'])) {
                $this->auditService->log('user.permissions_assigned', "Permission overrides assigned to user {$user->name}", 'warning', 'tenant_user', $actor->email, [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'permission_overrides' => $validated['permission_overrides'],
                ]);
            }

            return $user;
        });

        $user->clearPermissionCache();

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $this->ensureSameBusiness($user);

        $actor = auth()->user();
        $tenantId = $actor->tenant_id;
        $businessId = $actor->business_id;

        $roles = Role::where('tenant_id', $tenantId)
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $userRoles = $user->roles
            ->pluck('id')
            ->all();

        $overrides = $user->permissionOverrides()
            ->pluck('type', 'permission_id')
            ->all();

        $effectivePermissions = $user->effectivePermissions();

        return view(
            'users.edit',
            compact(
                'user',
                'roles',
                'permissions',
                'userRoles',
                'overrides',
                'effectivePermissions'
            )
        );
    }

    public function update(
        Request $request,
        User $user,
        PermissionEscalationService $escalation
    ) {
        $this->authorize('update', $user);

        $this->ensureSameBusiness($user);

        $actor = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],
            'permission_overrides' => 'nullable|array',
            'permission_overrides.*' => 'nullable|in:allow,deny',
            'active' => 'boolean',
        ]);

        if (! empty($validated['roles'])) {
            abort_unless(
                $actor->hasPermissionTo('roles.assign'),
                403,
                'You do not have permission to assign roles.'
            );
        }

        if (! empty($validated['permission_overrides'])) {
            abort_unless(
                $actor->hasPermissionTo('users.manage_permissions'),
                403,
                'You do not have permission to manage user permissions.'
            );
        }

        $tenantId = $actor->tenant_id;
        $businessId = $actor->business_id;

        /*
         * Only allow roles belonging to the current tenant/business.
         */
        $roles = Role::where('tenant_id', $tenantId)
            ->where('business_id', $businessId)
            ->whereIn('id', $validated['roles'] ?? [])
            ->where('is_active', true)
            ->with('permissions')
            ->get();

        foreach ($roles as $role) {
            $escalation->ensureCanAssignRole(
                $actor,
                $role
            );
        }

        /*
         * A non-Full-Administrator cannot modify another
         * Full Administrator.
         */
        if (
            $user->isFullAdmin()
            && ! $actor->isFullAdmin()
        ) {
            abort(403);
        }

        // Capture old overrides and roles before transaction
        $oldOverrides = $user->permissionOverrides()->pluck('type', 'permission_id')->toArray();
        $oldRoles = $user->roles->pluck('name')->toArray();

        DB::transaction(function () use (
            $validated,
            $user,
            $roles,
            $escalation,
            $actor
        ) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'active' => $validated['active'] ?? false,
            ]);

            if (! empty($validated['password'])) {
                $user->update([
                    'password' => Hash::make(
                        $validated['password']
                    ),
                ]);
            }

            $user->roles()->sync(
                $roles->pluck('id')
            );

            $this->syncOverrides(
                $user,
                $validated['permission_overrides'] ?? [],
                $escalation,
                $actor
            );
        });

        // Log role changes if roles were modified
        $newRoles = $user->roles->pluck('name')->toArray();
        if ($oldRoles !== $newRoles) {
            $this->auditService->log('user.roles_modified', "Roles modified for user {$user->name}", 'warning', 'tenant_user', $actor->email, [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'old_roles' => $oldRoles,
                'new_roles' => $newRoles,
            ]);
        }

        // Log permission changes if overrides were modified
        $newOverrides = $user->permissionOverrides()->pluck('type', 'permission_id')->toArray();
        if ($oldOverrides !== $newOverrides) {
            $this->auditService->log('user.permissions_modified', "Permission overrides modified for user {$user->name}", 'warning', 'tenant_user', $actor->email, [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'old_overrides' => $oldOverrides,
                'new_overrides' => $newOverrides,
            ]);
        }

        $user->clearPermissionCache();

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $this->ensureSameBusiness($user);

        $actor = auth()->user();

        /*
         * Prevent self deletion.
         */
        if ($user->id === $actor->id) {
            return back()->with(
                'error',
                'You cannot delete your own account.'
            );
        }

        /*
         * A non-Full-Administrator cannot delete
         * a Full Administrator.
         */
        if (
            $user->isFullAdmin()
            && ! $actor->isFullAdmin()
        ) {
            abort(403);
        }

        $user->delete();

        return back()->with(
            'success',
            'User deleted successfully.'
        );
    }

    private function syncOverrides(
        User $user,
        array $overrides,
        PermissionEscalationService $escalation,
        User $actor
    ): void {
        $permissionIds = array_keys($overrides);

        if (! $permissionIds) {
            $user->permissionOverrides()->delete();
            return;
        }

        $permissions = Permission::whereIn(
            'id',
            $permissionIds
        )->get();

        /*
         * Users may only grant permissions that they
         * themselves possess, unless they are Full Administrator.
         */
        $escalation->ensureCanGrantPermissions(
            $actor,
            $permissions->pluck('slug')->all()
        );

        /*
         * Replace existing overrides with the submitted set.
         */
        $user->permissionOverrides()->delete();

        foreach ($permissions as $permission) {
            $type = $overrides[$permission->id] ?? null;

            if (! in_array($type, ['allow', 'deny'], true)) {
                continue;
            }

            UserPermissionOverride::create([
                'user_id' => $user->id,
                'permission_id' => $permission->id,
                'type' => $type,
                'granted_by' => $actor->id,
                'granted_at' => now(),
            ]);
        }
    }

    private function ensureSameBusiness(User $user): void
    {
        abort_unless(
            $user->tenant_id === auth()->user()->tenant_id
            && $user->business_id === auth()->user()->business_id,
            404
        );
    }
}
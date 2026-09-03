<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('module');
        return view('users.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|exists:roles,slug',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'staff',
            'business_id' => auth()->user()->business_id,
            'active' => $validated['active'] ?? true,
        ]);

        // Assign role if provided
        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        // Assign direct permissions if provided
        if (!empty($validated['permissions'])) {
            $role = Role::where('slug', 'custom_' . $user->id)->first();
            if (!$role) {
                $role = Role::create([
                    'name' => 'Custom for ' . $user->name,
                    'slug' => 'custom_' . $user->id,
                    'is_system' => false
                ]);
            }
            $role->permissions()->sync($validated['permissions']);
            $user->roles()->syncWithoutDetaching([$role->id]);
        } elseif (empty($validated['role'])) {
            // If no role and no permissions selected, assign a default staff role
            $defaultRole = Role::where('slug', 'staff')->first();
            if ($defaultRole) {
                $user->assignRole('staff');
            }
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('module');
        $userRoles = $user->roles->pluck('slug')->toArray();
        
        // Get all permissions from all roles assigned to the user
        $userPermissions = [];
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $userPermissions[] = $permission->id;
            }
        }
        $userPermissions = array_unique($userPermissions);
        
        return view('users.edit', compact('user', 'roles', 'permissions', 'userRoles', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|exists:roles,slug',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'active' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? 'staff',
            'active' => $validated['active'] ?? true,
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        // Sync predefined roles (keep custom role)
        $customRoleSlug = 'custom_' . $user->id;
        $user->roles()->where('slug', '!=', $customRoleSlug)->detach();
        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        // Handle custom permissions
        if (!empty($validated['permissions'])) {
            $role = Role::where('slug', $customRoleSlug)->first();
            if (!$role) {
                $role = Role::create([
                    'name' => 'Custom for ' . $user->name,
                    'slug' => $customRoleSlug,
                    'is_system' => false
                ]);
            }
            $role->permissions()->sync($validated['permissions']);
            $user->roles()->syncWithoutDetaching([$role->id]);
        } else {
            // Remove custom role if no custom permissions selected
            $customRole = Role::where('slug', $customRoleSlug)->first();
            if ($customRole) {
                $user->roles()->detach($customRole->id);
            }
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
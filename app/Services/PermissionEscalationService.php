<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PermissionEscalationService
{
    public function ensureCanGrantPermissions(
        User $actor,
        array $permissionSlugs
    ): void {
        if ($actor->isFullAdmin()) {
            return;
        }

        foreach ($permissionSlugs as $permission) {
            if (! $actor->hasPermissionTo($permission)) {
                throw ValidationException::withMessages([
                    'permissions' => "You cannot grant the permission: {$permission}",
                ]);
            }
        }
    }

    public function ensureCanAssignRole(
        User $actor,
        Role $role
    ): void {
        if ($actor->isFullAdmin()) {
            return;
        }

        if ($role->is_full_admin) {
            throw ValidationException::withMessages([
                'role_id' => 'You cannot assign the full-admin role.',
            ]);
        }

        $permissionSlugs = $role->permissions()
            ->pluck('slug')
            ->all();

        $this->ensureCanGrantPermissions(
            $actor,
            $permissionSlugs
        );
    }
}
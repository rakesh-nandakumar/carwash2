<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('roles.access');
    }

    public function view(User $actor, Role $role): bool
    {
        return $this->sameBusiness($actor, $role)
            && $actor->hasPermissionTo('roles.access');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo('roles.create');
    }

    public function update(User $actor, Role $role): bool
    {
        if (! $this->sameBusiness($actor, $role)) {
            return false;
        }

        if (! $actor->hasPermissionTo('roles.edit')) {
            return false;
        }

        // Full-admin roles may only be modified by a full admin.
        if ($role->is_full_admin && ! $actor->isFullAdmin()) {
            return false;
        }

        return true;
    }

    public function delete(User $actor, Role $role): bool
    {
        if (! $this->sameBusiness($actor, $role)) {
            return false;
        }

        if (! $actor->hasPermissionTo('roles.delete')) {
            return false;
        }

        // System roles cannot be deleted.
        if ($role->is_system) {
            return false;
        }

        // Full-admin roles cannot be deleted.
        if ($role->is_full_admin) {
            return false;
        }

        // A role assigned to users cannot be deleted.
        if ($role->users()->exists()) {
            return false;
        }

        return true;
    }

    private function sameBusiness(User $actor, Role $role): bool
    {
        return $actor->business_id !== null
            && $role->business_id === $actor->business_id;
    }
}
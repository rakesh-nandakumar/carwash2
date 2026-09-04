<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('users.access');
    }

    public function view(User $actor, User $target): bool
    {
        return $this->sameBusiness($actor, $target)
            && $actor->hasPermissionTo('users.access');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo('users.create');
    }

    public function update(User $actor, User $target): bool
    {
        if (! $this->sameBusiness($actor, $target)) {
            return false;
        }

        if (! $actor->hasPermissionTo('users.edit')) {
            return false;
        }

        if ($actor->isFullAdmin()) {
            return true;
        }

        if ($actor->id === $target->id) {
            return true;
        }

        return $this->canManageUser($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        if (! $this->sameBusiness($actor, $target)) {
            return false;
        }

        if (! $actor->hasPermissionTo('users.delete')) {
            return false;
        }

        if ($target->isFullAdmin()) {
            return false;
        }

        return $actor->isFullAdmin()
            || $this->canManageUser($actor, $target);
    }

    private function sameBusiness(User $actor, User $target): bool
    {
        return $actor->business_id !== null
            && $actor->business_id === $target->business_id;
    }

    private function canManageUser(
        User $actor,
        User $target
    ): bool {
        $targetPermissions = $target->effectivePermissions();

        return $actor->hasAllPermissions(
            $targetPermissions
        );
    }
}
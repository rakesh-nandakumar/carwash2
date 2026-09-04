<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    public function userHasPermission(?User $user, string $permission): bool
    {
        return $user?->hasPermissionTo($permission) ?? false;
    }
}
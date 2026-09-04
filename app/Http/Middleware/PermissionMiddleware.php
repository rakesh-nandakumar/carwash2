<?php

namespace App\Http\Middleware;

use App\Services\TenantModules;
use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthorized');
        }

        if (! $user->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        // Licensing gate — strictly OUTSIDE hasPermission(), which
        // isAdmin() short-circuits to true for users whose `users.role`
        // string is super_admin/owner. A disabled module 403s even them:
        // what the operator licenses outranks what the tenant's own roles
        // allow. See App\Services\TenantModules.
        $module = TenantModules::moduleKeyForPermission($permission);
        if ($module !== null && ! TenantModules::isEnabled($module)) {
            abort(403, 'This module is not enabled for your account.');
        }

        return $next($request);
    }
}

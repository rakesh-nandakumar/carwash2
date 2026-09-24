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
            abort(401, 'Unauthorized.');
        }

        if (! $user->hasPermissionTo($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        // Check if the module is enabled for the tenant
        $moduleKey = TenantModules::moduleKeyForPermission($permission);
        if ($moduleKey !== null && !TenantModules::isEnabled($moduleKey)) {
            abort(403, 'This module is not enabled for your tenant.');
        }

        return $next($request);
    }
}
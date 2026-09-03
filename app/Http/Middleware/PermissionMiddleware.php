<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        // Refresh user from database to ensure fresh data
        $freshUser = $user->fresh();
        $freshUser->load('roles.permissions');
        
        // Check if user has the required permission using fresh data
        if (!$freshUser->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }
        
        return $next($request);
    }
}

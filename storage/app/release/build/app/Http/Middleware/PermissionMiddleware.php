<?php

namespace App\Http\Middleware;

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

        return $next($request);
    }
}
<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => App\Http\Middleware\PermissionMiddleware::class,
            'tenant' => App\Http\Middleware\IdentifyTenant::class,
            'central_only' => App\Http\Middleware\EnsureCentralContext::class,
            'central_reset_guard' => App\Http\Middleware\ResetDefaultGuardAfterCentralAuth::class,
        ]);

        // IdentifyTenant must run BEFORE the auth middleware within the web
        // group's priority-sorted pipeline: Authenticate/SubstituteBindings
        // are in Laravel's default middleware priority list, and the sort
        // would otherwise place auth before it (an unauthenticated request to
        // a tenant path would then try to route('login') with no slug bound).
        $middleware->prependToPriorityList(
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            App\Http\Middleware\IdentifyTenant::class,
        );

        // IdentifyTenant runs at the END of the web group — after StartSession
        // (it inspects the session) and SubstituteBindings, but before every
        // route-level middleware (auth, permission): the tenant resolves
        // before any model binding or authorization, and the session-hijack
        // guard can read the session.
        $middleware->appendToGroup('web', App\Http\Middleware\IdentifyTenant::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {})
    ->create();

<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Services\CurrentContext;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // CurrentContext MUST be a singleton: TenantScope and IdentifyTenant
        // read it through app(CurrentContext::class), and a fresh instance on
        // every call would lose the resolved tenant mid-request.
        $this->app->singleton(CurrentContext::class);
    }

    public function boot(): void
    {
        // Laravel's default unauthenticated-redirect fallback is a bare
        // route('login') (see Handler::unauthenticated()), which always
        // resolves to the tenant-scoped `{tenant}/login` route and blows up
        // with a missing-parameter error for the central guard, which has no
        // tenant. Route by context instead.
        Authenticate::redirectUsing(fn () => app(CurrentContext::class)->isCentral()
            ? route('central.login')
            : route('login'));

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        Gate::before(function (User $user, string $ability) {
            if (! $user->active) {
                return false;
            }

            if ($user->isFullAdmin()) {
                return true;
            }

            return null;
        });
    }
}
<?php

namespace App\Providers;

use App\Services\CurrentContext;
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

    public function boot(): void {}
}

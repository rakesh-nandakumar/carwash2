<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Services\CurrentContext;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

/**
 * Applies TenantScope (see there for the exact fail-closed rules) and
 * auto-stamps tenant_id on create from the resolved tenant context.
 *
 * The stamp is a FORCE, never a `??=`: carwash models use `$guarded = []`,
 * so controllers create straight off request data — an inbound tenant_id
 * POST field must not shift a row into another tenant.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $resolved = app(CurrentContext::class)->tenantId();

            if ($resolved !== null) {
                $model->tenant_id = $resolved;
            } elseif ($model->tenant_id === null) {
                throw new RuntimeException(
                    'Creating '.$model::class.' with no tenant bound. Use CurrentContext::runForTenant().'
                );
            }
        });
    }

    /**
     * Central-admin escape hatch — explicitly cross tenants (e.g. "show me
     * tenant #4's settings" from the platform panel) without touching the
     * ambient tenant context every other query relies on.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}

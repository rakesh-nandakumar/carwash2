<?php

namespace App\Support;

use App\Models\Tenant;
use Carbon\Carbon;

/**
 * Whether a tenant may be served at all (IdentifyTenant must fail exactly
 * the same way a request that resolves no tenant does). Anything that isn't
 * unambiguously ACTIVE or a not-yet-expired TRIAL is unreachable — suspended
 * and cancelled tenants are indistinguishable from prefixes that don't exist.
 */
class TenantReachability
{
    public static function check(Tenant $tenant): bool
    {
        return match ($tenant->status) {
            TenantStatus::ACTIVE => true,
            TenantStatus::TRIAL => $tenant->trial_ends_at === null
                || Carbon::parse($tenant->trial_ends_at)->isFuture(),
            default => false,
        };
    }
}

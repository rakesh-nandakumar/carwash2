<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central prefix
    |--------------------------------------------------------------------------
    |
    | The URL path prefix that maps to "master control" (tenant administration)
    | instead of a tenant: hms.com/admin/... is central, hms.com/acme/... is
    | tenant "acme". The same string is what a tenant-slot identity check
    | refuses (ReservedSlug), and a path slug equal to it fails closed rather
    | than granting central — master control is only ever reachable on a
    | central path, never through a tenant hint.
    |
    | bootstrap_slugs: business_id => preferred slug used by
    | seed_tenants_from_businesses so the operator picks real slugs rather
    | than accepting a machine-generated one; missing entries fall back to a
    | ReservedSlug-checked Str::slug($name).
    |
    */

    'central_prefix' => env('TENANCY_CENTRAL_PREFIX', 'admin'),

    'bootstrap_slugs' => [
        // 1 => 'autocare-pro',
    ],

    /*
    |--------------------------------------------------------------------------
    | Host fallback (inert)
    |--------------------------------------------------------------------------
    |
    | Carwash deploys no subdomains today, so this is documented but deaf:
    | TenantHostResolver::resolve() keeps the {slug}.{base}/admin.{base}
    | branch for the future host-based rollout — re-attaching it in
    | IdentifyTenant is the one-line switch.
    |
    */

    'base_domain' => env('TENANCY_BASE_DOMAIN'),

    'min_base_labels' => env('TENANCY_MIN_BASE_LABELS', 2),

];

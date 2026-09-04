<?php

namespace App\Services;

use App\Support\HostContext;

/**
 * Resolves a tenant identity — the URL path prefix — into master-control,
 * tenant or unknown. The single place IdentifyTenant and anything else that
 * needs to name a tenant look it up.
 *
 * resolveFromPath() is the primary (path-prefix) path: a bare slug is a
 * tenant name unless it is the central prefix or empty/garbage, and it NEVER
 * grants central — master control is only ever named by the central path, so
 * a tenant hint can never lasso a central request.
 *
 * resolve() is the HOST fallback kept for the dual-mode window (old
 * {slug}.{base} URL style). It is currently unreferenced; re-attaching it in
 * IdentifyTenant is the one-line switch when a host-based rollout starts.
 *
 * Pure: no request, no database, fully unit-testable.
 */
class TenantHostResolver
{
    public function resolveFromPath(?string $slug): HostContext
    {
        $slug = strtolower(trim((string) $slug));
        $slug = rtrim($slug, '/.');

        if ($slug === '' || $slug === strtolower((string) config('tenancy.central_prefix'))) {
            return HostContext::unknown();
        }

        return HostContext::tenant($slug);
    }

    public function resolve(string $host): HostContext
    {
        $host = $this->normalise($host);

        if ($host === '' || $this->isIpLiteral($host)) {
            return HostContext::unknown();
        }

        $pinned = config('tenancy.base_domain');
        $pinned = is_string($pinned) && trim($pinned) !== '' ? strtolower(trim($pinned)) : null;

        // The pinned base domain itself, bare, is the apex/central host. See
        // the reference implementation for the label-peeling rationale.
        if ($pinned !== null && $host === $pinned) {
            return HostContext::central();
        }

        $labels = explode('.', $host);
        $firstLabel = $labels[0];
        $base = implode('.', array_slice($labels, 1));

        if ($this->isApex($host, $base)) {
            return HostContext::central();
        }

        if ($firstLabel === strtolower((string) config('tenancy.central_prefix'))) {
            return HostContext::central();
        }

        if ($pinned !== null && $base !== $pinned) {
            return HostContext::unknown();
        }

        return HostContext::tenant($firstLabel);
    }

    /**
     * The base domain the given host sits under — used to build tenant URLs
     * ({slug}.{base}) when tenancy.base_domain is unset (relative mode). For
     * the apex host the base IS the whole host.
     */
    public function baseOf(string $host): string
    {
        $host = $this->normalise($host);

        if ($host === '') {
            return '';
        }

        $pinned = config('tenancy.base_domain');
        $pinned = is_string($pinned) && trim($pinned) !== '' ? strtolower(trim($pinned)) : null;

        if ($pinned !== null && $host === $pinned) {
            return $host;
        }

        $labels = explode('.', $host);
        $first = $labels[0];
        $base = implode('.', array_slice($labels, 1));

        return $this->isApex($host, $base) ? $host : $base;
    }

    /**
     * Apex check: a host whose base is too short to be a domain in its own
     * right (default tenancy.min_base_labels = 2, so "com" fails) cannot be
     * a tenant subdomain — it IS the bare base domain. *.localhost gets a
     * floor of 1 label so acme.localhost still resolves locally.
     */
    private function isApex(string $host, string $base): bool
    {
        if ($base === '') {
            return true;
        }

        $min = max(1, (int) config('tenancy.min_base_labels', 2));

        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            $min = 1;
        }

        return substr_count($base, '.') + 1 < $min;
    }

    private function normalise(string $host): string
    {
        $host = strtolower(trim($host));
        $host = rtrim($host, '.');
        $host = trim($host, '[]');

        if ($host !== '' && filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $host;
        }

        $host = (string) preg_replace('/:\d+$/', '', $host);

        if ($host === '') {
            return '';
        }

        if (function_exists('idn_to_ascii')) {
            $punycode = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($punycode !== false) {
                $host = strtolower($punycode);
            }
        }

        return $host;
    }

    private function isIpLiteral(string $host): bool
    {
        return filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) !== false;
    }
}

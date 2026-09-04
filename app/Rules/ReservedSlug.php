<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A slug that would shadow another identity can never be a tenant slug: the
 * central prefix, and every path static/asset name this app's webserver or
 * Laravel serves. Path-prefix tenancy makes this list load-bearing — a slug
 * equal to one of these would sit on a URL that already means something else.
 */
class ReservedSlug implements ValidationRule
{
    /** @var list<string> */
    private const INFRASTRUCTURE = [
        // Static paths & script names this app serves.
        'uploads', 'css', 'js', 'build', 'storage', 'vendor', 'favicon.ico',
        'robots.txt', 'up',
        // Auth/panel paths.
        'login', 'logout', 'admin', 'central',
        // Marketplace-ish infrastructure hints (kept from the reference).
        'www', 'api', 'sanctum', 'broadcasting', 'app', 'mail', 'smtp',
        'ftp', 'cdn', 'static', 'assets', 'status', 'help', 'support',
        'billing', 'dashboard',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = strtolower((string) $value);

        $reserved = array_merge(
            self::INFRASTRUCTURE,
            [strtolower((string) config('tenancy.central_prefix'))],
        );

        if (in_array($slug, $reserved, true)) {
            $fail('That slug is reserved.');
        }
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use App\Services\CurrentContext;

/**
 * Asset URL normalisation for media stored outside the document root.
 */
class Media
{
    public static function url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        // Legacy rows live under public/uploads (moved there by the old
        // controller) — keep working untouched.
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        try {
            // Use tenant-aware storage URL when in tenant context
            $context = app(CurrentContext::class);
            $tenant = $context->tenant();

            if ($tenant && !$context->isCentral() && !empty($tenant->slug)) {
                return url("/{$tenant->slug}/storage/{$path}");
            }

            // Fallback for central context or when tenant is not available
            $centralPrefix = config('tenancy.central_prefix', 'admin');
            return url("/{$centralPrefix}/storage/{$path}");
        } catch (\Exception $e) {
            // If context is not available, fall back to basic storage URL
            return url('/storage/' . $path);
        }
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

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

        // Use Laravel route to serve files directly (bypasses Windows symlink issues)
        return url('/storage/' . $path);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StorageService
{
    /**
     * Upload an image to Cloudflare R2
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $path  Storage path (e.g., 'products', 'vehicles')
     * @return string  Public URL of the uploaded file
     */
    public function uploadImage($file, $path = 'uploads')
    {
        if (!$file->isValid()) {
            throw new RuntimeException('File upload failed.');
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath = $path . '/' . $filename;

        // Upload to R2
        $uploaded = Storage::disk('r2')->put($fullPath, file_get_contents($file), 'public');

        if (!$uploaded) {
            throw new RuntimeException('Failed to upload file to storage.');
        }

        // Return public URL
        return Storage::disk('r2')->url($fullPath);
    }

    /**
     * Delete an image from Cloudflare R2
     *
     * @param  string  $path  Full path of the file
     * @return bool
     */
    public function deleteImage($path)
    {
        // Convert URL to path if needed
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path, PHP_URL_PATH);
            // Remove leading slash if present
            $path = ltrim($path, '/');
        }

        return Storage::disk('r2')->delete($path);
    }

    /**
     * Check if R2 storage is configured
     *
     * @return bool
     */
    public function isConfigured()
    {
        return !empty(config('filesystems.disks.r2.key')) 
            && !empty(config('filesystems.disks.r2.secret'))
            && !empty(config('filesystems.disks.r2.bucket'));
    }

    /**
     * Get the R2 disk or throw exception if not configured
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     * @throws RuntimeException
     */
    public function getDiskOrFail()
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException(
                'Cloudflare R2 storage is not configured. Please set CLOUDFLARE_R2_* values in .env'
            );
        }

        return Storage::disk('r2');
    }
}
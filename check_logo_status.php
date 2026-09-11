<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

// Check the current logo status for tenant 2
$setting = Setting::withoutTenantScope()
    ->where('tenant_id', 2)
    ->where('key', 'branding.logo_path')
    ->first();

if ($setting) {
    echo "Current logo path in database: '{$setting->value}'\n";
    echo "Is empty: " . (empty($setting->value) ? 'YES' : 'NO') . "\n";
    
    if (!empty($setting->value)) {
        echo "File exists: " . (Storage::disk('public')->exists($setting->value) ? 'YES' : 'NO') . "\n";
        echo "File size: " . Storage::disk('public')->size($setting->value) . " bytes\n";
        echo "Public URL: http://localhost/storage/" . $setting->value . "\n";
    }
} else {
    echo "No logo setting found for tenant 2\n";
}

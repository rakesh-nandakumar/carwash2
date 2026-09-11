<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking branding.logo_path for tenant 2:\n";
$setting = \App\Models\Setting::withoutTenantScope()->where('tenant_id', 2)->where('key', 'branding.logo_path')->first();
if ($setting) {
    echo "Found: " . $setting->value . "\n";
    echo "Length: " . strlen($setting->value) . "\n";
    echo "Hex dump: " . bin2hex($setting->value) . "\n";
} else {
    echo "Not found in database\n";
}

echo "\nChecking if exact file exists:\n";
$path = 'tenants/2/branding/oZBSwt7xBrR98a4dqhCCssINFKUeUaGBLYmflEY7.jpg';
$fullPath = storage_path('app/public/' . $path);
echo "Looking for: " . $fullPath . "\n";
echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

echo "\nChecking all files in branding directory:\n";
$storagePath = storage_path('app/public/tenants/2/branding/');
if (is_dir($storagePath)) {
    $files = scandir($storagePath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            if (strpos($file, 'oZBSwt7xBrR98a4dqhCCssINFKUeUaGBLYm') !== false) {
                echo "MATCH: " . $file . "\n";
            }
        }
    }
}

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Business;
use App\Models\Setting;

$business = Business::first();

if (!$business) {
    echo "No business found.\n";
    exit;
}

echo "Checking current settings for Business ID: {$business->id}\n";
echo "==============================================\n\n";

$settings = $business->getBillingSettings();

echo "Current settings:\n";
print_r($settings);

echo "\n==============================================\n";
echo "Checking for missing background fields...\n\n";

$needsUpdate = false;
$missingFields = [];

$requiredFields = [
    'background_type' => 'image',
    'background_color' => '',
    'custom_background_color' => ''
];

foreach ($requiredFields as $field => $default) {
    if (!isset($settings[$field])) {
        echo "Missing field: {$field}\n";
        $missingFields[$field] = $default;
        $needsUpdate = true;
    } else {
        echo "Field exists: {$field} = " . var_export($settings[$field], true) . "\n";
    }
}

if ($needsUpdate) {
    echo "\nUpdating settings with missing fields...\n";
    $settings = array_merge($settings, $missingFields);
    $business->saveBillingSettings($settings);
    echo "Settings updated successfully!\n";
    
    echo "\nUpdated settings:\n";
    print_r($business->getBillingSettings());
} else {
    echo "\nAll required fields are present.\n";
}

echo "\n==============================================\n";
echo "Done.\n";

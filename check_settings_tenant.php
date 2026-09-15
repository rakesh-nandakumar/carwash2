<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrentContext;

$context = app(CurrentContext::class);
$context->runWithoutTenant(function() {
    $settings = \App\Models\Setting::whereIn('key', ['draft', 'confirmed', 'deleted'])->get();

    echo "Settings (without tenant context):\n";
    foreach ($settings as $setting) {
        echo "  {$setting->key}: ID={$setting->id}, tenant_id=" . ($setting->tenant_id ?? 'NULL') . ", value={$setting->value}\n";
    }
});

echo "\n";

$context->runForTenant(1, function() {
    $settings = \App\Models\Setting::whereIn('key', ['draft', 'confirmed', 'deleted'])->get();

    echo "Settings (with tenant context): \n";
    foreach ($settings as $setting) {
        echo "  {$setting->key}: ID={$setting->id}, tenant_id=" . ($setting->tenant_id ?? 'NULL') . ", value={$setting->value}\n";
    }
});

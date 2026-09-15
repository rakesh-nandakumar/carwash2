<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrentContext;

$context = app(CurrentContext::class);
$context->runWithoutTenant(function() {
    $deleted = \App\Models\Setting::where('key', 'deleted')->first();

    echo "Before fix:\n";
    echo "  ID: {$deleted->id}\n";
    echo "  tenant_id: " . ($deleted->tenant_id ?? 'NULL') . "\n";
    echo "  value: {$deleted->value}\n\n";

    // Fix tenant_id
    $deleted->tenant_id = 1;
    $deleted->save();

    echo "After fix:\n";
    echo "  ID: {$deleted->id}\n";
    echo "  tenant_id: {$deleted->tenant_id}\n";
    echo "  value: {$deleted->value}\n";
});

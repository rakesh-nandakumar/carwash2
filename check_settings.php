<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrentContext;

$context = app(CurrentContext::class);
$context->runWithoutTenant(function() {
    $draft = \App\Models\Setting::where('key', 'draft')->first();
    $confirmed = \App\Models\Setting::where('key', 'confirmed')->first();
    $deleted = \App\Models\Setting::where('key', 'deleted')->first();

    echo "Draft: ID={$draft->id}, Value={$draft->value}\n";
    echo "Confirmed: ID={$confirmed->id}, Value={$confirmed->value}\n";
    echo "Deleted: ID={$deleted->id}, Value={$deleted->value}\n";
});

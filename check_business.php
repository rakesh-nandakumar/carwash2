<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking business data...\n";

$context = app(\App\Services\CurrentContext::class);
$context->runWithoutTenant(function() {
    $businesses = \App\Models\Business::all();
    echo "Total Businesses: " . $businesses->count() . "\n";

    foreach ($businesses as $business) {
        echo "ID: {$business->id}, Name: {$business->name}, Code: {$business->code}\n";
    }
});

$tenant = \App\Models\Tenant::demo();
$context = app(\App\Services\CurrentContext::class);

$context->runForTenant($tenant->id, function() {
    $users = \App\Models\User::all();
    echo "\nUsers in tenant:\n";
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Business ID: {$user->business_id}\n";
    }
});

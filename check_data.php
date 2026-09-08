<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking data counts...\n";

$tenant = \App\Models\Tenant::demo();
$context = app(\App\Services\CurrentContext::class);

$context->runForTenant($tenant->id, function() {
    echo 'Customers: ' . \App\Models\Customer::count() . "\n";
    echo 'ServiceCategories: ' . \App\Models\ServiceCategory::count() . "\n";
    echo 'Categories: ' . \App\Models\Category::count() . "\n";
    echo 'Users: ' . \App\Models\User::count() . "\n";
    echo 'Business: ' . \App\Models\Business::count() . "\n";
});

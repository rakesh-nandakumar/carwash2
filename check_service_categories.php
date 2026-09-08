<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking service_categories data...\n";

$tenant = \App\Models\Tenant::demo();
$context = app(\App\Services\CurrentContext::class);

$context->runForTenant($tenant->id, function() {
    $categories = \App\Models\ServiceCategory::all();
    echo "Total ServiceCategories: " . $categories->count() . "\n";
    
    foreach ($categories as $cat) {
        echo "ID: {$cat->id}, Name: {$cat->name}, Business ID: {$cat->business_id}\n";
    }
    
    echo "\nCurrent user business_id: " . (auth()->user() ? auth()->user()->business_id : 'not logged in') . "\n";
});

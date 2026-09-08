<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking and fixing service categories business_id...\n";

$context = app(\App\Services\CurrentContext::class);
$context->runWithoutTenant(function() {
    $categories = \App\Models\ServiceCategory::all();
    echo "Total service categories: " . $categories->count() . "\n";
    
    foreach ($categories as $category) {
        echo "ID: {$category->id}, Name: {$category->name}, Business ID: " . ($category->business_id ?? 'NULL') . "\n";
        
        // Fix categories without business_id by setting them to business_id 1
        if (!$category->business_id) {
            $category->business_id = 1;
            $category->save();
            echo "  - Fixed: Set business_id to 1\n";
        }
    }
    
    echo "\nAfter fix:\n";
    $categories = \App\Models\ServiceCategory::all();
    foreach ($categories as $category) {
        echo "ID: {$category->id}, Name: {$category->name}, Business ID: " . ($category->business_id ?? 'NULL') . "\n";
    }
});

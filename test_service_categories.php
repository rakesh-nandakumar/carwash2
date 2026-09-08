<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing service categories list endpoint...\n";

$context = app(\App\Services\CurrentContext::class);
$context->runWithoutTenant(function() {
    // Simulate authentication as admin user
    $admin = \App\Models\User::where('email', 'admin@autocare.local')->first();
    if ($admin) {
        auth()->login($admin);
        
        echo "Logged in as: {$admin->name} (Business ID: {$admin->business_id})\n";
        
        // Simulate the controller logic
        $categories = \App\Models\ServiceCategory::select('id', 'name')
            ->where('business_id', auth()->user()->business_id)
            ->orderBy('name')
            ->get();
        
        echo "ServiceCategories found: " . $categories->count() . "\n";
        
        foreach ($categories as $category) {
            echo "  - ID: {$category->id}, Name: {$category->name}, Business ID: {$category->business_id}\n";
        }
        
        echo "\nJSON that would be returned:\n";
        echo json_encode($categories, JSON_PRETTY_PRINT);
    }
});

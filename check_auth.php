<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking authentication and business context...\n";

// Simulate a login as admin user
$context = app(\App\Services\CurrentContext::class);
$context->runWithoutTenant(function() {
    $admin = \App\Models\User::where('email', 'admin@autocare.local')->first();
    if ($admin) {
        echo "Found admin user: {$admin->name} (ID: {$admin->id}, Business ID: {$admin->business_id})\n";
        
        // Simulate authentication
        auth()->login($admin);
        
        echo "After login - Auth user business_id: " . auth()->user()->business_id . "\n";
        echo "After login - Auth user tenant_id: " . auth()->user()->tenant_id . "\n";
        
        // Now try to get data as this user
        $tenant = \App\Models\Tenant::find($admin->tenant_id);
        if ($tenant) {
            $context = app(\App\Services\CurrentContext::class);
            $context->runForTenant($tenant->id, function() use ($admin) {
                echo "\nWith tenant context:\n";
                echo "Auth user business_id: " . auth()->user()->business_id . "\n";
                
                $categories = \App\Models\ServiceCategory::where('business_id', auth()->user()->business_id)->get();
                echo "ServiceCategories for business_id " . auth()->user()->business_id . ": " . $categories->count() . "\n";
                
                $customers = \App\Models\Customer::where('business_id', auth()->user()->business_id)->get();
                echo "Customers for business_id " . auth()->user()->business_id . ": " . $customers->count() . "\n";
            });
        }
    } else {
        echo "Admin user not found\n";
    }
});

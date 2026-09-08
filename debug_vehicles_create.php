<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking vehicles/create data passing...\n";

$context = app(\App\Services\CurrentContext::class);
$context->runWithoutTenant(function() {
    // Simulate authentication as admin user
    $admin = \App\Models\User::where('email', 'admin@autocare.local')->first();
    if ($admin) {
        auth()->login($admin);
        
        echo "Logged in as: {$admin->name} (Business ID: {$admin->business_id})\n";
        
        // Simulate the controller logic
        $user = auth()->user();
        $customers = \App\Models\Customer::where('business_id', $user->business_id)
            ->orderBy('full_name')
            ->get();
        
        echo "Customers found: " . $customers->count() . "\n";
        
        foreach ($customers as $customer) {
            echo "  - ID: {$customer->id}, Name: {$customer->full_name}, Phone: {$customer->phone}, Business ID: {$customer->business_id}\n";
        }
        
        // Check what the JavaScript array would look like
        echo "\nJavaScript array that would be generated:\n";
        echo "[\n";
        foreach ($customers as $c) {
            echo "  { id: {$c->id}, label: \"{$c->full_name} — {$c->phone}\" },\n";
        }
        echo "]\n";
    }
});

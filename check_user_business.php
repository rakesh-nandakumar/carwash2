<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking user business assignment...\n";

$context = app(\App\Services\CurrentContext::class);
$context->runWithoutTenant(function() {
    $businesses = \App\Models\Business::all();
    echo "Businesses:\n";
    foreach ($businesses as $business) {
        echo "ID: {$business->id}, Name: {$business->name}\n";
        
        $users = \App\Models\User::where('business_id', $business->id)->get();
        echo "  Users ({$users->count()}):\n";
        foreach ($users as $user) {
            echo "    - {$user->name} ({$user->email})\n";
        }
        
        $categories = \App\Models\ServiceCategory::where('business_id', $business->id)->get();
        echo "  ServiceCategories ({$categories->count()}):\n";
        foreach ($categories as $cat) {
            echo "    - {$cat->name}\n";
        }
        
        $customers = \App\Models\Customer::where('business_id', $business->id)->get();
        echo "  Customers ({$customers->count()}):\n";
        foreach ($customers as $customer) {
            echo "    - {$customer->full_name} ({$customer->phone})\n";
        }
    }
});

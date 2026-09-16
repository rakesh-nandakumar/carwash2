<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== FULL GRN IMPLEMENTATION CHECK ===\n\n";

// Check goods_receipts table
echo "1. Checking goods_receipts table structure:\n";
$columns = Schema::getColumnListing('goods_receipts');
foreach ($columns as $column => $type) {
    echo "   - $column ($type)\n";
}

echo "\n2. Checking GoodsReceipt model:\n";
$model = new \App\Models\GoodsReceipt();
echo "   - Fillable: " . implode(', ', $model->getFillable()) . "\n";
echo " - Guarded: " . ($model->guarded === [] ? 'none' : 'yes') . "\n";

echo "\n3. Checking routes:\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
foreach ($routes as $route) {
    if (strpos($route->uri, 'grns') !== false) {
        echo "   - " . $route->methods[0] . ' ' . $route->uri . "\n";
    }
}

echo "\n4. Testing database insert:\n";
try {
    $test = new \App\Models\GoodsReceipt();
    $test->grn_number = 'TEST-001';
    $test->supplier_id = 1;
    $test->reference = 'TEST';
    $test->notes = 'TEST';
    $test->received_by = 1;
    $test->received_at = now();
    $test->save();
    echo "   - Test insert SUCCESS (ID: {$test->id})\n";
    $test->delete();
} catch (\Exception $e) {
    echo "   - Test insert FAILED: " . $e->getMessage() . "\n";
}

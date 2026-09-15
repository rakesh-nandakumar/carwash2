<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== CHECKING goods_receipt_items TABLE ===\n\n";

if (!Schema::hasTable('goods_receipt_items')) {
    echo "ERROR: goods_receipts_items table does not exist!\n";
    exit(1);
}

echo "Table exists. Checking columns:\n";
$columns = Schema::getColumnListing('goods_receipt_items');
foreach ($columns as $column) {
    echo "  - $column\n";
}

echo "\nChecking existing GRN items:\n";
$items = \DB::table('goods_receipt_items')->get();
echo "Total items: " . $items->count() . "\n";
foreach ($items as $item) {
    echo "  - ID: {$item->id}, GRN ID: {$item->goods_receipt_id}, Product ID: {$item->product_id}, Qty: {$item->quantity}\n";
}

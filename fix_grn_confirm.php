<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FIXING GRN CONFIRM STATUS ===\n\n";

$confirmedStatus = \DB::table('settings')
    ->where('group', 'grn_statuses')
    ->where('key', 'confirmed')
    ->first();

if (!$confirmedStatus) {
    echo "ERROR: Confirmed status not found!\n";
    exit(1);
}

echo "Confirmed status ID: {$confirmedStatus->id}\n";

// Update the GRN
$updated = \DB::table('goods_receipts')
    ->where('id', 1)
    ->update(['status_id' => $confirmedStatus->id]);

echo "Updated GRN status_id to {$confirmedStatus->id}\n";

// Verify
$grn = \DB::table('goods_receipts')->first();
echo "New status_id: {$grn->status_id}\n";

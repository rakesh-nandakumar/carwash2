<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING CURRENT GRN STATUS ===\n\n";

$grn = \DB::table('goods_receipts')->first();
echo "GRN ID: {$grn->id}\n";
echo "GRN status_id: {$grn->status_id}\n";
echo "GRN confirmed_by: {$grn->confirmed_by}\n";
echo "GRN confirmed_at: {$grn->confirmed_at}\n";

$confirmedStatus = \DB::table('settings')
    ->where('group', 'grn_statuses')
    ->where('key', 'confirmed')
    ->first();

echo "\nConfirmed status ID: {$confirmedStatus->id}\n";

if ($grn->status_id == $confirmedStatus->id) {
    echo "\nGRN is confirmed!\n";
} else {
    echo "\nGRN is NOT confirmed. Current status_id: {$grn->status_id}, Expected: {$confirmedStatus->id}\n";
}

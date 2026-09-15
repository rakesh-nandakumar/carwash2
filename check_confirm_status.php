<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING CONFIRMED STATUS ===\n\n";

$confirmedStatus = \DB::table('settings')
    ->where('group', 'grn_statuses')
    ->where('key', 'confirmed')
    ->first();

if ($confirmedStatus) {
    echo "Confirmed status ID: {$confirmedStatus->id}\n";
    echo "Confirmed status tenant_id: {$confirmedStatus->tenant_id}\n";
} else {
    echo "ERROR: Confirmed status not found!\n";
}

$grn = \DB::table('goods_receipts')->first();
echo "\nCurrent GRN status_id: {$grn->status_id}\n";
echo "Current GRN tenant_id: {$grn->tenant_id}\n";

if ($confirmedStatus && $grn->tenant_id != $confirmedStatus->tenant_id) {
    echo "\nMISMATCH! Updating confirmed status tenant...\n";
    \DB::table('settings')->where('id', $confirmedStatus->id)->update(['tenant_id' => $grn->tenant_id]);
    echo "Done.\n";
}

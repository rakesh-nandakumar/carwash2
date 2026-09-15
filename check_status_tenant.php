<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING STATUS TENANT ===\n\n";

$grn = \DB::table('goods_receipts')->first();
echo "GRN status_id: {$grn->status_id}\n";
echo "GRN tenant_id: {$grn->tenant_id}\n\n";

$status = \DB::table('settings')->where('id', 44)->first();
echo "Status ID 44 tenant_id: {$status->tenant_id}\n";
echo "Status key: {$status->key}\n";
echo "Status value: {$status->value}\n";

if ($grn->tenant_id != $status->tenant_id) {
    echo "\nMISMATCH! GRN tenant ({$grn->tenant_id}) != Status tenant ({$status->tenant_id})\n";
    echo "Updating status to match GRN tenant...\n";
    \DB::table('settings')->where('id', 44)->update(['tenant_id' => $grn->tenant_id]);
    echo "Done.\n";
}

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrentContext;

$context = app(CurrentContext::class);

// Get deleted status ID without tenant context
$deletedStatus = null;
$context->runWithoutTenant(function() use (&$deletedStatus) {
    $deletedStatus = \App\Models\Setting::where('key', 'deleted')->first();
});

echo "Deleted Status ID: {$deletedStatus->id}\n\n";

$context->runForTenant(1, function() use ($deletedStatus) {
    $grn = \App\Models\GoodsReceipt::where('grn_number', 'GRN-2026-000001')->first();

    if (!$grn) {
        echo "GRN not found\n";
        return;
    }

    echo "Before delete:\n";
    echo "  Status ID: {$grn->status_id}\n";
    echo "  Status: " . ($grn->status ? $grn->status->value : 'NULL') . "\n";
    echo "  Deleted at: " . ($grn->deleted_at ? $grn->deleted_at : 'NULL') . "\n";
    echo "  Deleted by: " . ($grn->deleted_by ? $grn->deleted_by : 'NULL') . "\n";

    // Try to delete
    try {
        $grn->status_id = $deletedStatus->id;
        $grn->deleted_by = 1;
        $grn->deleted_at = now();
        $grn->save();

        echo "\nAfter delete:\n";
        echo "  Status ID: {$grn->status_id}\n";
        echo "  Status: " . ($grn->status ? $grn->status->value : 'NULL') . "\n";
        echo "  Deleted at: " . ($grn->deleted_at ? $grn->deleted_at : 'NULL') . "\n";
        echo "  Deleted by: " . ($grn->deleted_by ? $grn->deleted_by : 'NULL') . "\n";
    } catch (\Exception $e) {
        echo "\nError: " . $e->getMessage() . "\n";
    }
});

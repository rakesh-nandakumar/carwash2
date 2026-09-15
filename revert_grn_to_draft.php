<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrentContext;

$context = app(CurrentContext::class);

// Get draft status ID without tenant context
$draftStatusId = null;
$context->runWithoutTenant(function() use (&$draftStatusId) {
    $draftStatus = \App\Models\Setting::where('key', 'draft')->first();
    $draftStatusId = $draftStatus->id;
});

echo "Draft Status ID: {$draftStatusId}\n";

// Revert GRN to draft
$context->runForTenant(1, function() use ($draftStatusId) {
    $grn = \App\Models\GoodsReceipt::where('grn_number', 'GRN-2026-000001')->first();

    if ($grn) {
        $grn->status_id = $draftStatusId;
        $grn->deleted_by = null;
        $grn->deleted_at = null;
        $grn->save();

        echo "GRN reverted to draft status\n";
    }
});

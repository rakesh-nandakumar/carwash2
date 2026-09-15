<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrentContext;

$context = app(CurrentContext::class);
$context->runForTenant(1, function() {
    // Get the draft status ID
    $draftStatus = \App\Models\Setting::where('key', 'draft')->first();
    echo "Draft Status ID: {$draftStatus->id}\n\n";

    // Update all GRNs with null status_id to draft
    $updated = \App\Models\GoodsReceipt::whereNull('status_id')->update(['status_id' => $draftStatus->id]);

    echo "Updated {$updated} GRNs to Draft status\n";

    // Verify
    $grns = \App\Models\GoodsReceipt::all();
    echo "\nVerification:\n";
    foreach ($grns as $grn) {
        echo "GRN: {$grn->grn_number} - Status ID: {$grn->status_id}\n";
    }
});

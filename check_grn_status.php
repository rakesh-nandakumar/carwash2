<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrentContext;

$context = app(CurrentContext::class);
$context->runForTenant(1, function() {
    $grns = \App\Models\GoodsReceipt::all();

    echo "GRN Status Check:\n";
    echo "================\n\n";

    foreach ($grns as $grn) {
        echo "GRN: {$grn->grn_number}\n";
        echo "  Status ID: {$grn->status_id}\n";
        echo "  Status: " . ($grn->status ? $grn->status->value : 'NULL') . "\n";
        echo "\n";
    }
});

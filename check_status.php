<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== CHECKING STATUS SETTINGS ===\n\n";

if (!Schema::hasTable('settings')) {
    echo "ERROR: settings table does not exist!\n";
    exit(1);
}

echo "Settings table columns:\n";
$columns = Schema::getColumnListing('settings');
foreach ($columns as $column) {
    echo "  - $column\n";
}

echo "\nGRN Status settings:\n";
$statuses = \DB::table('settings')
    ->where('group', 'grn_statuses')
    ->get();

foreach ($statuses as $status) {
    echo "  - ID: {$status->id}, Key: {$status->key}, Value: {$status->value}\n";
}

if ($statuses->isEmpty()) {
    echo "\nNo GRN statuses found. Creating draft status...\n";
    $draftId = \DB::table('settings')->insertGetId([
        'group' => 'grn_statuses',
        'key' => 'draft',
        'value' => 'Draft',
        'type' => 'string',
        'tenant_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created draft status with ID: $draftId\n";
}

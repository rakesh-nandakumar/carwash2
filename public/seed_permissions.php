<?php

// Temporary script to seed permissions
// Access this file at: http://yourdomain.com/seed_permissions.php
// DELETE THIS FILE AFTER USE!

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h2>Running Permission Seeder...</h2>";
echo "<pre>";

try {
    $seeder = new Database\Seeders\PermissionSeeder();
    $seeder->run();
    echo "<strong style='color: green;'>✓ Permission seeder completed successfully!</strong>";
    echo "<br><br><strong>IMPORTANT:</strong> Delete this file (seed_permissions.php) from your server now!";
} catch (Exception $e) {
    echo "<strong style='color: red;'>✗ Error:</strong> " . $e->getMessage();
}

echo "</pre>";

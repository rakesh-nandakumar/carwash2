<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mark any tenant 3 cleanup migration as already run to prevent it from executing
        $cleanupMigrations = [
            '2026_09_21_000000_fresh_tenant_3_data',
        ];
        
        foreach ($cleanupMigrations as $migration) {
            if (!DB::table('migrations')->where('migration', $migration)->exists()) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => 1,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $cleanupMigrations = [
            '2026_09_21_000000_fresh_tenant_3_data',
        ];
        
        foreach ($cleanupMigrations as $migration) {
            DB::table('migrations')
                ->where('migration', $migration)
                ->delete();
        }
    }
};

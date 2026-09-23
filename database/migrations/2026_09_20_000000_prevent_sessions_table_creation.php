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
        // Check if sessions table already exists and mark the migration as run
        if (Schema::hasTable('sessions')) {
            if (!DB::table('migrations')->where('migration', '2026_09_20_014139_create_sessions_table')->exists()) {
                DB::table('migrations')->insert([
                    'migration' => '2026_09_20_014139_create_sessions_table',
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
        // Remove the migration record
        DB::table('migrations')
            ->where('migration', '2026_09_20_014139_create_sessions_table')
            ->delete();
    }
};

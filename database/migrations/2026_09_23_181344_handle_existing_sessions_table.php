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
        // Check if sessions table already exists
        if (Schema::hasTable('sessions')) {
            // Mark the sessions table migration as already run
            DB::table('migrations')->insert([
                'migration' => '2026_09_20_014139_create_sessions_table',
                'batch' => 1,
            ]);
        } else {
            // Create the sessions table if it doesn't exist
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the migration record if we added it
        DB::table('migrations')
            ->where('migration', '2026_09_20_014139_create_sessions_table')
            ->delete();
    }
};

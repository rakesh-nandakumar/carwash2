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
        // Check if sessions table already exists before creating
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
        
        // Mark the sessions table migration as run
        if (!DB::table('migrations')->where('migration', '2026_09_20_014139_create_sessions_table')->exists()) {
            DB::table('migrations')->insert([
                'migration' => '2026_09_20_014139_create_sessions_table',
                'batch' => 1,
            ]);
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
            
        // Only drop the table if we created it
        Schema::dropIfExists('sessions');
    }
};

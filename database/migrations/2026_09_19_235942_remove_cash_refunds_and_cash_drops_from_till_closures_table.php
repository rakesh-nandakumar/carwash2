<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('till_closures', function (Blueprint $table) {
            $table->dropColumn(['cash_refunds', 'cash_drops']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('till_closures', function (Blueprint $table) {
            $table->decimal('cash_refunds', 14, 2)->default(0)->after('cash_out');
            $table->decimal('cash_drops', 14, 2)->default(0)->after('cash_refunds');
        });
    }
};

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
            $table->decimal('cheque_sales', 14, 2)->default(0)->after('bank_transfer_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('till_closures', function (Blueprint $table) {
            $table->dropColumn('cheque_sales');
        });
    }
};

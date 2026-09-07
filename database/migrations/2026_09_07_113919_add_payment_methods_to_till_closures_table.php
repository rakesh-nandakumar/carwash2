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
            $table->decimal('card_sales', 14, 2)->default(0)->after('cash_sales');
            $table->decimal('mobile_money_sales', 14, 2)->default(0)->after('card_sales');
            $table->decimal('bank_transfer_sales', 14, 2)->default(0)->after('mobile_money_sales');
            $table->decimal('other_payment_sales', 14, 2)->default(0)->after('bank_transfer_sales');
            $table->decimal('total_sales', 14, 2)->default(0)->after('other_payment_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('till_closures', function (Blueprint $table) {
            $table->dropColumn([
                'card_sales',
                'mobile_money_sales',
                'bank_transfer_sales',
                'other_payment_sales',
                'total_sales',
            ]);
        });
    }
};

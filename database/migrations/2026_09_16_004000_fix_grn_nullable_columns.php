<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')->nullable()->change();
            $table->string('receipt_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')->nullable(false)->change();
            $table->string('receipt_number')->nullable(false)->change();
        });
    }
};

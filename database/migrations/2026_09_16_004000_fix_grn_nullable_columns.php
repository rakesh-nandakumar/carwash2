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
            
            // Handle both old receipt_number and new grn_number columns
            if (Schema::hasColumn('goods_receipts', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->change();
            } elseif (Schema::hasColumn('goods_receipts', 'grn_number')) {
                $table->string('grn_number')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')->nullable(false)->change();
            
            // Handle both old receipt_number and new grn_number columns
            if (Schema::hasColumn('goods_receipts', 'receipt_number')) {
                $table->string('receipt_number')->nullable(false)->change();
            } elseif (Schema::hasColumn('goods_receipts', 'grn_number')) {
                $table->string('grn_number')->nullable(false)->change();
            }
        });
    }
};

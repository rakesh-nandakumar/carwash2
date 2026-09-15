<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // Add all missing columns
            if (!Schema::hasColumn('goods_receipts', 'grn_number')) {
                $table->string('grn_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('goods_receipts', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete()->after('purchase_order_id');
            }
            if (!Schema::hasColumn('goods_receipts', 'reference')) {
                $table->string('reference')->nullable()->after('supplier_id');
            }
            if (!Schema::hasColumn('goods_receipts', 'status_id')) {
                $table->foreignId('status_id')->nullable()->constrained('settings')->nullOnDelete()->after('reference');
            }
            if (!Schema::hasColumn('goods_receipts', 'received_by')) {
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete()->after('status_id');
            }
            if (!Schema::hasColumn('goods_receipts', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }
            if (!Schema::hasColumn('goods_receipts', 'confirmed_by')) {
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete()->after('received_at');
            }
            if (!Schema::hasColumn('goods_receipts', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            }
            if (!Schema::hasColumn('goods_receipts', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->after('confirmed_at');
            }
            if (!Schema::hasColumn('goods_receipts', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('deleted_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $columns = ['grn_number', 'supplier_id', 'reference', 'status_id', 'received_by', 'received_at', 'confirmed_by', 'confirmed_at', 'deleted_by', 'deleted_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('goods_receipts', $column)) {
                    if (in_array($column, ['supplier_id', 'status_id', 'received_by', 'confirmed_by', 'deleted_by'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};

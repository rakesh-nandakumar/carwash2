<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // Add GRN number if not exists (receipt_number might exist)
            if (!Schema::hasColumn('goods_receipts', 'grn_number')) {
                if (Schema::hasColumn('goods_receipts', 'receipt_number')) {
                    $table->renameColumn('receipt_number', 'grn_number');
                } else {
                    $table->string('grn_number')->unique()->after('id');
                }
            }
            
            // Add supplier relationship if it doesn't exist
            if (!Schema::hasColumn('goods_receipts', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete()->after('purchase_order_id');
            }
            
            // Add reference field if it doesn't exist
            if (!Schema::hasColumn('goods_receipts', 'reference')) {
                $table->string('reference')->nullable()->after('supplier_id');
            }
            
            // Change status to use lookup table instead of simple string
            if (!Schema::hasColumn('goods_receipts', 'status_id')) {
                $table->foreignId('status_id')->nullable()->constrained('settings')->nullOnDelete()->after('reference');
            }
            
            // Add confirmation fields if they don't exist
            if (!Schema::hasColumn('goods_receipts', 'confirmed_by')) {
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete()->after('status_id');
            }
            if (!Schema::hasColumn('goods_receipts', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            }
            
            // Add deletion fields for soft delete workflow if they don't exist
            if (!Schema::hasColumn('goods_receipts', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->after('confirmed_at');
            }
            if (!Schema::hasColumn('goods_receipts', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('deleted_by');
            }
            
            // Drop the old status column if it exists
            if (Schema::hasColumn('goods_receipts', 'status')) {
                $table->dropColumn('status');
            }
            
            // Drop branch_id column if it exists
            if (Schema::hasColumn('goods_receipts', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('goods_receipts', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
            }
            if (Schema::hasColumn('goods_receipts', 'status_id')) {
                $table->dropForeign(['status_id']);
            }
            if (Schema::hasColumn('goods_receipts', 'confirmed_by')) {
                $table->dropForeign(['confirmed_by']);
            }
            if (Schema::hasColumn('goods_receipts', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
            }
            
            // If grn_number exists, rename it back to receipt_number
            if (Schema::hasColumn('goods_receipts', 'grn_number')) {
                $table->renameColumn('grn_number', 'receipt_number');
            }
            
            $columnsToDrop = [];
            if (Schema::hasColumn('goods_receipts', 'supplier_id')) $columnsToDrop[] = 'supplier_id';
            if (Schema::hasColumn('goods_receipts', 'reference')) $columnsToDrop[] = 'reference';
            if (Schema::hasColumn('goods_receipts', 'status_id')) $columnsToDrop[] = 'status_id';
            if (Schema::hasColumn('goods_receipts', 'confirmed_by')) $columnsToDrop[] = 'confirmed_by';
            if (Schema::hasColumn('goods_receipts', 'confirmed_at')) $columnsToDrop[] = 'confirmed_at';
            if (Schema::hasColumn('goods_receipts', 'deleted_by')) $columnsToDrop[] = 'deleted_by';
            if (Schema::hasColumn('goods_receipts', 'deleted_at')) $columnsToDrop[] = 'deleted_at';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            if (!Schema::hasColumn('goods_receipts', 'status')) {
                $table->string('status')->default('received')->after('purchase_order_id');
            }
            
            // Add back branch_id column
            if (!Schema::hasColumn('goods_receipts', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('purchase_order_id');
            }
        });
    }
};

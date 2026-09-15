<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Add UUID if it doesn't exist
            if (!Schema::hasColumn('suppliers', 'uuid')) {
                $table->ulid('uuid')->unique()->after('id');
            }
            
            // Add business details if they don't exist
            if (!Schema::hasColumn('suppliers', 'business_name')) {
                $table->string('business_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('company');
            }
            if (!Schema::hasColumn('suppliers', 'tax_number')) {
                $table->string('tax_number')->nullable()->after('registration_number');
            }
            if (!Schema::hasColumn('suppliers', 'website')) {
                $table->string('website')->nullable()->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'notes')) {
                $table->text('notes')->nullable()->after('credit_limit');
            }
            
            // Add financial fields if they don't exist
            if (!Schema::hasColumn('suppliers', 'payment_terms_id')) {
                $table->foreignId('payment_terms_id')->nullable()->constrained('settings')->nullOnDelete()->after('payment_terms');
            }
            if (!Schema::hasColumn('suppliers', 'outstanding_balance')) {
                $table->decimal('outstanding_balance', 14, 2)->default(0)->after('credit_limit');
            }
            
            // Add status management if they don't exist
            if (!Schema::hasColumn('suppliers', 'status_id')) {
                $table->foreignId('status_id')->nullable()->constrained('settings')->nullOnDelete()->after('outstanding_balance');
            }
            if (!Schema::hasColumn('suppliers', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')->default(false)->after('status_id');
            }
            if (!Schema::hasColumn('suppliers', 'blacklisted_reason')) {
                $table->text('blacklisted_reason')->nullable()->after('is_blacklisted');
            }
            if (!Schema::hasColumn('suppliers', 'blacklisted_at')) {
                $table->timestamp('blacklisted_at')->nullable()->after('blacklisted_reason');
            }
            if (!Schema::hasColumn('suppliers', 'blacklisted_by')) {
                $table->foreignId('blacklisted_by')->nullable()->constrained('users')->nullOnDelete()->after('blacklisted_at');
            }
            
            // Add audit fields if they don't exist
            if (!Schema::hasColumn('suppliers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('blacklisted_by');
            }
            if (!Schema::hasColumn('suppliers', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }
            if (!Schema::hasColumn('suppliers', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_by');
            }
            
            // Add soft deletes if it doesn't exist
            if (!Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Drop the old active field if it exists
            if (Schema::hasColumn('suppliers', 'active')) {
                $table->dropColumn('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('suppliers', 'payment_terms_id')) {
                $table->dropForeign(['payment_terms_id']);
            }
            if (Schema::hasColumn('suppliers', 'status_id')) {
                $table->dropForeign(['status_id']);
            }
            if (Schema::hasColumn('suppliers', 'blacklisted_by')) {
                $table->dropForeign(['blacklisted_by']);
            }
            if (Schema::hasColumn('suppliers', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
            if (Schema::hasColumn('suppliers', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('suppliers', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
            }
            
            if (Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            
            $columnsToDrop = [];
            if (Schema::hasColumn('suppliers', 'uuid')) $columnsToDrop[] = 'uuid';
            if (Schema::hasColumn('suppliers', 'business_name')) $columnsToDrop[] = 'business_name';
            if (Schema::hasColumn('suppliers', 'registration_number')) $columnsToDrop[] = 'registration_number';
            if (Schema::hasColumn('suppliers', 'tax_number')) $columnsToDrop[] = 'tax_number';
            if (Schema::hasColumn('suppliers', 'website')) $columnsToDrop[] = 'website';
            if (Schema::hasColumn('suppliers', 'notes')) $columnsToDrop[] = 'notes';
            if (Schema::hasColumn('suppliers', 'payment_terms_id')) $columnsToDrop[] = 'payment_terms_id';
            if (Schema::hasColumn('suppliers', 'outstanding_balance')) $columnsToDrop[] = 'outstanding_balance';
            if (Schema::hasColumn('suppliers', 'status_id')) $columnsToDrop[] = 'status_id';
            if (Schema::hasColumn('suppliers', 'is_blacklisted')) $columnsToDrop[] = 'is_blacklisted';
            if (Schema::hasColumn('suppliers', 'blacklisted_reason')) $columnsToDrop[] = 'blacklisted_reason';
            if (Schema::hasColumn('suppliers', 'blacklisted_at')) $columnsToDrop[] = 'blacklisted_at';
            if (Schema::hasColumn('suppliers', 'blacklisted_by')) $columnsToDrop[] = 'blacklisted_by';
            if (Schema::hasColumn('suppliers', 'created_by')) $columnsToDrop[] = 'created_by';
            if (Schema::hasColumn('suppliers', 'updated_by')) $columnsToDrop[] = 'updated_by';
            if (Schema::hasColumn('suppliers', 'deleted_by')) $columnsToDrop[] = 'deleted_by';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            if (!Schema::hasColumn('suppliers', 'active')) {
                $table->boolean('active')->default(true)->after('payment_terms');
            }
        });
    }
};

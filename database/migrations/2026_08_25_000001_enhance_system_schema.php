<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Permissions & Roles
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('module');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        // Job Status History
        Schema::create('job_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['job_id', 'created_at']);
        });

        // Invoice Versions
        Schema::create('invoice_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->integer('version');
            $table->json('old_data');
            $table->json('new_data');
            $table->decimal('old_total', 14, 2);
            $table->decimal('new_total', 14, 2);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['invoice_id', 'version']);
        });

        // Service Approvals
        Schema::create('service_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_service_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, rejected, partially_approved
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->timestamps();
        });

        // Additional Work Requests
        Schema::create('additional_work_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->decimal('estimated_cost', 12, 2);
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // Stock Movement Enhancements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('reference_id');
            $table->decimal('unit_cost', 12, 2)->nullable()->change();
            $table->text('notes')->nullable()->after('reason');
        });

        // Customer Supplied Parts
        Schema::create('customer_supplied_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('part_number')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->string('condition')->default('new');
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->timestamps();
        });

        // Emergency Purchases
        Schema::create('emergency_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('supplier_name');
            $table->string('shop_name')->nullable();
            $table->string('contact')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost', 12, 2);
            $table->string('invoice_number')->nullable();
            $table->foreignId('purchased_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->decimal('markup', 12, 2)->default(0);
            $table->timestamps();
        });

        // Stock Transfers
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, in_transit, received, cancelled
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->timestamps();
        });

        // Communication Center
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel'); // whatsapp, sms, email
            $table->string('direction'); // inbound, outbound
            $table->string('recipient')->nullable();
            $table->string('sender')->nullable();
            $table->text('message');
            $table->string('status')->default('queued'); // queued, sent, delivered, failed
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
            $table->index(['job_id', 'created_at']);
        });

        // Communication Templates
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event'); // vehicle_received, inspection_completed, etc.
            $table->string('channel');
            $table->text('template');
            $table->boolean('active')->default(true);
            $table->boolean('auto_send')->default(false);
            $table->timestamps();
        });

        // Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['group', 'key']);
        });

        // Vehicle Categories (enhanced)
        Schema::create('vehicle_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Service Pricing by Vehicle Category
        Schema::create('service_vehicle_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 12, 2);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['service_id', 'vehicle_category_id', 'branch_id'], 'svc_unique');
        });

        // Discounts
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('type'); // percentage, fixed
            $table->decimal('value', 12, 2);
            $table->string('applicable_to'); // services, products, all, specific
            $table->json('applicable_ids')->nullable();
            $table->decimal('minimum_amount', 12, 2)->nullable();
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
        });

        // Loyalty Transactions
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // earned, redeemed, expired, adjusted
            $table->decimal('points', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('reference_type')->nullable(); // invoice, manual, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['loyalty_account_id', 'created_at']);
        });

        // Refunds
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('reason');
            $table->string('method'); // cash, card, bank_transfer
            $table->string('reference')->nullable();
            $table->string('status')->default('pending'); // pending, approved, processed, cancelled
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Credit Notes
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number')->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->text('reason');
            $table->string('status')->default('issued'); // issued, applied, expired
            $table->date('valid_until')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Work Time Tracking
        Schema::create('work_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['job_id', 'technician_id']);
        });

        // Service Bay Assignments
        Schema::create('service_bay_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_bay_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Technician Skills
        Schema::create('technician_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('skill_name');
            $table->string('proficiency'); // beginner, intermediate, advanced, expert
            $table->timestamps();
            $table->unique(['technician_id', 'skill_name']);
        });

        // Purchase Orders (enhance existing)
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('subtotal', 14, 2)->default(0)->after('status');
            $table->decimal('tax', 14, 2)->default(0)->after('subtotal');
            $table->date('expected_date')->nullable()->after('total');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete()->after('expected_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('notes')->nullable()->after('approved_at');
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->decimal('received_quantity', 12, 3)->default(0);
            $table->timestamps();
        });

        // Goods Receipts
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('received'); // received, partial, returned
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('received_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Supplier Returns
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, shipped, received, cancelled
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // Expenses (enhanced)
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('expense_number')->nullable()->unique()->first();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('amount');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('status')->default('pending')->after('approved_at');
            $table->string('receipt')->nullable()->after('description');
            $table->string('payment_method')->nullable()->after('receipt');
        });

        // Warranties
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('warranty_type'); // part, service, labor
            $table->text('description');
            $table->integer('warranty_period_months');
            $table->date('start_date');
            $table->date('expiry_date');
            $table->string('terms')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Warranty Claims
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('open'); // open, investigating, approved, rejected, completed
            $table->text('issue_description');
            $table->text('resolution')->nullable();
            $table->decimal('cost', 14, 2)->default(0);
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('open'); // open, investigating, resolved, closed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->decimal('compensation', 14, 2)->default(0);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Notifications (internal)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->json('data')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read', 'created_at']);
        });

        // Cash Registers / Shifts
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shift_number')->unique();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('closing_balance', 14, 2)->nullable();
            $table->decimal('expected_balance', 14, 2)->nullable();
            $table->decimal('variance', 14, 2)->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamps();
        });

        // Cash Register Transactions
        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // sale, refund, expense, payout, drop
            $table->decimal('amount', 14, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Equipment
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('serial_number')->nullable()->unique();
            $table->string('type');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 14, 2)->nullable();
            $table->string('status')->default('active'); // active, maintenance, broken, retired
            $table->date('warranty_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Equipment Maintenance
        Schema::create('equipment_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // scheduled, repair
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->text('description');
            $table->decimal('cost', 14, 2)->nullable();
            $table->string('performed_by')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'equipment_maintenance', 'equipment', 'cash_register_transactions', 'cash_registers',
            'notifications', 'complaints', 'warranty_claims', 'warranties',
            'supplier_returns', 'goods_receipts', 'purchase_order_items', 'purchase_orders',
            'technician_skills', 'service_bay_assignments', 'work_time_logs',
            'credit_notes', 'refunds', 'loyalty_transactions', 'discounts',
            'service_vehicle_pricing', 'vehicle_categories', 'settings',
            'communication_templates', 'communications', 'stock_transfer_items', 'stock_transfers',
            'emergency_purchases', 'customer_supplied_parts', 'service_approvals', 'additional_work_requests',
            'invoice_versions', 'job_status_history', 'role_user', 'permission_role', 'permissions', 'roles'
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};

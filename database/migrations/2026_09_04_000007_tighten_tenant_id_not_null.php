<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Runs only after Phase 4 verification passes (zero NULL tenant_id outside
 * audit_logs): NOT NULL everywhere except audit_logs (central/system records
 * stay unattributable). Split into its own migration so a failed backfill is
 * recoverable without rolling back a live database.
 */
return new class extends Migration
{
    /**
     * Tables whose tenant_id must become NOT NULL. audit_logs is excluded —
     * central/system records with no tenant must remain invisible to tenants.
     *
     * @var list<string>
     */
    private const TABLES = [
        'businesses', 'branches', 'users', 'customers', 'service_categories',
        'services', 'service_packages', 'appointments', 'jobs', 'products',
        'suppliers', 'purchase_orders', 'invoices', 'vehicle_categories',
        'discounts', 'categories', 'service_bays', 'expenses', 'inventory',
        'inventory_movements', 'service_prices', 'service_vehicle_pricing',
        'cash_registers', 'equipment', 'goods_receipts', 'supplier_returns',
        'vehicles', 'loyalty_accounts', 'loyalty_transactions', 'memberships',
        'communications', 'notifications_log', 'inspections', 'inspection_photos',
        'damage_records', 'job_services', 'job_parts', 'job_status_history',
        'service_approvals', 'additional_work_requests', 'customer_supplied_parts',
        'emergency_purchases', 'quality_checks', 'work_time_logs',
        'service_bay_assignments', 'warranties', 'complaints', 'invoice_items',
        'invoice_versions', 'payments', 'refunds', 'credit_notes',
        'warranty_claims', 'package_items', 'purchase_order_items',
        'stock_transfer_items', 'cash_register_transactions',
        'equipment_maintenance', 'technician_skills', 'notifications',
        'roles', 'communication_templates', 'stock_transfers',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally not reversible — only moves one way (see up()).
    }
};

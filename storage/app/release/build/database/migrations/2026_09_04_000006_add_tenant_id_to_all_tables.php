<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extends tenant row-level isolation (App\Models\Concerns\BelongsToTenant)
 * from the core tables to every business-domain table. Until now most tables
 * carried no tenant column and no scope, so any tenant user could see every
 * tenant's data.
 *
 * Backfill strategy, three waves, each a correlated-subquery UPDATE (portable
 * to SQLite for a future test harness — UPDATE ... JOIN is MySQL-only):
 *   1. DIRECT_TABLES — rows already carry business_id; inherit from
 *      businesses (business 1's rows, already seeded in 000005).
 *   2. BRANCH_TABLES — rows carry only branch_id; inherit through branches.
 *   3. PARENT_BACKFILLS — rows inherit from a specific parent row, written in
 *      dependency order.
 * FALLBACK_TABLES (roles, communication_templates, stock_transfers) have no
 * attributable parent and take the sole tenant.
 *
 * audit_logs is the deliberate exception: it stays NULL-able and never
 * falls back, so central/system records with no tenant remain invisible to
 * tenants.
 *
 * Column pattern is cascadeOnDelete (NOT the reference's nullOnDelete): an
 * orphaned row here would be invisible to every tenant AND to TenantScope's
 * central bypass — undeletable garbage.
 */
return new class extends Migration
{
    /**
     * Tables already carrying business_id (businesses itself was backfilled
     * by seed_tenants_from_businesses, settings gets its own rework migration).
     *
     * @var list<string>
     */
    private const DIRECT_TABLES = [
        'branches', 'users', 'customers', 'service_categories', 'services',
        'service_packages', 'appointments', 'jobs', 'products', 'suppliers',
        'purchase_orders', 'invoices', 'vehicle_categories', 'discounts', 'categories',
    ];

    /**
     * Tables carrying only branch_id — inherit through branches.
     *
     * @var list<string>
     */
    private const BRANCH_TABLES = [
        'service_bays', 'expenses', 'inventory', 'inventory_movements',
        'service_prices', 'service_vehicle_pricing', 'cash_registers',
        'equipment', 'goods_receipts', 'supplier_returns',
    ];

    /**
     * Tables whose rows inherit their tenant from a parent row via a join —
     * {table} => [parent_table => fk_column], in dependency order (parents
     * backfilled before children).
     *
     * @var array<string, array<string, string>>
     */
    private const PARENT_BACKFILLS = [
        'vehicles' => ['customers' => 'customer_id'],
        'loyalty_accounts' => ['customers' => 'customer_id'],
        'loyalty_transactions' => ['loyalty_accounts' => 'loyalty_account_id'],
        'memberships' => ['customers' => 'customer_id'],
        'communications' => ['customers' => 'customer_id'],
        'notifications_log' => ['customers' => 'customer_id'],
        'inspections' => ['jobs' => 'job_id'],
        'inspection_photos' => ['jobs' => 'job_id'],
        'damage_records' => ['jobs' => 'job_id'],
        'job_services' => ['jobs' => 'job_id'],
        'job_parts' => ['jobs' => 'job_id'],
        'job_status_history' => ['jobs' => 'job_id'],
        'service_approvals' => ['job_services' => 'job_service_id'],
        'additional_work_requests' => ['jobs' => 'job_id'],
        'customer_supplied_parts' => ['jobs' => 'job_id'],
        'emergency_purchases' => ['jobs' => 'job_id'],
        'quality_checks' => ['jobs' => 'job_id'],
        'work_time_logs' => ['jobs' => 'job_id'],
        'service_bay_assignments' => ['jobs' => 'job_id'],
        'warranties' => ['jobs' => 'job_id'],
        'complaints' => ['customers' => 'customer_id'],
        'invoice_items' => ['invoices' => 'invoice_id'],
        'invoice_versions' => ['invoices' => 'invoice_id'],
        'payments' => ['invoices' => 'invoice_id'],
        'refunds' => ['invoices' => 'invoice_id'],
        'credit_notes' => ['invoices' => 'invoice_id'],
        'warranty_claims' => ['warranties' => 'warranty_id'],
        'package_items' => ['service_packages' => 'service_package_id'],
        'purchase_order_items' => ['purchase_orders' => 'purchase_order_id'],
        'stock_transfer_items' => ['stock_transfers' => 'stock_transfer_id'],
        'cash_register_transactions' => ['cash_registers' => 'cash_register_id'],
        'equipment_maintenance' => ['equipment' => 'equipment_id'],
        'technician_skills' => ['users' => 'technician_id'],
        'notifications' => ['users' => 'user_id'],
        'audit_logs' => ['users' => 'user_id'], // never participates in fallback
    ];

    /**
     * Tables with no tenant-attributable parent — they get the sole-tenant
     * fallback directly. roles is seeded per tenant from provisioning going
     * forward; communication_templates and stock_transfers are legacy-global.
     *
     * @var list<string>
     */
    private const FALLBACK_TABLES = [
        'roles', 'communication_templates', 'stock_transfers',
    ];

    /**
     * Single-column unique keys that must become tenant-scoped —
     * [table => [old_index => new_columns]]. settings is handled separately
     * (rework_settings_for_tenancy adds its composite at the end).
     *
     * @var array<string, array<string, list<string>>>
     */
    private const UNIQUE_REWRITES = [
        'users' => ['users_email_unique' => ['tenant_id', 'email']],
        'roles' => [
            'roles_name_unique' => ['tenant_id', 'name'],
            'roles_slug_unique' => ['tenant_id', 'slug'],
        ],
        'customers' => ['customers_customer_code_unique' => ['tenant_id', 'customer_code']],
        'vehicles' => ['vehicles_registration_number_unique' => ['tenant_id', 'registration_number']],
        'jobs' => ['jobs_job_number_unique' => ['tenant_id', 'job_number']],
        'invoices' => ['invoices_invoice_number_unique' => ['tenant_id', 'invoice_number']],
        'products' => [
            'products_sku_unique' => ['tenant_id', 'sku'],
            'products_barcode_unique' => ['tenant_id', 'barcode'],
        ],
        'purchase_orders' => ['purchase_orders_po_number_unique' => ['tenant_id', 'po_number']],
        'expenses' => ['expenses_expense_number_unique' => ['tenant_id', 'expense_number']],
        'refunds' => ['refunds_refund_number_unique' => ['tenant_id', 'refund_number']],
        'credit_notes' => ['credit_notes_credit_note_number_unique' => ['tenant_id', 'credit_note_number']],
        'goods_receipts' => ['goods_receipts_receipt_number_unique' => ['tenant_id', 'receipt_number']],
        'stock_transfers' => ['stock_transfers_transfer_number_unique' => ['tenant_id', 'transfer_number']],
        'supplier_returns' => ['supplier_returns_return_number_unique' => ['tenant_id', 'return_number']],
        'discounts' => ['discounts_code_unique' => ['tenant_id', 'code']],
        'vehicle_categories' => ['vehicle_categories_code_unique' => ['tenant_id', 'code']],
        'equipment' => ['equipment_serial_number_unique' => ['tenant_id', 'serial_number']],
        'cash_registers' => ['cash_registers_shift_number_unique' => ['tenant_id', 'shift_number']],
    ];

    /**
     * Uniques whose LEFT-MOST column is an FK column — the unique is the ONLY
     * index backing that FK, and the new composite's leftmost column
     * (tenant_id) does not satisfy it. Dropping the unique before re-adding
     * an index breaks the FK (errno 150/1553), so these get the
     * rewriteFkBackedUnique sequence instead: dropForeign → dropUnique →
     * unique([tenant_id, col]) → index(col) → re-add foreign(col).
     *
     * @var array<string, array{column: string, index: string, target: string}>
     */
    private const FK_BACKED_UNIQUES = [
        'branches' => ['column' => 'business_id', 'index' => 'branches_business_id_code_unique', 'target' => 'businesses'],
        'inventory' => ['column' => 'product_id', 'index' => 'inventory_product_id_branch_id_unique', 'target' => 'products'],
        'service_prices' => ['column' => 'service_id', 'index' => 'service_prices_service_id_vehicle_category_branch_id_unique', 'target' => 'services'],
        'service_vehicle_pricing' => ['column' => 'service_id', 'index' => 'svc_unique', 'target' => 'services'],
        'technician_skills' => ['column' => 'technician_id', 'index' => 'technician_skills_technician_id_skill_name_unique', 'target' => 'users'],
        'invoices' => ['column' => 'job_id', 'index' => 'invoices_job_id_unique', 'target' => 'jobs'],
        'inspections' => ['column' => 'job_id', 'index' => 'inspections_job_id_unique', 'target' => 'jobs'],
        'quality_checks' => ['column' => 'job_id', 'index' => 'quality_checks_job_id_unique', 'target' => 'jobs'],
        'loyalty_accounts' => ['column' => 'customer_id', 'index' => 'loyalty_accounts_customer_id_unique', 'target' => 'customers'],
    ];

    public function up(): void
    {
        $this->addTenantColumns();

        $this->backfillFromDirect();
        $this->backfillFromBranches();
        $this->backfillFromParents();
        $this->backfillFallback();
        // Second parent pass: children of fallback-assigned tables
        // (stock_transfer_items → stock_transfers, ...) still resolve.
        $this->backfillFromParents();

        $this->rewriteUniques();

        // The two tables every list and report paginates:
        Schema::table('jobs', function (Blueprint $table) {
            $table->index(['tenant_id', 'status']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        $tables = array_values(array_unique(array_merge(
            self::DIRECT_TABLES,
            self::BRANCH_TABLES,
            array_keys(self::PARENT_BACKFILLS),
            self::FALLBACK_TABLES,
        )));

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }

    private function addTenantColumns(): void
    {
        $tables = array_values(array_unique(array_merge(
            self::DIRECT_TABLES,
            self::BRANCH_TABLES,
            array_keys(self::PARENT_BACKFILLS),
            self::FALLBACK_TABLES,
        )));

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();
            });
        }
    }

    private function backfillFromDirect(): void
    {
        foreach (self::DIRECT_TABLES as $table) {
            DB::statement(
                "UPDATE {$table} t
                 SET t.tenant_id = (SELECT b.tenant_id FROM businesses b WHERE b.id = t.business_id)
                 WHERE t.tenant_id IS NULL"
            );
        }
    }

    private function backfillFromBranches(): void
    {
        foreach (self::BRANCH_TABLES as $table) {
            DB::statement(
                "UPDATE {$table} t
                 SET t.tenant_id = (SELECT b.tenant_id FROM branches b WHERE b.id = t.branch_id)
                 WHERE t.tenant_id IS NULL
                   AND EXISTS (SELECT 1 FROM branches b WHERE b.id = t.branch_id AND b.tenant_id IS NOT NULL)"
            );
        }
    }

    private function backfillFromParents(): void
    {
        foreach (self::PARENT_BACKFILLS as $child => $parents) {
            foreach ($parents as $parent => $fk) {
                DB::statement(
                    "UPDATE {$child} t
                     SET t.tenant_id = (SELECT p.tenant_id FROM {$parent} p WHERE p.id = t.{$fk})
                     WHERE t.tenant_id IS NULL
                       AND EXISTS (SELECT 1 FROM {$parent} p WHERE p.id = t.{$fk} AND p.tenant_id IS NOT NULL)"
                );
            }
        }
    }

    /**
     * Anything still unowned goes to the sole tenant (Phase 0 deleted seeder
     * junk, so there is exactly one). audit_logs never participates — central/
     * system records have no tenant and must stay invisible to tenant users.
     */
    private function backfillFallback(): void
    {
        $tenantId = DB::table('tenants')
            ->where('environment', 'live')
            ->whereNull('deleted_at')
            ->value('id');

        if ($tenantId === null || DB::table('tenants')->count() !== 1) {
            return;
        }

        foreach (self::FALLBACK_TABLES as $table) {
            DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }
    }

    private function rewriteUniques(): void
    {
        foreach (self::FK_BACKED_UNIQUES as $table => $spec) {
            $this->rewriteFkBackedUnique($table, $spec['column'], $spec['index']);
        }

        foreach (self::UNIQUE_REWRITES as $table => $rewrites) {
            foreach ($rewrites as $oldIndex => $columns) {
                Schema::table($table, function (Blueprint $table) use ($oldIndex, $columns) {
                    $table->dropUnique($oldIndex);
                    $table->unique($columns);
                });
            }
        }
    }

    /**
     * dropForeign → dropUnique → unique([tenant_id, col]) → index(col) →
     * re-add foreign(col). The composite's leftmost column (tenant_id) does
     * not satisfy the FK, so the bare-column index must come back too.
     */
    private function rewriteFkBackedUnique(string $table, string $column, string $index): void
    {
        Schema::table($table, function (Blueprint $table) use ($column, $index) {
            $table->dropForeign([$column]);
            $table->dropUnique($index);
        });

        $target = $this->fkTarget($column);

        Schema::table($table, function (Blueprint $table) use ($column, $target) {
            $table->unique(['tenant_id', $column]);
            $table->index($column);
            $table->foreign($column)->references('id')
                ->on($target); // cascade delete, matching the original constraint
        });
    }

    private function fkTarget(string $column): string
    {
        return match ($column) {
            'business_id' => 'businesses',
            'product_id' => 'products',
            'service_id' => 'services',
            'technician_id' => 'users',
            'job_id' => 'jobs',
            'customer_id' => 'customers',
            default => throw new RuntimeException("Unknown FK target for {$column}."),
        };
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fresh data for tenant ID 3 (Prasad) from specified tables.
     */
    public function up(): void
    {
        $tenantId = 3;

        // Tables to clear for tenant 3 - in dependency order (children first)
        $tables = [
            // Job-related children
            'job_status_history',
            'job_parts',
            'job_services',
            'additional_work_requests',

            // Invoice-related children
            'invoice_items',
            'payments', // includes cheque data

            // Invoice-related (parent)
            'invoices',

            // Job-related (parent)
            'jobs',

            // Vehicle-related
            'vehicles',

            // Customer-related
            'customers',

            // Appointment-related
            'appointments',

            // Notification-related
            'notifications',

            // Till-related
            'cash_movements',
            'till_closures',
            'tills',

            // Audit logs
            'audit_logs',

            // Report-related (if there's a reports table)
            // Note: reports might be generated on demand, not stored
        ];

        DB::beginTransaction();

        try {
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)
                        ->where('tenant_id', $tenantId)
                        ->delete();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse data deletion
        // This migration is one-way by design
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Seed GRN statuses
        $grnStatuses = [
            ['group' => 'grn_statuses', 'key' => 'draft', 'value' => 'Draft', 'type' => 'string', 'description' => 'GRN is in draft state'],
            ['group' => 'grn_statuses', 'key' => 'confirmed', 'value' => 'Confirmed', 'type' => 'string', 'description' => 'GRN has been confirmed and stock added'],
            ['group' => 'grn_statuses', 'key' => 'deleted', 'value' => 'Deleted', 'type' => 'string', 'description' => 'GRN has been deleted'],
        ];

        foreach ($grnStatuses as $status) {
            DB::table('settings')->updateOrInsert(
                ['group' => $status['group'], 'key' => $status['key']],
                $status
            );
        }

        // Seed supplier statuses
        $supplierStatuses = [
            ['group' => 'supplier_statuses', 'key' => 'active', 'value' => 'Active', 'type' => 'string', 'description' => 'Supplier is active'],
            ['group' => 'supplier_statuses', 'key' => 'blacklisted', 'value' => 'Blacklisted', 'type' => 'string', 'description' => 'Supplier is blacklisted'],
        ];

        foreach ($supplierStatuses as $status) {
            DB::table('settings')->updateOrInsert(
                ['group' => $status['group'], 'key' => $status['key']],
                $status
            );
        }

        // Seed ledger entry types
        $ledgerEntryTypes = [
            ['group' => 'ledger_entry_types', 'key' => 'debit', 'value' => 'Debit', 'type' => 'string', 'description' => 'Debit entry (increases balance)'],
            ['group' => 'ledger_entry_types', 'key' => 'credit', 'value' => 'Credit', 'type' => 'string', 'description' => 'Credit entry (decreases balance)'],
        ];

        foreach ($ledgerEntryTypes as $type) {
            DB::table('settings')->updateOrInsert(
                ['group' => $type['group'], 'key' => $type['key']],
                $type
            );
        }

        // Seed address types
        $addressTypes = [
            ['group' => 'address_types', 'key' => 'billing', 'value' => 'Billing', 'type' => 'string', 'description' => 'Billing address'],
            ['group' => 'address_types', 'key' => 'shipping', 'value' => 'Shipping', 'type' => 'string', 'description' => 'Shipping address'],
        ];

        foreach ($addressTypes as $type) {
            DB::table('settings')->updateOrInsert(
                ['group' => $type['group'], 'key' => $type['key']],
                $type
            );
        }

        // Seed payment terms
        $paymentTerms = [
            ['group' => 'payment_terms', 'key' => 'net_30', 'value' => 'Net 30', 'type' => 'string', 'description' => 'Payment due in 30 days'],
            ['group' => 'payment_terms', 'key' => 'net_60', 'value' => 'Net 60', 'type' => 'string', 'description' => 'Payment due in 60 days'],
            ['group' => 'payment_terms', 'key' => 'cod', 'value' => 'COD', 'type' => 'string', 'description' => 'Cash on delivery'],
        ];

        foreach ($paymentTerms as $term) {
            DB::table('settings')->updateOrInsert(
                ['group' => $term['group'], 'key' => $term['key']],
                $term
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('group', ['grn_statuses', 'supplier_statuses', 'ledger_entry_types', 'address_types', 'payment_terms'])
            ->delete();
    }
};

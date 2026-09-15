<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The only path that mutates suppliers.outstanding_balance.
 *
 * `debit()`   increases what we owe the supplier (a GRN was received on credit).
 * `credit()`  decreases it (a payment was made to the supplier).
 */
class SupplierService
{
    public function debit(Supplier $supplier, float $amount, ?Model $reference = null, ?string $note = null): SupplierLedger
    {
        if ($amount <= 0) {
            throw new RuntimeException('Debit amount must be positive.');
        }

        return DB::transaction(function () use ($supplier, $amount, $reference, $note) {
            $row = Supplier::query()->whereKey($supplier->id)->lockForUpdate()->first();
            $newBalance = (float) $row->outstanding_balance + $amount;
            $row->update(['outstanding_balance' => $newBalance]);

            return SupplierLedger::create([
                'supplier_id' => $supplier->id,
                'ledger_entry_type_id' => $this->getLedgerEntryTypeId('debit'),
                'amount' => $amount,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'reference_number' => $this->getReferenceNumber($reference),
                'balance_after' => $newBalance,
                'note' => $note,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);
        });
    }

    public function credit(Supplier $supplier, float $amount, ?Model $reference = null, ?string $note = null): SupplierLedger
    {
        if ($amount <= 0) {
            throw new RuntimeException('Credit amount must be positive.');
        }

        return DB::transaction(function () use ($supplier, $amount, $reference, $note) {
            $row = Supplier::query()->whereKey($supplier->id)->lockForUpdate()->first();
            $newBalance = max(0.0, (float) $row->outstanding_balance - $amount);
            $row->update(['outstanding_balance' => $newBalance]);

            return SupplierLedger::create([
                'supplier_id' => $supplier->id,
                'ledger_entry_type_id' => $this->getLedgerEntryTypeId('credit'),
                'amount' => $amount,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'reference_number' => $this->getReferenceNumber($reference),
                'balance_after' => $newBalance,
                'note' => $note,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);
        });
    }

    public function isOverLimit(Supplier $supplier, float $additionalAmount): bool
    {
        if ((float) $supplier->credit_limit <= 0) {
            return false;
        }

        return (float) $supplier->outstanding_balance + $additionalAmount > (float) $supplier->credit_limit;
    }

    public function blacklist(Supplier $supplier, string $reason): void
    {
        $supplier->update([
            'is_blacklisted' => true,
            'blacklisted_reason' => $reason,
            'blacklisted_at' => now(),
            'blacklisted_by' => Auth::id(),
            'status_id' => $this->getStatusId('blacklisted'),
        ]);

        // Log the blacklist action
        if (class_exists(\App\Services\AuditService::class)) {
            app(\App\Services\AuditService::class)->log(
                'supplier.blacklisted',
                "Supplier {$supplier->name} blacklisted: {$reason}",
                'warning',
                'tenant_user',
                auth()->user()->email ?? null,
                ['reason' => $reason, 'supplier_id' => $supplier->id]
            );
        }
    }

    public function unblacklist(Supplier $supplier): void
    {
        $supplier->update([
            'is_blacklisted' => false,
            'blacklisted_reason' => null,
            'blacklisted_at' => null,
            'blacklisted_by' => null,
            'status_id' => $this->getStatusId('active'),
        ]);

        // Log the unblacklist action
        if (class_exists(\App\Services\AuditService::class)) {
            app(\App\Services\AuditService::class)->log(
                'supplier.unblacklisted',
                "Supplier {$supplier->name} reinstated",
                'info',
                'tenant_user',
                auth()->user()->email ?? null,
                ['supplier_id' => $supplier->id]
            );
        }
    }

    private function getLedgerEntryTypeId(string $type): ?int
    {
        // Try to find the ledger entry type in settings
        $setting = \App\Models\Setting::where('group', 'ledger_entry_types')
            ->where('key', $type)
            ->first();

        return $setting ? (int) $setting->id : null;
    }

    private function getStatusId(string $status): ?int
    {
        // Try to find the status in settings
        $setting = \App\Models\Setting::where('group', 'supplier_statuses')
            ->where('key', $status)
            ->first();

        return $setting ? (int) $setting->id : null;
    }

    private function getSupplierStatusKey(?int $statusId): ?string
    {
        if (!$statusId) {
            return null;
        }

        $setting = \App\Models\Setting::find($statusId);
        return $setting ? $setting->key : null;
    }

    private function getReferenceNumber(?Model $reference): ?string
    {
        if (! $reference) {
            return null;
        }

        // Try common reference number fields
        if (method_exists($reference, 'getGrnNumberAttribute')) {
            return $reference->grn_number;
        }
        if (method_exists($reference, 'getPoNumberAttribute')) {
            return $reference->po_number;
        }
        if (method_exists($reference, 'getReceiptNumberAttribute')) {
            return $reference->receipt_number;
        }
        if (isset($reference->grn_number)) {
            return $reference->grn_number;
        }
        if (isset($reference->po_number)) {
            return $reference->po_number;
        }
        if (isset($reference->receipt_number)) {
            return $reference->receipt_number;
        }

        return null;
    }
}

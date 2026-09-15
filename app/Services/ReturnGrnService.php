<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ReturnGrn;
use App\Models\ReturnGrnItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

/**
 * Return items back to supplier via Return GRN.
 * Simplified version without batch/serial tracking.
 */
class ReturnGrnService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly SupplierService $suppliers,
        private readonly DocumentNumberService $documentNumbers,
    ) {}

    /**
     * @param  array{supplier_id?:int,reference?:string,address?:string,reason?:string,notes?:string}  $header
     * @param  array<int,array{product_id:int,quantity:float,unit_cost:float|null,notes?:string}>  $lines
     */
    public function create(array $header, array $lines): ReturnGrn
    {
        return app(\App\Services\CurrentContext::class)->runForTenant(
            app(\App\Services\CurrentContext::class)->tenantId(),
            function () use ($header, $lines) {
                $returnGrnNumber = 'RGRN-' . date('Y') . '-' . str_pad(ReturnGrn::count() + 1, 6, '0', STR_PAD_LEFT);

                $returnGrn = new ReturnGrn();
                $returnGrn->return_grn_number = $returnGrnNumber;
                $returnGrn->supplier_id = $header['supplier_id'] ?? null;
                $returnGrn->reference = $header['reference'] ?? null;
                $returnGrn->address = $header['address'] ?? null;
                $returnGrn->reason = $header['reason'] ?? null;
                $returnGrn->notes = $header['notes'] ?? null;
                $returnGrn->total_cost = 0;
                $returnGrn->returned_by = auth()->id();
                $returnGrn->returned_at = now();

                // Set initial status to draft
                $draftStatusId = $this->getStatusId('return_draft');
                $returnGrn->status_id = $draftStatusId;

                $returnGrn->save();

                // Validate inventory before creating items
                foreach ($lines as $line) {
                    $product = Product::find($line['product_id']);
                    if ($product) {
                        $currentStock = $this->inventory->getStock($product, null);
                        $returnQuantity = (float) $line['quantity'];
                        if ($currentStock < $returnQuantity) {
                            throw new \InvalidArgumentException(
                                "Insufficient inventory for product '{$product->name}'. Current stock: {$currentStock}, trying to return: {$returnQuantity}"
                            );
                        }
                    }
                }

                $totalCost = 0.0;

                foreach ($lines as $line) {
                    $unitCost = $line['unit_cost'] ?? null;
                    $lineTotal = $unitCost !== null ? round($unitCost * (float) $line['quantity'], 2) : 0.0;
                    $totalCost += $lineTotal;

                    ReturnGrnItem::create([
                        'return_grn_id' => $returnGrn->id,
                        'product_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'unit_cost' => $unitCost,
                        'total_cost' => $lineTotal,
                        'notes' => $line['notes'] ?? null,
                        'tenant_id' => $returnGrn->tenant_id,
                    ]);
                }

                $returnGrn->total_cost = round($totalCost, 2);
                $returnGrn->save();

                // Log the return GRN creation
                if (class_exists(\App\Services\AuditService::class)) {
                    app(\App\Services\AuditService::class)->log(
                        'return_grn.created',
                        "Return GRN {$returnGrn->return_grn_number} created",
                        'info',
                        'tenant_user',
                        auth()->user()->email ?? null,
                        [
                            'return_grn_number' => $returnGrn->return_grn_number,
                            'reference' => $header['reference'] ?? null,
                            'reason' => $header['reason'] ?? null,
                            'items_count' => count($lines),
                            'total_cost' => $totalCost,
                            'status' => 'draft',
                        ]
                    );
                }

                return $returnGrn;
            }
        );
    }

    /**
     * Confirm a draft return GRN: reverse inventory and credit supplier ledger.
     */
    public function confirm(ReturnGrn $returnGrn, int $confirmedByUserId): ReturnGrn
    {
        return DB::transaction(function () use ($returnGrn, $confirmedByUserId) {
            $returnGrn->load([
                'items.product',
                'supplier',
            ]);

            $totalCost = 0.0;

            foreach ($returnGrn->items as $item) {
                $product = $item->product;
                $quantity = (float) $item->quantity;

                // Reverse inventory by adjusting with negative quantity
                $this->inventory->adjust(
                    $product,
                    null,  // branchId (no branch tracking)
                    -$quantity,
                    "Return GRN: {$returnGrn->return_grn_number}",
                    'supplier_return'
                );

                if ($item->unit_cost !== null) {
                    $totalCost += (float) $item->unit_cost * $quantity;
                }
            }

            // Credit supplier ledger if supplier exists and cost > 0
            if ($returnGrn->supplier_id && $totalCost > 0) {
                $this->suppliers->credit(
                    Supplier::findOrFail($returnGrn->supplier_id),
                    $totalCost,
                    $returnGrn,
                    "Return GRN {$returnGrn->return_grn_number} sent to supplier"
                );
            }

            // Update return GRN status to confirmed
            $confirmedStatusId = $this->getStatusId('return_confirmed');
            $returnGrn->update([
                'status_id' => $confirmedStatusId,
                'returned_by' => $confirmedByUserId,
                'returned_at' => now(),
            ]);

            // Log the confirmation
            if (class_exists(\App\Services\AuditService::class)) {
                app(\App\Services\AuditService::class)->log(
                    'return_grn.confirmed',
                    "Return GRN {$returnGrn->return_grn_number} confirmed",
                    'info',
                    'tenant_user',
                    auth()->user()->email ?? null,
                    [
                        'return_grn_number' => $returnGrn->return_grn_number,
                        'items_count' => $returnGrn->items->count(),
                        'total_cost' => $totalCost,
                    ]
                );
            }

            return $returnGrn;
        });
    }

    /**
     * Delete a return GRN.
     * Reverses stock if the return GRN was confirmed; always sets status to Deleted.
     */
    public function delete(ReturnGrn $returnGrn, int $deletedByUserId): void
    {
        if ($returnGrn->isDeleted()) {
            throw new \InvalidArgumentException('Return GRN is already deleted.');
        }

        DB::transaction(function () use ($returnGrn, $deletedByUserId) {
            $stockReversed = $returnGrn->isConfirmed();

            if ($stockReversed) {
                $returnGrn->load([
                    'items.product',
                    'supplier',
                ]);

                $totalCost = 0.0;

                foreach ($returnGrn->items as $item) {
                    $product = $item->product;
                    $quantity = (float) $item->quantity;

                    // Restore inventory by adjusting with positive quantity
                    $this->inventory->adjust(
                        $product,
                        null,  // branchId (no branch tracking)
                        $quantity,
                        "Return GRN deletion reversal: {$returnGrn->return_grn_number}",
                        'adjustment_reversal'
                    );

                    if ($item->unit_cost !== null) {
                        $totalCost += (float) $item->unit_cost * $quantity;
                    }
                }

                // Debit supplier ledger if supplier exists and cost > 0
                if ($returnGrn->supplier_id && $totalCost > 0) {
                    $this->suppliers->debit(
                        Supplier::findOrFail($returnGrn->supplier_id),
                        $totalCost,
                        $returnGrn,
                        "Reversal of Return GRN {$returnGrn->return_grn_number} deletion"
                    );
                }
            }

            // Set status to Deleted and stamp the deletion audit columns
            $deletedStatusId = $this->getStatusId('return_deleted');
            $returnGrn->status_id = $deletedStatusId;
            $returnGrn->deleted_by = $deletedByUserId;
            $returnGrn->deleted_at = now();
            $returnGrn->save();

            // Log the deletion
            if (class_exists(\App\Services\AuditService::class)) {
                app(\App\Services\AuditService::class)->log(
                    'return_grn.deleted',
                    "Return GRN {$returnGrn->return_grn_number} deleted",
                    'warning',
                    'tenant_user',
                    auth()->user()->email ?? null,
                    [
                        'return_grn_number' => $returnGrn->return_grn_number,
                        'previous_status' => $returnGrn->status?->key ?? 'unknown',
                        'items_count' => $returnGrn->items->count(),
                        'stock_reversed' => $stockReversed,
                    ]
                );
            }
        });
    }

    private function getStatusId(string $status): ?int
    {
        // Try to find the status in settings
        $setting = \App\Models\Setting::where('key', $status)
            ->first();

        if ($setting) {
            return (int) $setting->id;
        }

        return null;
    }
}

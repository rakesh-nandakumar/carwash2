<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

/**
 * Stock-in via GRN. Each line writes a `purchase` movement through the
 * InventoryService (single source of truth).
 */
class GrnService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly SupplierService $suppliers,
        private readonly PurchaseOrderService $purchaseOrders,
        private readonly DocumentNumberService $documentNumbers,
    ) {}

    /**
     * @param  array{supplier_id?:int,purchase_order_id?:int,reference?:string,note?:string}  $header
     * @param  array<int,array{product_id:int,quantity:float,unit_cost:float|null,sale_price:float|null}>  $lines
     */
    public function create(array $header, array $lines): GoodsReceipt
    {
        return app(\App\Services\CurrentContext::class)->runForTenant(
            app(\App\Services\CurrentContext::class)->tenantId(),
            function () use ($header, $lines) {
                $grnNumber = 'GRN-' . date('Y') . '-' . str_pad(GoodsReceipt::count() + 1, 6, '0', STR_PAD_LEFT);
                
                $grn = new GoodsReceipt();
                $grn->grn_number = $grnNumber;
                $grn->supplier_id = $header['supplier_id'] ?? null;
                $grn->purchase_order_id = $header['purchase_order_id'] ?? null;
                $grn->reference = $header['reference'] ?? null;
                $grn->notes = $header['note'] ?? null;
                $grn->receipt_number = $grnNumber;
                $grn->received_by = auth()->id();
                $grn->received_at = now();

                // Set initial status to draft
                $draftStatusId = $this->getStatusId('draft');
                \Log::info("Setting GRN to draft", ['draft_status_id' => $draftStatusId]);
                $grn->status_id = $draftStatusId;

                $grn->save();

                \Log::info("GRN saved with status", ['grn_id' => $grn->id, 'status_id' => $grn->status_id]);

            \Log::info('GRN saved, now creating items', ['grn_id' => $grn->id, 'items_count' => count($lines)]);

            foreach ($lines as $line) {
                \Log::info('Creating item', ['line' => $line]);
                try {
                    GoodsReceiptItem::create([
                        'goods_receipt_id' => $grn->id,
                        'product_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'unit_cost' => $line['unit_cost'] ?? null,
                        'sale_price' => $line['sale_price'] ?? null,
                        'notes' => $line['notes'] ?? null,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create GRN item', ['error' => $e->getMessage()]);
                }
            }

            \Log::info('Items created successfully');

            // Log the GRN creation
            if (class_exists(\App\Services\AuditService::class)) {
                app(\App\Services\AuditService::class)->log(
                    'grn.created',
                    "GRN {$grn->grn_number} created",
                    'info',
                    'tenant_user',
                    auth()->user()->email ?? null,
                    [
                        'grn_number' => $grn->grn_number,
                        'reference' => $header['reference'] ?? null,
                        'items_count' => count($lines),
                        'note' => $header['note'] ?? null,
                        'status' => 'draft',
                    ]
                );
            }

            return $grn;
        });
    }

    /**
     * Confirm a draft GRN: records all stock movements and updates the supplier ledger.
     */
    public function confirm(GoodsReceipt $grn, int $confirmedByUserId): GoodsReceipt
    {
        return DB::transaction(function () use ($grn, $confirmedByUserId) {
            $grn->load([
                'items.product',
                'supplier',
            ]);

            $totalCost = 0.0;
            $receivedByProductId = [];

            foreach ($grn->items as $item) {
                $product = $item->product;
                $quantity = (float) $item->quantity;

                // Add to inventory using InventoryService
                $this->inventory->adjust(
                    $product,
                    null,  // branchId (no branch tracking)
                    $quantity,
                    "GRN receipt: {$grn->grn_number}",
                    'purchase'
                );

                // Update the sale price on the product if provided
                if ($item->sale_price !== null && $item->sale_price > 0) {
                    $product->update(['selling_price' => $item->sale_price]);
                }

                $receivedByProductId[$item->product_id] = ($receivedByProductId[$item->product_id] ?? 0) + $quantity;

                if ($item->unit_cost !== null) {
                    $totalCost += (float) $item->unit_cost * $quantity;
                }
            }

            // Debit supplier ledger if supplier exists and cost > 0
            if ($grn->supplier_id && $totalCost > 0) {
                $this->suppliers->debit(
                    Supplier::findOrFail($grn->supplier_id),
                    $totalCost,
                    $grn,
                    "GRN {$grn->grn_number} received"
                );
            }

            // Update purchase order if linked
            if ($grn->purchase_order_id) {
                $this->purchaseOrders->applyGrnReceipt(
                    PurchaseOrder::findOrFail($grn->purchase_order_id),
                    $receivedByProductId
                );
            }

            // Update GRN status to confirmed
            $confirmedStatusId = $this->getStatusId('confirmed');
            \Log::info("Confirming GRN", ['grn_id' => $grn->id, 'confirmed_status_id' => $confirmedStatusId]);
            $grn->update([
                'status_id' => $confirmedStatusId,
                'confirmed_by' => $confirmedByUserId,
                'confirmed_at' => now(),
            ]);
            \Log::info("GRN confirmed", ['grn_id' => $grn->id, 'new_status_id' => $grn->status_id]);

            // Log the confirmation
            if (class_exists(\App\Services\AuditService::class)) {
                app(\App\Services\AuditService::class)->log(
                    'grn.confirmed',
                    "GRN {$grn->grn_number} confirmed",
                    'info',
                    'tenant_user',
                    auth()->user()->email ?? null,
                    [
                        'grn_number' => $grn->grn_number,
                        'items_count' => $grn->items->count(),
                        'total_cost' => $totalCost,
                    ]
                );
            }

            return $grn;
        });
    }

    /**
     * Delete a GRN (admin-only).
     * Reverses stock if the GRN was confirmed; always sets status to Deleted.
     */
    public function delete(GoodsReceipt $grn, int $deletedByUserId): void
    {
        if ($grn->isDeleted()) {
            throw new \InvalidArgumentException('GRN is already deleted.');
        }

        DB::transaction(function () use ($grn, $deletedByUserId) {
            $stockReversed = $grn->isConfirmed();

            if ($stockReversed) {
                $grn->load([
                    'items.product',
                    'supplier',
                ]);

                $totalCost = 0.0;

                foreach ($grn->items as $item) {
                    $product = $item->product;
                    $quantity = (float) $item->quantity;

                    // Reverse inventory by adjusting with negative quantity
                    $this->inventory->adjust(
                        $product,
                        null,  // branchId (no branch tracking)
                        -$quantity,
                        "GRN deletion reversal: {$grn->grn_number}",
                        'adjustment_reversal'
                    );

                    if ($item->unit_cost !== null) {
                        $totalCost += (float) $item->unit_cost * $quantity;
                    }
                }

                // Credit supplier ledger if supplier exists and cost > 0
                if ($grn->supplier_id && $totalCost > 0) {
                    $this->suppliers->credit(
                        Supplier::findOrFail($grn->supplier_id),
                        $totalCost,
                        $grn,
                        "Reversal of GRN {$grn->grn_number} deletion"
                    );
                }
            }

            // Set status to Deleted and stamp the deletion audit columns
            $deletedStatusId = $this->getStatusId('deleted');
            $grn->status_id = $deletedStatusId;
            $grn->deleted_by = $deletedByUserId;
            $grn->deleted_at = now();
            $grn->save();

            // Log the deletion
            if (class_exists(\App\Services\AuditService::class)) {
                app(\App\Services\AuditService::class)->log(
                    'grn.deleted',
                    "GRN {$grn->grn_number} deleted",
                    'warning',
                    'tenant_user',
                    auth()->user()->email ?? null,
                    [
                        'grn_number' => $grn->grn_number,
                        'previous_status' => $grn->status?->key ?? 'unknown',
                        'items_count' => $grn->items->count(),
                        'stock_reversed' => $stockReversed,
                    ]
                );
            }
        });
    }

    /**
     * Revert a confirmed GRN back to draft.
     * Reverses stock and supplier ledger, but keeps the GRN as draft (not deleted).
     */
    public function revert(GoodsReceipt $grn, int $revertedByUserId): void
    {
        if (!$grn->isConfirmed()) {
            throw new \InvalidArgumentException('Only confirmed GRNs can be reverted to draft.');
        }

        DB::transaction(function () use ($grn, $revertedByUserId) {
            $grn->load([
                'items.product',
                'supplier',
            ]);

            $totalCost = 0.0;

            foreach ($grn->items as $item) {
                $product = $item->product;
                $quantity = (float) $item->quantity;

                // Reverse inventory by adjusting with negative quantity
                $this->inventory->adjust(
                    $product,
                    null,  // branchId (no branch tracking)
                    -$quantity,
                    "GRN revert to draft: {$grn->grn_number}",
                    'adjustment_reversal'
                );

                if ($item->unit_cost !== null) {
                    $totalCost += (float) $item->unit_cost * $quantity;
                }
            }

            // Credit supplier ledger if supplier exists and cost > 0
            if ($grn->supplier_id && $totalCost > 0) {
                $this->suppliers->credit(
                    Supplier::findOrFail($grn->supplier_id),
                    $totalCost,
                    $grn,
                    "Reversal of GRN {$grn->grn_number} revert to draft"
                );
            }

            // Set status back to draft
            $draftStatusId = $this->getStatusId('draft');
            $grn->update([
                'status_id' => $draftStatusId,
                'confirmed_by' => null,
                'confirmed_at' => null,
            ]);

            // Log the revert
            if (class_exists(\App\Services\AuditService::class)) {
                app(\App\Services\AuditService::class)->log(
                    'grn.reverted',
                    "GRN {$grn->grn_number} reverted to draft",
                    'info',
                    'tenant_user',
                    auth()->user()->email ?? null,
                    [
                        'grn_number' => $grn->grn_number,
                        'items_count' => $grn->items->count(),
                        'total_cost' => $totalCost,
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
            \Log::info("Found status setting", ['status' => $status, 'id' => $setting->id, 'value' => $setting->value]);
            return (int) $setting->id;
        }

        \Log::warning("Status setting not found", ['status' => $status]);
        return null;
    }
}

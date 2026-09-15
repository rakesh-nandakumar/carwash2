<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\SupplierReturn;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(
        private InventoryService $inventory,
        private DocumentNumberService $documentNumbers,
    ) {}

    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $poNumber = $this->documentNumbers->next('purchase_order');

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'business_id' => $data['business_id'],
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'],
                'status' => 'draft',
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'expected_date' => $data['expected_date'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Add items
            foreach ($data['items'] as $item) {
                $this->addItem($purchaseOrder, $item);
            }

            // Recalculate totals
            $this->recalculateTotals($purchaseOrder);

            return $purchaseOrder;
        });
    }

    public function addItem(PurchaseOrder $purchaseOrder, array $itemData): PurchaseOrderItem
    {
        return DB::transaction(function () use ($purchaseOrder, $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            
            $taxAmount = $itemData['quantity'] * $itemData['unit_price'] * ($itemData['tax_rate'] ?? 0) / 100;
            $lineTotal = $itemData['quantity'] * $itemData['unit_price'] + $taxAmount;

            $item = PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'tax_amount' => $taxAmount,
                'total' => $lineTotal,
            ]);

            $this->recalculateTotals($purchaseOrder);

            return $item;
        });
    }

    private function recalculateTotals(PurchaseOrder $purchaseOrder): void
    {
        $subtotal = $purchaseOrder->items->sum('total');
        $tax = $purchaseOrder->items->sum('tax_amount');
        $total = $subtotal + $tax;

        $purchaseOrder->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
    }

    public function submitForApproval(int $purchaseOrderId): PurchaseOrder
    {
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        
        $purchaseOrder->update(['status' => 'pending_approval']);
        
        return $purchaseOrder;
    }

    public function approvePurchaseOrder(int $purchaseOrderId, int $approvedBy): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrderId, $approvedBy) {
            $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
            
            $purchaseOrder->update([
                'status' => 'ordered',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            return $purchaseOrder;
        });
    }

    public function rejectPurchaseOrder(int $purchaseOrderId, string $reason): PurchaseOrder
    {
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        
        $purchaseOrder->update([
            'status' => 'cancelled',
        ]);

        return $purchaseOrder;
    }

    public function receiveGoods(int $purchaseOrderId, array $receivedItems, int $receivedBy): GoodsReceipt
    {
        return DB::transaction(function () use ($purchaseOrderId, $receivedItems, $receivedBy) {
            $purchaseOrder = PurchaseOrder::with('items')->findOrFail($purchaseOrderId);
            
            $grnNumber = $this->documentNumbers->next('grn');

            // Get draft status ID
            $statusId = $this->getStatusId('draft');

            $goodsReceipt = GoodsReceipt::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $purchaseOrderId,
                'branch_id' => $purchaseOrder->branch_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'status_id' => $statusId,
                'received_by' => $receivedBy,
                'received_at' => now(),
            ]);

            // Create GRN items with received quantities
            foreach ($receivedItems as $receivedItem) {
                $poItem = $purchaseOrder->items->where('product_id', $receivedItem['product_id'])->first();
                
                if ($poItem) {
                    GoodsReceiptItem::create([
                        'goods_receipt_id' => $goodsReceipt->id,
                        'product_id' => $receivedItem['product_id'],
                        'quantity' => $receivedItem['quantity'],
                        'unit_cost' => $poItem->unit_price,
                        'sale_price' => null, // Will be set during confirmation
                    ]);
                }
            }

            return $goodsReceipt;
        });
    }

    /**
     * Called by GrnService::confirm() when a GRN references this PO. Updates
     * each line's received_quantity and flips status to partial/received.
     *
     * @param  array<int,float>  $receivedQtyByProductId  quantity received per product_id
     */
    public function applyGrnReceipt(PurchaseOrder $po, array $receivedQtyByProductId): void
    {
        foreach ($po->items as $item) {
            $received = $receivedQtyByProductId[$item->product_id] ?? 0.0;
            if ($received > 0) {
                $item->increment('received_quantity', $received);
            }
        }

        $po->refresh()->load('items');
        $allReceived = $po->items->every(function ($item) {
            return $item->received_quantity >= $item->quantity;
        });

        $newStatus = $allReceived ? 'received' : 'partially_received';
        $po->update(['status' => $newStatus]);

        // Log the PO receipt application
        if (class_exists(\App\Services\AuditService::class)) {
            app(\App\Services\AuditService::class)->log(
                'purchase_order.receipt_applied',
                "Purchase Order {$po->po_number} receipt applied: {$newStatus}",
                'info',
                'tenant_user',
                auth()->user()->email ?? null,
                ['po_number' => $po->po_number, 'status' => $newStatus]
            );
        }
    }

    public function createSupplierReturn(array $data): SupplierReturn
    {
        return DB::transaction(function () use ($data) {
            $returnNumber = 'SR-' . date('Y') . '-' . str_pad(
                SupplierReturn::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            return SupplierReturn::create([
                'return_number' => $returnNumber,
                'supplier_id' => $data['supplier_id'],
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'branch_id' => $data['branch_id'],
                'status' => 'pending',
                'total_amount' => $data['total_amount'] ?? 0,
                'created_by' => auth()->id(),
                'reason' => $data['reason'] ?? null,
            ]);
        });
    }

    public function approveSupplierReturn(int $returnId, int $approvedBy): SupplierReturn
    {
        return DB::transaction(function () use ($returnId, $approvedBy) {
            $supplierReturn = SupplierReturn::findOrFail($returnId);
            
            $supplierReturn->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            // Remove items from inventory
            if ($supplierReturn->purchase_order_id) {
                $purchaseOrder = PurchaseOrder::with('items')->find($supplierReturn->purchase_order_id);
                if ($purchaseOrder) {
                    foreach ($purchaseOrder->items as $item) {
                        $product = Product::find($item->product_id);
                        $this->inventory->adjust(
                            $product,
                            $supplierReturn->branch_id,
                            -$item->received_quantity,
                            'Supplier return: ' . $supplierReturn->return_number,
                            'supplier_return'
                        );
                    }
                }
            }

            return $supplierReturn;
        });
    }

    public function getPurchaseOrderDetails(int $purchaseOrderId): array
    {
        $purchaseOrder = PurchaseOrder::with([
            'items.product',
            'supplier',
            'branch',
            'createdBy',
            'approvedBy',
            'goodsReceipts'
        ])->findOrFail($purchaseOrderId);

        return [
            'purchase_order' => $purchaseOrder,
            'items' => $purchaseOrder->items,
            'supplier' => $purchaseOrder->supplier,
            'branch' => $purchaseOrder->branch,
            'created_by' => $purchaseOrder->createdBy,
            'approved_by' => $purchaseOrder->approvedBy,
            'receipts' => $purchaseOrder->goodsReceipts,
        ];
    }

    public function getPendingPurchaseOrders(?int $branchId = null): array
    {
        $query = PurchaseOrder::with(['supplier', 'branch'])
            ->whereIn('status', ['pending_approval', 'ordered']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get()->map(function ($po) {
            return [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'supplier' => $po->supplier->name,
                'branch' => $po->branch->name,
                'total' => $po->total,
                'status' => $po->status,
                'expected_date' => $po->expected_date?->format('Y-m-d'),
                'created_at' => $po->created_at->format('Y-m-d'),
            ];
        })->toArray();
    }

    private function getStatusId(string $status): ?int
    {
        // Try to find the status in settings
        $setting = \App\Models\Setting::where('group', 'grn_statuses')
            ->where('key', $status)
            ->first();

        return $setting ? (int) $setting->id : null;
    }

    private function getPoStatusId(string $status): ?int
    {
        // Try to find the status in settings for purchase orders
        $setting = \App\Models\Setting::where('group', 'purchase_order_statuses')
            ->where('key', $status)
            ->first();

        return $setting ? (int) $setting->id : null;
    }
}

<?php

namespace App\Services;

use App\Models\{
    Inventory,
    InventoryMovement,
    Product,
    Job,
    StockTransfer,
    StockTransferItem,
    EmergencyPurchase,
    CustomerSuppliedPart,
    StockAdjustment
};
use App\Enums\InventoryMovementType;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function adjust(
        Product $product,
        int $branchId,
        float $qty,
        string $reason,
        string $type = 'adjustment'
    ): Inventory {
        // Protect against negative / zero quantities coming from Item Master
        if ($qty <= 0) {
            abort(422, 'Stock can only be increased from Item Master.');
        }

        return DB::transaction(function () use ($product, $branchId, $qty, $reason, $type) {
            $i = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (!$i) {
                $i = Inventory::create([
                    'product_id' => $product->id,
                    'branch_id' => $branchId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);

                $i = Inventory::where('id', $i->id)
                    ->lockForUpdate()
                    ->first();
            }

            $i->quantity += $qty;

            if ($i->quantity < 0) {
                abort(422, 'Insufficient inventory.');
            }

            $i->save();

            InventoryMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'type' => $type,
                'quantity' => $qty,
                'unit_cost' => $product->cost_price,
                'user_id' => auth()->id(),
                'reason' => $reason,
            ]);

            return $i;
        });
    }

    public function reserve(Product $product, int $branchId, float $qty): void
    {
        DB::transaction(function () use ($product, $branchId, $qty) {
            $i = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($i->quantity - $i->reserved_quantity < $qty) {
                abort(422, 'Insufficient available inventory.');
            }

            $i->reserved_quantity += $qty;
            $i->save();
        });
    }

    public function releaseReservation(Product $product, int $branchId, float $qty): void
    {
        DB::transaction(function () use ($product, $branchId, $qty) {
            $i = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->firstOrFail();

            $i->reserved_quantity = max(0, $i->reserved_quantity - $qty);
            $i->save();
        });
    }

    public function consume(
        Product $product,
        int $branchId,
        float $qty,
        int $jobId,
        string $referenceType = 'job'
    ): void {
        DB::transaction(function () use (
            $product,
            $branchId,
            $qty,
            $jobId,
            $referenceType
        ) {
            $inventory = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw new \RuntimeException(
                    "No inventory record found for {$product->name}."
                );
            }

            $available = $inventory->quantity - $inventory->reserved_quantity;

            if ($available < $qty) {
                throw new \RuntimeException(
                    "Insufficient stock for {$product->name}. " .
                    "Available: {$available}, Required: {$qty}."
                );
            }

            $inventory->quantity -= $qty;

            $inventory->reserved_quantity = max(
                0,
                $inventory->reserved_quantity - $qty
            );

            $inventory->save();

            InventoryMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'type' => InventoryMovementType::SERVICE_USAGE->value,
                'quantity' => -$qty,
                'unit_cost' => $product->cost_price,
                'reference_type' => $referenceType,
                'reference_id' => $jobId,
                'user_id' => auth()->id(),
                'reason' => 'Service usage',
            ]);
        });
    }

    /**
     * Consume all approved but not yet applied job parts.
     * Validates ALL parts first, then consumes them atomically.
     * Returns the number of parts that were successfully consumed.
     */
    public function consumeUnappliedJobParts(Job $job): int
    {
        return DB::transaction(function () use ($job) {

            $parts = $job->parts()
                ->with('product')
                ->where('approved', true)
                ->where('applied', false)
                ->get();

            if ($parts->isEmpty()) {
                return 0;
            }

            /*
             * Validate every part before consuming anything.
             */
            foreach ($parts as $part) {

                if (!$part->product) {
                    throw new \RuntimeException(
                        "Product not found for job part #{$part->id}."
                    );
                }

                $inventory = Inventory::where('product_id', $part->product_id)
                    ->where('branch_id', $job->branch_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw new \RuntimeException(
                        "No inventory record found for {$part->product->name}."
                    );
                }

                $available = $inventory->quantity
                    - $inventory->reserved_quantity;

                if ($available < $part->quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$part->product->name}. " .
                        "Available: {$available}, " .
                        "Required: {$part->quantity}."
                    );
                }
            }

            /*
             * All parts are available.
             * Now consume them.
             */
            $consumed = 0;

            foreach ($parts as $part) {

                $this->consume(
                    $part->product,
                    $job->branch_id,
                    (float) $part->quantity,
                    $job->id
                );

                $part->update([
                    'applied' => true,
                ]);

                $consumed++;
            }

            return $consumed;
        });
    }

    public function restore(
        Product $product,
        int $branchId,
        float $qty,
        int $jobId,
        string $referenceType = 'job'
    ): void {
        DB::transaction(function () use ($product, $branchId, $qty, $jobId, $referenceType) {
            $i = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->first();

            if ($i) {
                $i->quantity += $qty;
                $i->save();
            }

            InventoryMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'type' => InventoryMovementType::RESTOCK->value,
                'quantity' => $qty,
                'unit_cost' => $product->cost_price,
                'reference_type' => $referenceType,
                'reference_id' => $jobId,
                'user_id' => auth()->id(),
                'reason' => 'Restored from job',
            ]);
        });
    }

    public function checkAvailability(Product $product, int $branchId, float $qty): array
    {
        $i = Inventory::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->first();

        $available = $i ? $i->quantity - $i->reserved_quantity : 0;

        return [
            'available' => $available,
            'required' => $qty,
            'sufficient' => $available >= $qty,
            'shortage' => max(0, $qty - $available),
        ];
    }

    public function transferStock(
        int $fromBranchId,
        int $toBranchId,
        array $items,
        int $requestedBy
    ): StockTransfer {
        return DB::transaction(function () use ($fromBranchId, $toBranchId, $items, $requestedBy) {
            $transferNumber = 'ST-' . date('Y') . '-' . str_pad(
                StockTransfer::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'status' => 'pending',
                'requested_by' => $requestedBy,
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $availability = $this->checkAvailability($product, $fromBranchId, $item['quantity']);

                if (!$availability['sufficient']) {
                    abort(
                        422,
                        "Insufficient stock for {$product->name}. Available: {$availability['available']}, Required: {$item['quantity']}"
                    );
                }

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $product->cost_price,
                ]);
            }

            return $transfer;
        });
    }

    public function approveTransfer(StockTransfer $transfer, int $approvedBy): void
    {
        DB::transaction(function () use ($transfer, $approvedBy) {
            $transfer->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            foreach ($transfer->items as $item) {
                $this->adjust(
                    Product::find($item->product_id),
                    $transfer->from_branch_id,
                    -$item->quantity,
                    'Stock transfer to ' . $transfer->toBranch->name,
                    InventoryMovementType::TRANSFER->value
                );
            }
        });
    }

    public function receiveTransfer(StockTransfer $transfer, int $receivedBy): void
    {
        DB::transaction(function () use ($transfer, $receivedBy) {
            $transfer->update([
                'status' => 'received',
                'received_by' => $receivedBy,
                'received_at' => now(),
            ]);

            foreach ($transfer->items as $item) {
                $this->adjust(
                    Product::find($item->product_id),
                    $transfer->to_branch_id,
                    $item->quantity,
                    'Stock transfer from ' . $transfer->fromBranch->name,
                    InventoryMovementType::TRANSFER->value
                );
            }
        });
    }

    public function recordEmergencyPurchase(
        int $jobId,
        int $productId,
        float $qty,
        float $cost,
        string $supplierName,
        string $reason
    ): EmergencyPurchase {
        return DB::transaction(function () use ($jobId, $productId, $qty, $cost, $supplierName, $reason) {
            $purchase = EmergencyPurchase::create([
                'job_id' => $jobId,
                'product_id' => $productId,
                'supplier_name' => $supplierName,
                'quantity' => $qty,
                'cost' => $cost,
                'purchased_by' => auth()->id(),
                'reason' => $reason,
            ]);

            return $purchase;
        });
    }

    public function recordCustomerSuppliedPart(
        int $jobId,
        int $productId,
        float $qty,
        string $partNumber,
        string $condition
    ): CustomerSuppliedPart {
        return CustomerSuppliedPart::create([
            'job_id' => $jobId,
            'product_id' => $productId,
            'quantity' => $qty,
            'part_number' => $partNumber,
            'condition' => $condition,
        ]);
    }

    public function getLowStockItems(int $branchId): array
    {
        return Inventory::with('product')
            ->where('branch_id', $branchId)
            ->whereColumn('quantity', '<=', 'products.minimum_stock')
            ->get()
            ->map(function ($i) {
                return [
                    'product' => $i->product,
                    'current' => $i->quantity,
                    'minimum' => $i->product->minimum_stock,
                    'shortage' => $i->product->minimum_stock - $i->quantity,
                ];
            })
            ->toArray();
    }

    public function getOutOfStockItems(int $branchId): array
    {
        return Inventory::with('product')
            ->where('branch_id', $branchId)
            ->where('quantity', 0)
            ->get()
            ->map(function ($i) {
                return [
                    'product' => $i->product,
                ];
            })
            ->toArray();
    }

    /**
     * Create a stock adjustment (set quantity to a new absolute value)
     */
    public function createStockAdjustment(
        Product $product,
        int $branchId,
        int $businessId,
        float $newQuantity,
        string $reason,
        ?string $notes = null
    ): StockAdjustment {
        return DB::transaction(function () use (
            $product,
            $branchId,
            $businessId,
            $newQuantity,
            $reason,
            $notes
        ) {
            if ($newQuantity < 0) {
                abort(422, 'Stock quantity cannot be negative.');
            }

            $inventory = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = Inventory::create([
                    'product_id' => $product->id,
                    'branch_id' => $branchId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);

                $inventory = Inventory::where('id', $inventory->id)
                    ->lockForUpdate()
                    ->first();
            }

            $beforeQuantity = (float) $inventory->quantity;
            $reservedQuantity = (float) ($inventory->reserved_quantity ?? 0);

            if ($newQuantity < $reservedQuantity) {
                abort(
                    422,
                    "New stock quantity cannot be less than reserved quantity ({$reservedQuantity})."
                );
            }

            $difference = $newQuantity - $beforeQuantity;

            if (abs($difference) < 0.0005) {
                abort(422, 'There is no stock difference to adjust.');
            }

            $inventory->quantity = $newQuantity;
            $inventory->save();

            $adjustment = StockAdjustment::create([
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'product_id' => $product->id,
                'before_quantity' => $beforeQuantity,
                'new_quantity' => $newQuantity,
                'difference' => $difference,
                'reason' => $reason,
                'notes' => $notes,
                'adjusted_by' => auth()->id(),
            ]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'type' => InventoryMovementType::ADJUSTMENT->value,
                'quantity' => $difference,
                'unit_cost' => $product->cost_price,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'user_id' => auth()->id(),
                'reason' => $reason,
                'notes' => $notes,
            ]);

            return $adjustment;
        });
    }

    /**
     * Reverse a previous stock adjustment
     */
    public function reverseStockAdjustment(
        StockAdjustment $adjustment,
        string $reason = 'Stock adjustment reversed'
    ): void {
        DB::transaction(function () use ($adjustment, $reason) {
            if ($adjustment->reversed_at !== null) {
                abort(422, 'This stock adjustment has already been reversed.');
            }

            $inventory = Inventory::where('product_id', $adjustment->product_id)
                ->where('branch_id', $adjustment->branch_id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                abort(422, 'Inventory record not found.');
            }

            $currentQuantity = (float) $inventory->quantity;
            $reverseQuantity = -(float) $adjustment->difference;
            $newQuantity = $currentQuantity + $reverseQuantity;

            if ($newQuantity < 0) {
                abort(422, 'Reversal would result in negative inventory.');
            }

            $reservedQuantity = (float) ($inventory->reserved_quantity ?? 0);

            if ($newQuantity < $reservedQuantity) {
                abort(
                    422,
                    "Reversal would reduce stock below reserved quantity ({$reservedQuantity})."
                );
            }

            $inventory->quantity = $newQuantity;
            $inventory->save();

            InventoryMovement::create([
                'product_id' => $adjustment->product_id,
                'branch_id' => $adjustment->branch_id,
                'type' => InventoryMovementType::ADJUSTMENT_REVERSAL->value,
                'quantity' => $reverseQuantity,
                'unit_cost' => $adjustment->product->cost_price,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'user_id' => auth()->id(),
                'reason' => $reason,
                'notes' => 'Reversal of stock adjustment #' . $adjustment->id,
            ]);

            $adjustment->update([
                'reversed_by' => auth()->id(),
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);
        });
    }
}
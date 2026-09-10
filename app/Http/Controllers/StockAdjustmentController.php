<?php

namespace App\Http\Controllers;

use App\Enums\StockAdjustmentReason;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private readonly AuditService $auditService
    ) {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');

        $query = StockAdjustment::with([
            'product',
            'branch',
            'adjustedBy',
            'reversedBy',
        ])
            ->where('business_id', $user->business_id)
            ->orderByDesc('created_at');

        if (!$user->hasPermissionTo('stock_adjustments.access')) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $adjustments = $query
            ->paginate(20)
            ->withQueryString();

        $products = Product::where('business_id', $user->business_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $reasons = StockAdjustmentReason::cases();

        return view(
            'stock-adjustments.index',
            compact(
                'adjustments',
                'products',
                'reasons',
                'search'
            )
        );
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'new_quantity' => [
                'required',
                'numeric',
                'min:0',
            ],

            'reason' => [
                'required',
                'string',
                'in:' . implode(',', array_column(
                    StockAdjustmentReason::cases(),
                    'value'
                )),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->where('business_id', $user->business_id)
            ->first();

        if (!$product) {
            abort(403, 'Invalid product.');
        }

        // Get current stock quantity for audit logging
        $inventory = Inventory::where('product_id', $product->id)
            ->where('branch_id', $user->branch_id ?? null)
            ->first();
        $oldQuantity = $inventory ? (float) $inventory->quantity : 0;

        $adjustment = $this->inventoryService->createStockAdjustment(
            $product,
            $user->branch_id ?? null,
            $user->business_id,
            (float) $validated['new_quantity'],
            $validated['reason'],
            $validated['notes'] ?? null
        );

        $this->auditService->log('stock_adjustment_created', 'StockAdjustment', $adjustment->id, null, [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'branch_id' => $user->branch_id,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $validated['new_quantity'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('stock-adjustments.index')
            ->with(
                'success',
                "Stock adjustment #{$adjustment->id} created successfully."
            );
    }

    public function reverse(
        StockAdjustment $stockAdjustment,
        Request $request
    ) {
        $user = auth()->user();

        if ($stockAdjustment->business_id !== $user->business_id) {
            abort(403, 'Unauthorized.');
        }

        if (!$user->hasPermissionTo('stock_adjustments.reverse') &&
            $stockAdjustment->branch_id !== null &&
            $stockAdjustment->branch_id !== $user->branch_id
        ) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'reversal_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $originalAdjustmentData = $stockAdjustment->toArray();

        $this->inventoryService->reverseStockAdjustment(
            $stockAdjustment,
            $validated['reversal_reason']
        );

        $this->auditService->log('stock_adjustment_reversed', 'StockAdjustment', $stockAdjustment->id, $originalAdjustmentData, [
            'reversal_reason' => $validated['reversal_reason'],
            'reversed_by' => $user->id,
        ]);

        return redirect()
            ->route('stock-adjustments.index')
            ->with(
                'success',
                "Stock adjustment #{$stockAdjustment->id} reversed successfully."
            );
    }
}
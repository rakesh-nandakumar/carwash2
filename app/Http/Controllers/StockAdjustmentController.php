<?php

namespace App\Http\Controllers;

use App\Enums\StockAdjustmentReason;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
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
            ->where('tenant_id', $user->tenant_id)
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

        $products = Product::where('tenant_id', $user->tenant_id)
            ->where('business_id', $user->business_id)
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
            ->where('tenant_id', $user->tenant_id)
            ->where('business_id', $user->business_id)
            ->first();

        if (!$product) {
            abort(403, 'Invalid product.');
        }

        $adjustment = $this->inventoryService->createStockAdjustment(
            $product,
            $user->branch_id ?? null,
            $user->business_id,
            (float) $validated['new_quantity'],
            $validated['reason'],
            $validated['notes'] ?? null
        );

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

        $this->inventoryService->reverseStockAdjustment(
            $stockAdjustment,
            $validated['reversal_reason']
        );

        return redirect()
            ->route('stock-adjustments.index')
            ->with(
                'success',
                "Stock adjustment #{$stockAdjustment->id} reversed successfully."
            );
    }
}
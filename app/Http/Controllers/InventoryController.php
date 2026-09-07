<?php

namespace App\Http\Controllers;

use App\Models\{Product, Inventory, InventoryMovement};
use App\Services\InventoryService;
use App\Enums\InventoryMovementType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $service)
    {
    }

    public function index()
    {
        $user = auth()->user();

        /*
         * Load active products for this business.
         *
         * If the logged-in user has a branch:
         *     show stock for that branch.
         *
         * If branch_id is NULL:
         *     show total stock across all branches.
         */
        $items = Inventory::with('product')
            ->whereHas('product', function ($query) use ($user) {
                $query->where('business_id', $user->business_id)
                    ->where('active', true);
            })
            ->when(
                $user->branch_id !== null,
                function ($query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                }
            )
            ->paginate(20)
            ->withQueryString();

        /*
         * Load all inventory rows for the low-stock calculation.
         */
        $allItems = Inventory::with('product')
            ->whereHas('product', function ($query) use ($user) {
                $query->where('business_id', $user->business_id)
                    ->where('active', true);
            })
            ->when(
                $user->branch_id !== null,
                function ($query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                }
            )
            ->get();

        /*
         * Group inventory by product so that when branch_id is NULL
         * stock from all branches is combined.
         */
        $stockByProduct = $allItems
            ->groupBy('product_id')
            ->map(function ($inventoryRows) {
                return (float) $inventoryRows->sum('quantity');
            });

        /*
         * Low-stock products.
         */
        $lowStockItems = $allItems
            ->groupBy('product_id')
            ->map(function ($inventoryRows) use ($stockByProduct) {

                $product = $inventoryRows->first()->product;

                if (!$product) {
                    return null;
                }

                $currentStock = $stockByProduct->get(
                    $product->id,
                    0
                );

                if ($currentStock <= (float) $product->minimum_stock) {
                    return [
                        'product' => $product,
                        'current' => $currentStock,
                        'minimum' => (float) $product->minimum_stock,
                        'shortage' => max(
                            0,
                            (float) $product->minimum_stock - $currentStock
                        ),
                    ];
                }

                return null;
            })
            ->filter()
            ->values();

        return view(
            'inventory.index',
            compact(
                'items',
                'allItems',
                'lowStockItems',
                'stockByProduct'
            )
        );
    }

    public function create()
    {
        return view('inventory.create', [
            'mainCategories' => \App\Models\Category::where('business_id', auth()->user()->business_id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(),
            'subcategories' => \App\Models\Category::where('business_id', auth()->user()->business_id)
                ->whereNotNull('parent_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    // ... rest of the methods remain the same
}
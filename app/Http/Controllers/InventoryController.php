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
         * Load active products for this tenant.
         *
         * If the logged-in user has a branch:
         *     show stock for that branch.
         *
         * If branch_id is NULL:
         *     show total stock across all branches.
         */
        $items = Inventory::with('product')
            ->where('tenant_id', $user->tenant_id)
            ->whereHas('product', function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id)
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
            ->where('tenant_id', $user->tenant_id)
            ->whereHas('product', function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id)
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

    public function getStatus(Request $request)
    {
        $user = auth()->user();

        /*
         * Load all inventory rows for real-time status calculation.
         */
        $allItems = Inventory::with('product')
            ->where('tenant_id', $user->tenant_id)
            ->whereHas('product', function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id)
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
         * Calculate current inventory status for each product
         */
        $inventoryStatus = $allItems
            ->groupBy('product_id')
            ->map(function ($inventoryRows) use ($stockByProduct) {
                $product = $inventoryRows->first()->product;
                
                if (!$product) {
                    return null;
                }

                $currentStock = $stockByProduct->get($product->id, 0);
                $minimumStock = (float) $product->minimum_stock;
                $reservedQuantity = (float) $inventoryRows->sum('reserved_quantity');
                $available = max(0, $currentStock - $reservedQuantity);

                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'brand' => $product->brand,
                    'current_stock' => $currentStock,
                    'reserved_quantity' => $reservedQuantity,
                    'available' => $available,
                    'minimum_stock' => $minimumStock,
                    'selling_price' => (float) $product->selling_price,
                    'is_low_stock' => $available <= $minimumStock,
                    'is_out_of_stock' => $available == 0,
                ];
            })
            ->filter()
            ->values();

        /*
         * Calculate low stock items count
         */
        $lowStockCount = $inventoryStatus->filter(function ($item) {
            return $item['is_low_stock'];
        })->count();

        return response()->json([
            'inventory_status' => $inventoryStatus,
            'low_stock_count' => $lowStockCount,
            'total_products' => $inventoryStatus->count(),
        ]);
    }

    public function create()
    {
        return view('inventory.create', [
            'mainCategories' => \App\Models\Category::where('tenant_id', auth()->user()->tenant_id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(),
            'subcategories' => \App\Models\Category::where('tenant_id', auth()->user()->tenant_id)
                ->whereNotNull('parent_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,NULL,id,tenant_id,' . auth()->user()->tenant_id,
            'barcode' => 'nullable|string|max:100',
            'main_category_id' => 'nullable|exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'opening_stock' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'] ?? null,
                'category_id' => $validated['category_id'],
                'brand' => $validated['brand'],
                'part_number' => $validated['part_number'] ?? null,
                'selling_price' => $validated['selling_price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'minimum_stock' => $validated['minimum_stock'],
                'active' => true,
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $product->update(['image' => $imagePath]);
            }

            // Create inventory record
            Inventory::create([
                'product_id' => $product->id,
                'branch_id' => auth()->user()->branch_id ?? null,
                'quantity' => $validated['opening_stock'],
                'reserved_quantity' => 0,
                'tenant_id' => auth()->user()->tenant_id,
            ]);

            // Create initial inventory movement
            if ($validated['opening_stock'] > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => auth()->user()->branch_id ?? null,
                    'type' => InventoryMovementType::RESTOCK,
                    'quantity' => $validated['opening_stock'],
                    'reference_type' => 'product_creation',
                    'reference_id' => $product->id,
                    'user_id' => auth()->id(),
                    'notes' => 'Initial stock from product creation',
                    'tenant_id' => auth()->user()->tenant_id,
                ]);
            }
        });

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        
        $product = Product::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        return view('inventory.edit', [
            'product' => $product,
            'mainCategories' => \App\Models\Category::where('tenant_id', $user->tenant_id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(),
            'subcategories' => \App\Models\Category::where('tenant_id', $user->tenant_id)
                ->whereNotNull('parent_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        $product = Product::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $id . ',id,tenant_id,' . $user->tenant_id,
            'barcode' => 'nullable|string|max:100',
            'main_category_id' => 'nullable|exists:categories,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request, $product) {
            $product->update([
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'] ?? null,
                'category_id' => $validated['category_id'],
                'brand' => $validated['brand'],
                'part_number' => $validated['part_number'] ?? null,
                'selling_price' => $validated['selling_price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'minimum_stock' => $validated['minimum_stock'],
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $product->update(['image' => $imagePath]);
            }
        });

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        
        $product = Product::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        DB::transaction(function () use ($product) {
            // Delete related inventory records
            Inventory::where('product_id', $product->id)->delete();
            
            // Delete related inventory movements
            InventoryMovement::where('product_id', $product->id)->delete();
            
            // Delete the product
            $product->delete();
        });

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function stock($id, Request $request)
    {
        $user = auth()->user();
        $branchId = $request->query('branch_id');

        $product = Product::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        $inventory = Inventory::where('tenant_id', $user->tenant_id)
            ->where('product_id', $product->id)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when(!$branchId && $user->branch_id, function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->first();

        $currentStock = $inventory ? (float) $inventory->quantity : 0;

        return response()->json([
            'current_stock' => $currentStock,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
        ]);
    }

    // ... rest of the methods remain the same
}
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
        $branch = auth()->user()->branch_id;

        $items = Inventory::with('product')
            ->where('branch_id', $branch)
            ->whereHas('product', function ($query) {
                $query->where('active', true);
            })
            ->paginate(20);

        $allItems = Inventory::with('product')
            ->where('branch_id', $branch)
            ->whereHas('product', function ($query) {
                $query->where('active', true);
            })
            ->get();

        $lowStockItems = $allItems->filter(function ($item) {
            $available = max(0, $item->quantity - ($item->reserved_quantity ?? 0));
            return $available <= $item->product->minimum_stock;
        });

        return view('inventory.index', compact('items', 'lowStockItems'));
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

    public function store(Request $r)
    {
        $validated = $r->validate([
            'name' => [
                'required',
                'unique:products,name,NULL,id,active,1',
            ],
            'sku'           => 'nullable',
            'barcode'       => 'nullable|unique:products,barcode',
            'category_id'   => 'required|exists:categories,id',
            'brand'         => 'nullable',
            'part_number'   => 'nullable',
            'cost_price'    => 'nullable|numeric',
            'selling_price' => 'required|numeric',
            'minimum_stock' => 'required|integer',
            'image'         => 'nullable|image|max:2048',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = 'PRD-' . date('Y') . '-' . str_pad(
                (string) (Product::max('id') + 1),
                6,
                '0',
                STR_PAD_LEFT
            );
        }

        $validated['business_id'] = auth()->user()->business_id;

        if ($r->hasFile('image')) {
            $validated['image'] = $r->file('image')->store('products', 'public');
        }

        $p = Product::create($validated);

        $branchId = auth()->user()->branch_id;
        $openingStock = (float) $r->input('opening_stock', 0);

        if ($branchId !== null && $openingStock != 0) {
            $this->service->adjust(
                $p,
                (int) $branchId,
                $openingStock,
                'Opening stock',
                InventoryMovementType::PURCHASE->value
            );
        }

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('inventory.edit', [
            'product'        => $product,
            'mainCategories' => \App\Models\Category::where('business_id', auth()->user()->business_id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(),
            'subcategories'  => \App\Models\Category::where('business_id', auth()->user()->business_id)
                ->whereNotNull('parent_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $r, Product $product)
    {
        $validated = $r->validate([
            'name'          => 'required',
            'category_id'   => 'required|exists:categories,id',
            'brand'         => 'nullable',
            'part_number'   => 'nullable',
            'cost_price'    => 'nullable|numeric',
            'selling_price' => 'required|numeric',
            'minimum_stock' => 'required|integer',
            'image'         => 'nullable|image|max:2048',
        ]);

        if ($r->hasFile('image')) {
            $validated['image'] = $r->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        try {
            $product->update([
                'active' => false,
            ]);

            return redirect()
                ->route('inventory.index')
                ->with('success', 'Product deleted.');
        } catch (\Exception $e) {
            return back()->with(
                'error',
                'Cannot delete product: ' . $e->getMessage()
            );
        }
    }

    public function adjust(Request $r, Product $product)
    {
        $reason = $r->input('reason') ?: 'Manual adjustment';

        $quantity = (float) $r->validate([
            'quantity' => 'required|numeric|gt:0'
        ])['quantity'];

        $this->service->adjust(
            $product,
            auth()->user()->branch_id,
            $quantity,
            $reason
        );

        return back()->with('success', 'Stock adjusted.');
    }

    /**
     * API endpoint used by the Stock Adjustment UI
     * GET /inventory/{product}/stock
     */
    public function stock(Request $request, Product $product)
    {
        $user = auth()->user();

        if ($product->business_id !== $user->business_id) {
            abort(403, 'Unauthorized.');
        }

        $branchId = (int) (
            $request->input('branch_id', $user->branch_id)
        );

        if (!$user->hasPermissionTo('inventory.access') && $branchId !== $user->branch_id) {
            abort(403, 'Unauthorized.');
        }

        $inventory = Inventory::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->first();

        return response()->json([
            'quantity' => $inventory
                ? (float) $inventory->quantity
                : 0,
            'reserved_quantity' => $inventory
                ? (float) $inventory->reserved_quantity
                : 0,
            'available_quantity' => $inventory
                ? max(
                    0,
                    (float) $inventory->quantity - (float) $inventory->reserved_quantity
                )
                : 0,
        ]);
    }
}
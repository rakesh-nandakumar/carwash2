<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Inventory;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function __construct(
        private InventoryService $inventory
    ) {}

    /**
     * Show POS page with product catalog
     */
    public function index()
    {
        // Load all products with inventory and category
        $products = Product::with(['category', 'inventory'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $stock = $product->inventory->sum('quantity') ?? 0;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'unit_price' => (float) $product->selling_price,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category?->name,
                    'stock' => $stock,
                    'image_url' => $product->image_url,
                ];
            });

        // Load categories for filtering
        $categories = Category::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Load customers for selection
        $customers = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone']);

        return view('pos', compact('products', 'categories', 'customers'));
    }

    /**
     * Search customer by name or phone
     */
    public function searchCustomer(Request $request)
    {
        $query = $request->get('q');

        $customers = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($q) use ($query) {
                $q->where('full_name', 'like', '%' . $query . '%')
                  ->orWhere('phone', 'like', '%' . $query . '%');
            })
            ->orderBy('full_name')
            ->limit(10)
            ->get(['id', 'full_name', 'phone']);

        return response()->json($customers);
    }

    /**
     * Lookup product by barcode or SKU
     */
    public function lookupProduct(Request $request)
    {
        $code = $request->get('code');

        $product = Product::with(['category', 'inventory'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('sku', $code);
            })
            ->first();

        if (!$product) {
            return response()->json(['product' => null]);
        }

        $stock = $product->inventory->sum('quantity') ?? 0;

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'unit_price' => (float) $product->selling_price,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'stock' => $stock,
                'image_url' => $product->image_url,
            ]
        ]);
    }

    /**
     * Create invoice from POS cart
     */
    public function createInvoice(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Create invoice (direct POS sale, no job, no vehicle)
            $invoice = Invoice::create([
                'tenant_id' => auth()->user()->tenant_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $request->customer_id,
                'vehicle_id' => null,
                'date' => now(),
                'subtotal' => $subtotal,
                'tax' => 0, // TODO: Add tax calculation if needed
                'discount' => 0,
                'total' => $subtotal,
                'paid' => 0,
                'balance' => $subtotal,
                'status' => 'pending',
                'type' => 'pos_sale', // Mark as POS sale
                'notes' => 'Direct POS sale',
            ]);

            // Create invoice items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $lineTotal = $item['quantity'] * $item['unit_price'];

                InvoiceItem::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'invoice_id' => $invoice->id,
                    'item_type' => 'product',
                    'item_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax' => 0,
                    'discount' => 0,
                    'line_total' => $lineTotal,
                ]);

                // Deduct stock
                $this->inventory->deductStock(
                    $product->id,
                    $item['quantity'],
                    'POS Sale',
                    $invoice->id
                );
            }

            return response()->json([
                'ok' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
            ]);
        });
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'POS-';
        $date = now()->format('Ymd');
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . '-' . $newNumber;
    }
}

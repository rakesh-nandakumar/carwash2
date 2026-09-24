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
use Illuminate\Support\Facades\Validator;

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

        // Load main categories (no parent) with their subcategories
        $categories = Category::where('tenant_id', auth()->user()->tenant_id)
            ->whereNull('parent_id')
            ->with('children')
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
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|exists:customers,id',
                'discount_type' => 'nullable|in:none,amount,percentage',
                'discount_value' => 'nullable|numeric|min:0',
                'discount_apply_to' => 'nullable|in:total,individual',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.individual_discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            return DB::transaction(function () use ($request) {
                // Calculate totals
                $subtotal = 0;
                foreach ($request->items as $item) {
                    $subtotal += $item['quantity'] * $item['unit_price'];
                }

                // Calculate discount based on type
                $totalDiscount = 0;
                $discountType = $request->discount_type ?? 'none';
                $discountValue = $request->discount_value ?? 0;
                $discountApplyTo = $request->discount_apply_to ?? 'total';

                if ($discountType === 'amount' && $discountApplyTo === 'total') {
                    $totalDiscount = min($discountValue, $subtotal);
                } elseif ($discountType === 'percentage' && $discountApplyTo === 'total') {
                    $totalDiscount = $subtotal * ($discountValue / 100);
                } elseif ($discountApplyTo === 'individual') {
                    foreach ($request->items as $item) {
                        $itemSubtotal = $item['quantity'] * $item['unit_price'];
                        $itemDiscount = $item['individual_discount'] ?? 0;
                        if ($discountType === 'percentage') {
                            $totalDiscount += $itemSubtotal * ($itemDiscount / 100);
                        } else {
                            $totalDiscount += $itemDiscount;
                        }
                    }
                }

                $total = $subtotal - $totalDiscount;

                // Handle walk-in customer
                $customerId = $request->customer_id;
                if (!$customerId) {
                    // Find or create walk-in customer
                    $walkinCustomer = Customer::where('tenant_id', auth()->user()->tenant_id)
                        ->where('full_name', 'Walk-in Customer')
                        ->first();

                    if (!$walkinCustomer) {
                        $walkinCustomer = Customer::create([
                            'tenant_id' => auth()->user()->tenant_id,
                            'business_id' => auth()->user()->business_id,
                            'full_name' => 'Walk-in Customer',
                            'phone' => '0000000000',
                            'customer_code' => 'WALKIN',
                        ]);
                    }
                    $customerId = $walkinCustomer->id;
                }

                // Create invoice (direct POS sale, no job)
                $invoice = Invoice::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'business_id' => auth()->user()->business_id,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'customer_id' => $customerId,
                    'job_id' => null, // POS sale has no job
                    'subtotal' => $subtotal,
                    'tax' => 0,
                    'discount' => $totalDiscount,
                    'total' => $total,
                    'paid' => 0,
                    'balance' => $total,
                    'status' => 'issued',
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

                    // Deduct stock using consume method
                    $this->inventory->consume(
                        $product,
                        null, // branch_id
                        $item['quantity'],
                        $invoice->id,
                        'POS Sale'
                    );
                }

                return response()->json([
                    'ok' => true,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total' => $invoice->total,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

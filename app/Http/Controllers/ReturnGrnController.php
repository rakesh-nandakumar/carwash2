<?php

namespace App\Http\Controllers;

use App\Models\ReturnGrn;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;

class ReturnGrnController extends Controller
{
    public function index()
    {
        $returnGrns = ReturnGrn::with(['supplier', 'items.product', 'status'])
            ->latest('created_at')
            ->get();

        // Add computed status fields to prevent JSON serialization
        $returnGrns->transform(function ($returnGrn) {
            if ($returnGrn->status) {
                $returnGrn->status_display = $returnGrn->status->value ?? 'Draft';
                $returnGrn->status_key = $returnGrn->status->key ?? 'draft';
            } else {
                $returnGrn->status_display = 'Draft';
                $returnGrn->status_key = 'draft';
            }
            unset($returnGrn->status);
            return $returnGrn;
        });

        return view('return_grns.index', compact('returnGrns'));
    }

    public function show(ReturnGrn $returnGrn)
    {
        $returnGrn->load(['items.product', 'supplier', 'status']);
        return view('return_grns.show', compact('returnGrn'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        // Get current stock for each product
        $inventory = app(\App\Services\InventoryService::class);
        $productStocks = [];
        foreach ($products as $product) {
            $productStocks[$product->id] = $inventory->getStock($product, null);
        }
        
        // Get supplier products mapping (products each supplier has provided) - only from confirmed GRNs
        $supplierProducts = [];
        $confirmedStatusId = \App\Models\Setting::where('group', 'grn_statuses')
            ->where('key', 'confirmed')
            ->first()?->id ?? 45;
        $grnItems = \App\Models\GoodsReceiptItem::with('goodsReceipt.supplier')
            ->whereHas('goodsReceipt', function($query) use ($confirmedStatusId) {
                $query->where('status_id', $confirmedStatusId);
            })
            ->get();
        foreach ($grnItems as $item) {
            $supplierId = $item->goodsReceipt->supplier_id;
            if (!isset($supplierProducts[$supplierId])) {
                $supplierProducts[$supplierId] = [];
            }
            if (!in_array($item->product_id, $supplierProducts[$supplierId])) {
                $supplierProducts[$supplierId][] = $item->product_id;
            }
        }
        
        // Get supplier references (GRN references for each supplier) - only confirmed GRNs
        $supplierReferences = [];
        $grnItemsMap = []; // Map GRN number to its items with unit costs
        $confirmedStatusId = \App\Models\Setting::where('group', 'grn_statuses')
            ->where('key', 'confirmed')
            ->first()?->id ?? 45;
        $grns = \App\Models\GoodsReceipt::with(['supplier', 'items'])
            ->where('status_id', $confirmedStatusId)
            ->get();
        foreach ($grns as $grn) {
            $supplierId = $grn->supplier_id;
            if (!isset($supplierReferences[$supplierId])) {
                $supplierReferences[$supplierId] = [];
            }
            // Use GRN number as unique key to allow duplicate references
            $supplierReferences[$supplierId][] = [
                'reference' => $grn->reference,
                'grn_number' => $grn->grn_number,
                'address' => $grn->address,
                'notes' => $grn->notes,
            ];
            
            // Store items for this GRN with unit costs
            $grnItemsMap[$grn->grn_number] = [];
            foreach ($grn->items as $item) {
                $grnItemsMap[$grn->grn_number][] = [
                    'product_id' => $item->product_id,
                    'unit_cost' => $item->unit_cost,
                ];
            }
        }
        
        return view('return_grns.create', compact('suppliers', 'products', 'productStocks', 'supplierProducts', 'supplierReferences', 'grnItemsMap'));
    }
}

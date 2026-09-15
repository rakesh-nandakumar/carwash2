<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Branch;
use App\Models\User;
use App\Models\Supplier;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SampleGRNSupplierDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get base data without tenant context
        app(\App\Services\CurrentContext::class)->runWithoutTenant(function () {
            $business = Business::first();
            $user = User::first();

            if (!$business || !$user) {
                echo "Missing required base data (business or user)\n";
                return;
            }

            // Run with tenant context for seeder
            app(\App\Services\CurrentContext::class)->runForTenant($business->tenant_id, function () use ($business, $user) {
                // Create sample suppliers
                $supplier1 = Supplier::create([
                    'name' => 'Auto Parts Ltd',
                    'business_name' => 'Auto Parts Limited',
                    'phone' => '+94 77 123 4567',
                    'email' => 'info@autoparts.lk',
                    'address' => '123 Main Street, Colombo',
                    'credit_limit' => 50000.00,
                    'outstanding_balance' => 0,
                    'business_id' => $business->id,
                    'created_by' => $user->id,
                ]);

                $supplier2 = Supplier::create([
                    'name' => 'Motor Spares Warehouse',
                    'business_name' => 'Motor Spares Warehouse (Pvt) Ltd',
                    'phone' => '+94 71 987 6543',
                    'email' => 'sales@motorspares.lk',
                    'address' => '456 Industrial Area, Kandy',
                    'credit_limit' => 75000.00,
                    'outstanding_balance' => 25000.00,
                    'business_id' => $business->id,
                    'created_by' => $user->id,
                ]);

                echo "Created suppliers: {$supplier1->name}, {$supplier2->name}\n";

                // Use existing products or skip GRN creation if no products exist
                $products = Product::take(2)->get();
                if ($products->count() < 2) {
                    echo "Not enough products to create sample GRNs. Suppliers created successfully.\n";
                    return;
                }

                $product1 = $products[0];
                $product2 = $products[1];

                // Create sample GRNs
                $draftStatus = \App\Models\Setting::where('group', 'grn_statuses')
                    ->where('key', 'draft')
                    ->first();

                $grn1 = GoodsReceipt::create([
                    'grn_number' => 'GRN-2026-000001',
                    'supplier_id' => $supplier1->id,
                    'reference' => 'PO-2026-001',
                    'status_id' => $draftStatus ? $draftStatus->id : null,
                    'note' => 'Emergency stock delivery',
                    'received_by' => $user->id,
                    'received_at' => now(),
                    'business_id' => $business->id,
                ]);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn1->id,
                    'product_id' => $product1->id,
                    'quantity' => 50.0,
                    'unit_cost' => 1500.00,
                    'sale_price' => 2000.00,
                ]);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn1->id,
                    'product_id' => $product2->id,
                    'quantity' => 100.0,
                    'unit_cost' => 800.00,
                    'sale_price' => 1200.00,
                ]);

                $grn2 = GoodsReceipt::create([
                    'grn_number' => 'GRN-2026-000002',
                    'supplier_id' => $supplier2->id,
                    'reference' => 'PO-2026-002',
                    'status_id' => $draftStatus ? $draftStatus->id : null,
                    'note' => 'Regular order',
                    'received_by' => $user->id,
                    'received_at' => now()->subDays(2),
                    'business_id' => $business->id,
                ]);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn2->id,
                    'product_id' => $product1->id,
                    'quantity' => 25.0,
                    'unit_cost' => 1450.00,
                    'sale_price' => 1950.00,
                ]);

                echo "Created GRNs: {$grn1->grn_number}, {$grn2->grn_number}\n";
                echo "Sample data seeded successfully!\n";
            });
        });
    }
}

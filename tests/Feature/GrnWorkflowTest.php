<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\GrnService;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrnWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_grn_workflow_from_po_to_confirmation(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 0]);
        $product = Product::factory()->create();

        // Create Purchase Order
        $poService = app(PurchaseOrderService::class);
        $po = $poService->createPurchaseOrder([
            'business_id' => 1,
            'supplier_id' => $supplier->id,
            'expected_date' => now()->addDays(7)->toDateString(),
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'quantity' => 20.0,
            'unit_price' => 150.00,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => 3000.00,
            'received_quantity' => 0,
        ]);

        // Create GRN
        $grnService = app(GrnService::class);
        $grn = $grnService->create(
            header: [
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $po->id,
                'reference' => 'PO-TEST',
            ],
            lines: [
                [
                    'product_id' => $product->id,
                    'quantity' => 15.0,
                    'unit_cost' => 150.00,
                    'sale_price' => 200.00,
                ],
            ]
        );

        // Assert Draft State
        $this->assertTrue($grn->isDraft());
        $this->assertEquals(1, $grn->items()->count());
        $this->assertEquals(15.0, (float) $grn->items->first()->quantity);

        // Confirm GRN
        $confirmedGrn = $grnService->confirm($grn, 1);

        // Assert Confirmed State
        $this->assertTrue($confirmedGrn->isConfirmed());
        $this->assertNotNull($confirmedGrn->confirmed_at);

        // Assert Stock Added
        $inventory = Inventory::where('product_id', $product->id)
            ->first();
        $this->assertNotNull($inventory);
        $this->assertEquals(15.0, (float) $inventory->quantity);

        // Assert Supplier Debited
        $supplier->refresh();
        $this->assertEquals(2250.00, (float) $supplier->outstanding_balance); // 15 * 150

        // Assert PO Updated
        $po->refresh();
        $this->assertEquals('partially_received', $po->status);
        $this->assertEquals(15.0, (float) $po->items->first()->received_quantity);
    }

    public function test_grn_without_supplier_does_not_create_ledger_entry(): void
    {
        // Arrange
        $product = Product::factory()->create();

        $grnService = app(GrnService::class);
        $grn = $grnService->create(
            header: [
                'supplier_id' => null, // No supplier
            ],
            lines: [
                [
                    'product_id' => $product->id,
                    'quantity' => 10.0,
                    'unit_cost' => 150.00,
                    'sale_price' => 200.00,
                ],
            ]
        );

        // Act
        $confirmedGrn = $grnService->confirm($grn, 1);

        // Assert
        $this->assertTrue($confirmedGrn->isConfirmed());
        $this->assertDatabaseCount('supplier_ledgers', 0); // No ledger entry created
    }

    public function test_multiple_grns_for_same_supplier_accumulate_balance(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 0]);
        $product = Product::factory()->create();

        $grnService = app(GrnService::class);

        // First GRN
        $grn1 = $grnService->create(
            header: [
                'supplier_id' => $supplier->id,
            ],
            lines: [
                [
                    'product_id' => $product->id,
                    'quantity' => 10.0,
                    'unit_cost' => 100.00,
                    'sale_price' => 150.00,
                ],
            ]
        );
        $grnService->confirm($grn1, 1);

        // Second GRN
        $grn2 = $grnService->create(
            header: [
                'supplier_id' => $supplier->id,
            ],
            lines: [
                [
                    'product_id' => $product->id,
                    'quantity' => 5.0,
                    'unit_cost' => 200.00,
                    'sale_price' => 250.00,
                ],
            ]
        );
        $grnService->confirm($grn2, 1);

        // Assert
        $supplier->refresh();
        $this->assertEquals(2000.00, (float) $supplier->outstanding_balance); // (10*100) + (5*200)

        $this->assertDatabaseCount('supplier_ledgers', 2);
    }

    public function test_delete_confirmed_grn_reverses_po_status(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 0]);
        $product = Product::factory()->create();

        $poService = app(PurchaseOrderService::class);
        $po = $poService->createPurchaseOrder([
            'business_id' => 1,
            'supplier_id' => $supplier->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'quantity' => 10.0,
            'unit_price' => 150.00,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => 1500.00,
            'received_quantity' => 0,
        ]);

        $grnService = app(GrnService::class);
        $grn = $grnService->create(
            header: [
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $po->id,
            ],
            lines: [
                [
                    'product_id' => $product->id,
                    'quantity' => 10.0,
                    'unit_cost' => 150.00,
                    'sale_price' => 200.00,
                ],
            ]
        );

        $grnService->confirm($grn, 1);

        // Act - Delete the GRN
        $grnService->delete($grn, 1);

        // Assert
        $po->refresh();
        $this->assertEquals('ordered', $po->status); // Should revert to ordered status
        $this->assertEquals(0.0, (float) $po->items->first()->received_quantity);
    }

    public function test_grn_with_multiple_products(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 0]);
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $grnService = app(GrnService::class);
        $grn = $grnService->create(
            header: [
                'supplier_id' => $supplier->id,
            ],
            lines: [
                [
                    'product_id' => $product1->id,
                    'quantity' => 10.0,
                    'unit_cost' => 100.00,
                    'sale_price' => 150.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 5.0,
                    'unit_cost' => 200.00,
                    'sale_price' => 300.00,
                ],
            ]
        );

        // Act
        $confirmedGrn = $grnService->confirm($grn, 1);

        // Assert
        $this->assertEquals(2, $confirmedGrn->items()->count());

        $supplier->refresh();
        $this->assertEquals(2000.00, (float) $supplier->outstanding_balance); // (10*100) + (5*200)

        // Check both products have inventory
        $inventory1 = Inventory::where('product_id', $product1->id)
            ->first();
        $inventory2 = Inventory::where('product_id', $product2->id)
            ->first();

        $this->assertEquals(10.0, (float) $inventory1->quantity);
        $this->assertEquals(5.0, (float) $inventory2->quantity);
    }
}

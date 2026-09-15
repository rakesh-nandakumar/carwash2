<?php

namespace Tests\Unit;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\GrnService;
use App\Services\InventoryService;
use App\Services\SupplierService;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GrnServiceTest extends TestCase
{
    use RefreshDatabase;

    private GrnService $grnService;
    private InventoryService $inventoryService;
    private SupplierService $supplierService;
    private DocumentNumberService $documentNumberService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryService = app(InventoryService::class);
        $this->supplierService = app(SupplierService::class);
        $this->documentNumberService = app(DocumentNumberService::class);
        $this->grnService = new GrnService(
            $this->inventoryService,
            $this->supplierService,
            app(\App\Services\PurchaseOrderService::class),
            $this->documentNumberService,
        );
    }

    public function test_create_grn_creates_draft_with_items(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $header = [
            'supplier_id' => $supplier->id,
            'reference' => 'TEST-001',
            'note' => 'Test GRN',
        ];

        $lines = [
            [
                'product_id' => $product->id,
                'quantity' => 10.5,
                'unit_cost' => 150.00,
                'sale_price' => 200.00,
            ],
        ];

        // Act
        $grn = $this->grnService->create($header, $lines);

        // Assert
        $this->assertDatabaseHas('goods_receipts', [
            'grn_number' => $grn->grn_number,
            'supplier_id' => $supplier->id,
            'reference' => 'TEST-001',
        ]);

        $this->assertDatabaseHas('goods_receipt_items', [
            'goods_receipt_id' => $grn->id,
            'product_id' => $product->id,
            'quantity' => 10.5,
            'unit_cost' => 150.00,
            'sale_price' => 200.00,
        ]);

        $this->assertTrue($grn->isDraft());
        $this->assertEquals(1, $grn->items()->count());
    }

    public function test_confirm_grn_adds_stock_and_debits_supplier(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 0]);
        $product = Product::factory()->create();

        $grn = GoodsReceipt::factory()
            ->draft()
            ->create([
                'supplier_id' => $supplier->id,
            ]);

        GoodsReceiptItem::factory()->create([
            'goods_receipt_id' => $grn->id,
            'product_id' => $product->id,
            'quantity' => 10.0,
            'unit_cost' => 150.00,
            'sale_price' => 200.00,
        ]);

        // Act
        $confirmedGrn = $this->grnService->confirm($grn, 1);

        // Assert
        $this->assertTrue($confirmedGrn->isConfirmed());
        $this->assertNotNull($confirmedGrn->confirmed_at);

        // Check inventory was added
        $inventory = Inventory::where('product_id', $product->id)
            ->first();

        $this->assertNotNull($inventory);
        $this->assertEquals(10.0, (float) $inventory->quantity);

        // Check supplier was debited
        $supplier->refresh();
        $this->assertEquals(1500.00, (float) $supplier->outstanding_balance); // 10 * 150

        // Check ledger entry
        $this->assertDatabaseHas('supplier_ledgers', [
            'supplier_id' => $supplier->id,
            'amount' => 1500.00,
        ]);
    }

    public function test_confirm_grn_updates_product_sale_price(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['selling_price' => 100.00]);

        $grn = GoodsReceipt::factory()
            ->draft()
            ->create([
                'supplier_id' => $supplier->id,
            ]);

        GoodsReceiptItem::factory()->create([
            'goods_receipt_id' => $grn->id,
            'product_id' => $product->id,
            'quantity' => 5.0,
            'unit_cost' => 150.00,
            'sale_price' => 250.00,
        ]);

        // Act
        $this->grnService->confirm($grn, 1);

        // Assert
        $product->refresh();
        $this->assertEquals(250.00, (float) $product->selling_price);
    }

    public function test_delete_confirmed_grn_reverses_stock_and_credits_supplier(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 1500.00]);
        $product = Product::factory()->create();

        $grn = GoodsReceipt::factory()
            ->confirmed()
            ->create([
                'supplier_id' => $supplier->id,
            ]);

        GoodsReceiptItem::factory()->create([
            'goods_receipt_id' => $grn->id,
            'product_id' => $product->id,
            'quantity' => 10.0,
            'unit_cost' => 150.00,
            'sale_price' => 200.00,
        ]);

        // Ensure inventory exists
        Inventory::create([
            'product_id' => $product->id,
            'business_id' => 1,
            'quantity' => 10.0,
            'reserved_quantity' => 0,
        ]);

        // Act
        $this->grnService->delete($grn, 1);

        // Assert
        $grn->refresh();
        $this->assertTrue($grn->isDeleted());
        $this->assertNotNull($grn->deleted_at);

        // Check inventory was reversed
        $inventory = Inventory::where('product_id', $product->id)
            ->first();

        $this->assertEquals(0.0, (float) $inventory->quantity);

        // Check supplier was credited
        $supplier->refresh();
        $this->assertEquals(0.00, (float) $supplier->outstanding_balance);
    }

    public function test_delete_draft_grn_only_changes_status(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $grn = GoodsReceipt::factory()
            ->draft()
            ->create([
                'supplier_id' => $supplier->id,
            ]);

        GoodsReceiptItem::factory()->create([
            'goods_receipt_id' => $grn->id,
            'product_id' => $product->id,
            'quantity' => 10.0,
            'unit_cost' => 150.00,
            'sale_price' => 200.00,
        ]);

        // Act
        $this->grnService->delete($grn, 1);

        // Assert
        $grn->refresh();
        $this->assertTrue($grn->isDeleted());
        $this->assertNotNull($grn->deleted_at);

        // Inventory should not be affected
        $inventory = Inventory::where('product_id', $product->id)
            ->first();

        $this->assertNull($inventory);
    }

    public function test_confirm_only_draft_grns(): void
    {
        // Arrange
        $grn = GoodsReceipt::factory()->confirmed()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft GRNs can be confirmed');

        $this->grnService->confirm($grn, 1);
    }

    public function test_delete_already_deleted_grn_fails(): void
    {
        // Arrange
        $grn = GoodsReceipt::factory()->deleted()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GRN is already deleted');

        $this->grnService->delete($grn, 1);
    }
}

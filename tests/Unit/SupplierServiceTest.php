<?php

namespace Tests\Unit;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Services\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierServiceTest extends TestCase
{
    use RefreshDatabase;

    private SupplierService $supplierService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supplierService = app(SupplierService::class);
    }

    public function test_debit_increases_outstanding_balance(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 1000.00]);

        // Act
        $ledger = $this->supplierService->debit($supplier, 500.00, null, 'Test debit');

        // Assert
        $supplier->refresh();
        $this->assertEquals(1500.00, (float) $supplier->outstanding_balance);

        $this->assertDatabaseHas('supplier_ledgers', [
            'supplier_id' => $supplier->id,
            'amount' => 500.00,
            'balance_after' => 1500.00,
            'note' => 'Test debit',
        ]);
    }

    public function test_credit_decreases_outstanding_balance(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 1000.00]);

        // Act
        $ledger = $this->supplierService->credit($supplier, 500.00, null, 'Test credit');

        // Assert
        $supplier->refresh();
        $this->assertEquals(500.00, (float) $supplier->outstanding_balance);

        $this->assertDatabaseHas('supplier_ledgers', [
            'supplier_id' => $supplier->id,
            'amount' => 500.00,
            'balance_after' => 500.00,
            'note' => 'Test credit',
        ]);
    }

    public function test_credit_does_not_go_below_zero(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 200.00]);

        // Act
        $ledger = $this->supplierService->credit($supplier, 500.00, null, 'Test credit');

        // Assert
        $supplier->refresh();
        $this->assertEquals(0.00, (float) $supplier->outstanding_balance);
    }

    public function test_debit_requires_positive_amount(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create();

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Debit amount must be positive');

        $this->supplierService->debit($supplier, -100.00, null, 'Test');
    }

    public function test_credit_requires_positive_amount(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create();

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Credit amount must be positive');

        $this->supplierService->credit($supplier, -100.00, null, 'Test');
    }

    public function test_is_over_limit_returns_true_when_exceeded(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create([
            'credit_limit' => 5000.00,
            'outstanding_balance' => 4500.00,
        ]);

        // Act
        $isOverLimit = $this->supplierService->isOverLimit($supplier, 1000.00);

        // Assert
        $this->assertTrue($isOverLimit);
    }

    public function test_is_over_limit_returns_false_when_not_exceeded(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create([
            'credit_limit' => 5000.00,
            'outstanding_balance' => 3000.00,
        ]);

        // Act
        $isOverLimit = $this->supplierService->isOverLimit($supplier, 1000.00);

        // Assert
        $this->assertFalse($isOverLimit);
    }

    public function test_is_over_limit_returns_false_when_no_credit_limit(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create([
            'credit_limit' => 0,
            'outstanding_balance' => 10000.00,
        ]);

        // Act
        $isOverLimit = $this->supplierService->isOverLimit($supplier, 1000.00);

        // Assert
        $this->assertFalse($isOverLimit);
    }

    public function test_blacklist_sets_blacklisted_status(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['is_blacklisted' => false]);

        // Act
        $this->supplierService->blacklist($supplier, 'Test blacklist reason');

        // Assert
        $supplier->refresh();
        $this->assertTrue($supplier->is_blacklisted);
        $this->assertEquals('Test blacklist reason', $supplier->blacklisted_reason);
        $this->assertNotNull($supplier->blacklisted_at);
        $this->assertNotNull($supplier->blacklisted_by);
    }

    public function test_unblacklist_removes_blacklisted_status(): void
    {
        // Arrange
        $supplier = Supplier::factory()->blacklisted()->create();

        // Act
        $this->supplierService->unblacklist($supplier);

        // Assert
        $supplier->refresh();
        $this->assertFalse($supplier->is_blacklisted);
        $this->assertNull($supplier->blacklisted_reason);
        $this->assertNull($supplier->blacklisted_at);
        $this->assertNull($supplier->blacklisted_by);
    }

    public function test_ledger_entries_are_immutable(): void
    {
        // Arrange
        $supplier = Supplier::factory()->create(['outstanding_balance' => 0]);
        $ledger = $this->supplierService->debit($supplier, 100.00, null, 'Test');

        // Act & Assert
        $this->expectException(\Exception::class);
        $ledger->update(['amount' => 200.00]);
    }
}

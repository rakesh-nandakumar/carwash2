<?php

namespace Tests\Feature;

use App\Models\CashMovement;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Payment;
use App\Models\Till;
use App\Models\TillClosure;
use App\Models\Vehicle;
use App\Services\CashMovementService;
use App\Services\CurrentContext;
use Tests\TestCase;

class TillClosureTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenancy();
    }

    private function createTill(
        int $tenantId,
        float $openingBalance = 0
    ): Till {
        return app(CurrentContext::class)->runForTenant(
            $tenantId,
            function () use ($openingBalance): Till {
                return Till::create([
                    'name' => 'Main Till',
                    'code' => 'MAIN',
                    'description' => null,
                    'opening_balance' => $openingBalance,
                    'is_active' => true,
                ]);
            }
        );
    }

    public function test_open_shift_creates_till_closure(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                $closure = app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    notes: 'Opening shift for morning',
                    userId: $this->ownerA->id,
                );

                $this->assertDatabaseHas('till_closures', [
                    'id' => $closure->id,
                    'tenant_id' => $this->tenantA->id,
                    'till_id' => $till->id,
                    'user_id' => $this->ownerA->id,
                    'opening_balance' => 1500,
                    'expected_balance' => 1500,
                    'counted_balance' => 1500,
                    'discrepancy' => 0,
                    'notes' => 'Opening shift for morning',
                ]);

                $this->assertNotNull($closure->opened_at);
                $this->assertNull($closure->closed_at);
            }
        );
    }

    public function test_cannot_open_shift_when_previous_is_open(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                $this->expectException(\RuntimeException::class);
                $this->expectExceptionMessage('Cannot open new shift. Previous shift is still open.');

                app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );
            }
        );
    }

    public function test_close_shift_calculates_correct_summary(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        $closure = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                return app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till, $closure) {
                $movement1 = app(CashMovementService::class)->recordCashIn(
                    amount: 500,
                    reason: 'Owner funding',
                    userId: $this->ownerA->id,
                );

                $movement2 = app(CashMovementService::class)->recordCashOut(
                    amount: 200,
                    reason: 'Petty cash',
                    userId: $this->ownerA->id,
                );

                $movement3 = app(CashMovementService::class)->recordCashDrop(
                    amount: 300,
                    reason: 'Bank deposit',
                    userId: $this->ownerA->id,
                );

                $closedClosure = app(CashMovementService::class)->closeShift(
                    countedBalance: 1500,
                    notes: 'End of day',
                    userId: $this->ownerA->id,
                );

                $this->assertEquals(1500, $closedClosure->opening_balance);
                $this->assertEquals(1500, $closedClosure->expected_balance);
                $this->assertEquals(1500, $closedClosure->counted_balance);
                $this->assertEquals(0, $closedClosure->discrepancy);
                $this->assertEquals(0, $closedClosure->cash_sales);
                $this->assertEquals(0, $closedClosure->card_sales);
                $this->assertEquals(0, $closedClosure->mobile_money_sales);
                $this->assertEquals(0, $closedClosure->bank_transfer_sales);
                $this->assertEquals(0, $closedClosure->other_payment_sales);
                $this->assertEquals(0, $closedClosure->total_sales);
                $this->assertEquals(500, $closedClosure->cash_in);
                $this->assertEquals(200, $closedClosure->cash_out);
                $this->assertEquals(0, $closedClosure->cash_refunds);
                $this->assertEquals(300, $closedClosure->cash_drops);
                $this->assertNotNull($closedClosure->closed_at);
                $this->assertEquals('End of day', $closedClosure->notes);
            }
        );
    }

    public function test_close_shift_with_discrepancy(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                $closedClosure = app(CashMovementService::class)->closeShift(
                    countedBalance: 1450,
                    userId: $this->ownerA->id,
                );

                $this->assertEquals(-50, $closedClosure->discrepancy);
            }
        );
    }

    public function test_close_shift_with_cash_sales(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        $payment = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () {
                $businessId = $this->ownerA->business_id;
                $branchId = $this->ownerA->branch_id;

                $customer = Customer::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_code' => 'CUS-CASH-002',
                    'full_name' => 'Cash Sale Customer',
                    'phone' => '0770000002',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'CASH-002',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-CASH-002',
                    'status' => 'ready_for_payment',
                ]);

                $invoice = Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-CASH-002',
                    'status' => 'issued',
                    'subtotal' => 300,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 300,
                    'paid' => 0,
                    'balance' => 300,
                ]);

                return Payment::create([
                    'invoice_id' => $invoice->id,
                    'method' => 'cash',
                    'amount' => 300,
                    'reference' => null,
                    'received_by' => $this->ownerA->id,
                ]);
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till, $payment) {
                app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                app(CashMovementService::class)->recordSale(
                    amount: 300,
                    reference: $payment,
                    userId: $this->ownerA->id,
                );

                $closedClosure = app(CashMovementService::class)->closeShift(
                    countedBalance: 1800,
                    userId: $this->ownerA->id,
                );

                $this->assertEquals(300, $closedClosure->cash_sales);
                $this->assertEquals(300, $closedClosure->total_sales);
                $this->assertEquals(1800, $closedClosure->expected_balance);
                $this->assertEquals(1800, $closedClosure->counted_balance);
                $this->assertEquals(0, $closedClosure->discrepancy);
            }
        );
    }

    public function test_close_shift_tracks_multiple_payment_methods(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        $payments = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () {
                $businessId = $this->ownerA->business_id;
                $branchId = $this->ownerA->branch_id;

                $customer = Customer::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_code' => 'CUS-MULTI-001',
                    'full_name' => 'Multi Payment Customer',
                    'phone' => '0770000003',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'MULTI-001',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-MULTI-001',
                    'status' => 'ready_for_payment',
                ]);

                $invoice = Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-MULTI-001',
                    'status' => 'issued',
                    'subtotal' => 1000,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 1000,
                    'paid' => 0,
                    'balance' => 1000,
                ]);

                $payment1 = Payment::create([
                    'invoice_id' => $invoice->id,
                    'method' => 'cash',
                    'amount' => 300,
                    'reference' => null,
                    'received_by' => $this->ownerA->id,
                ]);

                $payment2 = Payment::create([
                    'invoice_id' => $invoice->id,
                    'method' => 'card',
                    'amount' => 400,
                    'reference' => 'CARD123',
                    'received_by' => $this->ownerA->id,
                ]);

                $payment3 = Payment::create([
                    'invoice_id' => $invoice->id,
                    'method' => 'upi',
                    'amount' => 300,
                    'reference' => 'UPI123',
                    'received_by' => $this->ownerA->id,
                ]);

                return [$payment1, $payment2, $payment3];
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till, $payments) {
                app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                // Only record cash movement for cash payment
                app(CashMovementService::class)->recordSale(
                    amount: 300,
                    reference: $payments[0],
                    userId: $this->ownerA->id,
                );

                $closedClosure = app(CashMovementService::class)->closeShift(
                    countedBalance: 1800,
                    userId: $this->ownerA->id,
                );

                $this->assertEquals(300, $closedClosure->cash_sales);
                $this->assertEquals(400, $closedClosure->card_sales);
                $this->assertEquals(300, $closedClosure->mobile_money_sales);
                $this->assertEquals(0, $closedClosure->bank_transfer_sales);
                $this->assertEquals(0, $closedClosure->other_payment_sales);
                $this->assertEquals(1000, $closedClosure->total_sales);
                $this->assertEquals(1800, $closedClosure->expected_balance);
                $this->assertEquals(1800, $closedClosure->counted_balance);
                $this->assertEquals(0, $closedClosure->discrepancy);
            }
        );
    }

    public function test_cannot_close_shift_without_opening(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                $this->expectException(\RuntimeException::class);
                $this->expectExceptionMessage('No open shift found. Please open a shift first.');

                app(CashMovementService::class)->closeShift(
                    countedBalance: 1500,
                    userId: $this->ownerA->id,
                );
            }
        );
    }

    public function test_cannot_close_already_closed_shift(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                app(CashMovementService::class)->closeShift(
                    countedBalance: 1500,
                    userId: $this->ownerA->id,
                );

                $this->expectException(\RuntimeException::class);
                $this->expectExceptionMessage('Shift is already closed.');

                app(CashMovementService::class)->closeShift(
                    countedBalance: 1500,
                    userId: $this->ownerA->id,
                );
            }
        );
    }

    public function test_get_cash_movement_summary(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                $closure = app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                app(CashMovementService::class)->recordCashIn(
                    amount: 500,
                    reason: 'Funding',
                    userId: $this->ownerA->id,
                );

                app(CashMovementService::class)->recordCashOut(
                    amount: 200,
                    reason: 'Expense',
                    userId: $this->ownerA->id,
                );

                $summary = app(CashMovementService::class)->getCashMovementSummary(
                    $till,
                    $closure->opened_at
                );

                $this->assertEquals(0, $summary['cash_sales']);
                $this->assertEquals(0, $summary['card_sales']);
                $this->assertEquals(0, $summary['mobile_money_sales']);
                $this->assertEquals(0, $summary['bank_transfer_sales']);
                $this->assertEquals(0, $summary['other_payment_sales']);
                $this->assertEquals(0, $summary['total_sales']);
                $this->assertEquals(500, $summary['cash_in']);
                $this->assertEquals(200, $summary['cash_out']);
                $this->assertEquals(0, $summary['cash_refunds']);
                $this->assertEquals(0, $summary['cash_drops']);
                $this->assertEquals(300, $summary['net_change']);
            }
        );
    }

    public function test_last_closure_returns_most_recent(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                $closure1 = app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                // Add a small delay to ensure different timestamps
                sleep(1);

                app(CashMovementService::class)->closeShift(
                    countedBalance: 1500,
                    userId: $this->ownerA->id,
                );

                // Add a small delay to ensure different timestamps
                sleep(1);

                $closure2 = app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                $lastClosure = app(CashMovementService::class)->lastClosure($till);

                $this->assertEquals($closure2->id, $lastClosure->id);
                $this->assertNull($lastClosure->closed_at);
            }
        );
    }

    public function test_denomination_breakdown_stored_as_json(): void
    {
        $till = $this->createTill(
            $this->tenantA->id,
            1000
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($till) {
                app(CashMovementService::class)->openShift(
                    openingBalance: 1500,
                    userId: $this->ownerA->id,
                );

                $denominationBreakdown = [
                    '2000' => 2,
                    '500' => 1,
                    '100' => 5,
                    'coins' => 50,
                ];

                $closedClosure = app(CashMovementService::class)->closeShift(
                    countedBalance: 4550,
                    denominationBreakdown: $denominationBreakdown,
                    userId: $this->ownerA->id,
                );

                $this->assertEquals($denominationBreakdown, $closedClosure->denomination_breakdown);
                $this->assertIsArray($closedClosure->denomination_breakdown);
            }
        );
    }
}
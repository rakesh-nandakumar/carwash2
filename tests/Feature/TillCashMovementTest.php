<?php

namespace Tests\Feature;

use App\Models\CashMovement;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Payment;
use App\Models\Till;
use App\Models\Vehicle;
use App\Services\CashMovementService;
use App\Services\CurrentContext;
use Tests\TestCase;

class TillCashMovementTest extends TestCase
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

    public function test_cash_sale_creates_cash_movement(): void
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
                    'customer_code' => 'CUS-CASH-001',
                    'full_name' => 'Cash Sale Customer',
                    'phone' => '0770000001',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'CASH-001',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-CASH-001',
                    'status' => 'ready_for_payment',
                ]);

                $invoice = Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-CASH-001',
                    'status' => 'issued',
                    'subtotal' => 500,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 500,
                    'paid' => 0,
                    'balance' => 500,
                ]);

                return Payment::create([
                    'invoice_id' => $invoice->id,
                    'method' => 'cash',
                    'amount' => 500,
                    'reference' => null,
                    'received_by' => $this->ownerA->id,
                ]);
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($payment, $till) {
                $movement = app(CashMovementService::class)->recordSale(
                    amount: 500,
                    reference: $payment,
                    userId: $this->ownerA->id,
                );

                $this->assertDatabaseHas('cash_movements', [
                    'id' => $movement->id,
                    'tenant_id' => $this->tenantA->id,
                    'till_id' => $till->id,
                    'type' => 'in',
                    'source' => 'sale',
                    'amount' => 500,
                    'reference_id' => $payment->id,
                ]);

                $this->assertSame(
                    1500.0,
                    app(CashMovementService::class)->expectedBalance($till)
                );
            }
        );
    }
}
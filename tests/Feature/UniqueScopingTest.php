<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Product;
use App\Models\User;
use App\Services\CurrentContext;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UniqueScopingTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_same_email_can_exist_in_two_tenants(): void
    {
        app(CurrentContext::class)->runForTenant($this->tenantB->id, function () {
            $business = Business::first();
            User::create([
                'business_id' => $business->id,
                'name' => 'Same Name',
                'email' => 'owner-a@fixtures.test',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'active' => true,
            ]);
        });

        $this->assertSame(1, $this->ownerA->email === 'owner-a@fixtures.test' ? 1 : 0);

        $a = app(CurrentContext::class)->runForTenant($this->tenantA->id, function () {
            return User::where('email', 'owner-a@fixtures.test')->first();
        });
        $b = app(CurrentContext::class)->runForTenant($this->tenantB->id, function () {
            return User::where('email', 'owner-a@fixtures.test')->first();
        });

        $this->assertNotNull($a);
        $this->assertNotNull($b);
        $this->assertNotSame($a->id, $b->id);
    }

    public function test_same_job_number_can_exist_in_two_tenants(): void
    {
        app(CurrentContext::class)->runForTenant($this->tenantB->id, function () {
            $business = Business::first();
            $customer = Customer::create([
                'business_id' => $business->id,
                'customer_code' => 'CUS-B1',
                'full_name' => 'Beta Customer',
                'phone' => '0771231234',
            ]);
            $vehicle = \App\Models\Vehicle::create([
                'customer_id' => $customer->id,
                'registration_number' => 'VB-0001',
            ]);
            Job::create([
                'business_id' => $business->id,
                'branch_id' => \App\Models\Branch::first()->id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'job_number' => 'JB-0001',
                'status' => 'waiting_for_checkin',
            ]);
        });

        app(CurrentContext::class)->runForTenant($this->tenantA->id, function () {
            $business = Business::where('tenant_id', $this->tenantA->id)->first();
            $customer = Customer::create([
                'business_id' => $business->id,
                'customer_code' => 'CUS-A1',
                'full_name' => 'Alpha Customer',
                'phone' => '0773213210',
            ]);
            $vehicle = \App\Models\Vehicle::create([
                'customer_id' => $customer->id,
                'registration_number' => 'VA-0001',
            ]);
            Job::create([
                'business_id' => $business->id,
                'branch_id' => \App\Models\Branch::first()->id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'job_number' => 'JB-0001',
                'status' => 'waiting_for_checkin',
            ]);
        });

        $this->assertSame(2, Job::query()->withoutTenantScope()->where('job_number', 'JB-0001')->count());
    }

    public function test_same_product_sku_can_exist_in_two_tenants(): void
    {
        app(CurrentContext::class)->runForTenant($this->tenantA->id, function () {
            Product::create([
                'business_id' => Business::first()->id,
                'sku' => 'SKU-SHARED',
                'name' => 'Alpha Oil',
            ]);
        });

        app(CurrentContext::class)->runForTenant($this->tenantB->id, function () {
            Product::create([
                'business_id' => Business::first()->id,
                'sku' => 'SKU-SHARED',
                'name' => 'Beta Oil',
            ]);
        });

        $this->assertSame(2, Product::query()->withoutTenantScope()->where('sku', 'SKU-SHARED')->count());
    }
}

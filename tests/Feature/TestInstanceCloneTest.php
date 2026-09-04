<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Product;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\CurrentContext;
use App\Services\Tenancy\TestInstanceService;
use Tests\TestCase;

class TestInstanceCloneTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_clone_copies_all_owned_rows_without_cross_tenant_fks(): void
    {
        $live = $this->tenantA;

        app(CurrentContext::class)->runForTenant($live->id, function () {
            $business = Business::first();
            $branch = \App\Models\Branch::first();
            $customer = Customer::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'customer_code' => 'CUS-CLONE',
                'full_name' => 'Clone Customer',
                'phone' => '0775551234',
            ]);
            $vehicle = Vehicle::create([
                'customer_id' => $customer->id,
                'registration_number' => 'CLONE-1',
            ]);
            $service = Service::create([
                'business_id' => $business->id,
                'name' => 'Clone Wash',
                'base_price' => 100,
            ]);
            $product = Product::create([
                'business_id' => $business->id,
                'sku' => 'CLONE-SKU',
                'name' => 'Clone Part',
            ]);
            $job = Job::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'job_number' => 'CLONE-JOB',
                'status' => 'in_service',
            ]);
            Invoice::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'job_id' => $job->id,
                'invoice_number' => 'CLONE-INV',
                'status' => 'issued',
            ]);
            Appointment::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'scheduled_at' => now()->addDay(),
                'status' => 'confirmed',
            ]);
        });

        $test = app(TestInstanceService::class)->create($live);

        $this->assertSame('alpha-test', $test->slug);
        $this->assertSame($live->id, $test->parent_tenant_id);
        $this->assertSame('test', $test->environment);

        $parity = [
            'customers' => 1, 'vehicles' => 1, 'services' => 1, 'products' => 1,
            'jobs' => 1, 'invoices' => 1, 'businesses' => 1, 'appointments' => 1,
            'settings' => count(\Database\Seeders\SettingsSeeder::definitions()),
        ];

        foreach ($parity as $table => $count) {
            $liveCount = \Illuminate\Support\Facades\DB::table($table)->where('tenant_id', $live->id)->count();
            $testCount = \Illuminate\Support\Facades\DB::table($table)->where('tenant_id', $test->id)->count();
            $this->assertSame($liveCount, $testCount, "{$table} row parity");
            $this->assertSame($count, $viewCount = $testCount, "{$table} expected row count");
        }

        // No test row may reference a live row.
        $fk = [
            'jobs' => ['customer_id', 'customers'],
            'invoices' => ['job_id', 'jobs'],
            'appointments' => ['customer_id', 'customers'],
        ];
        foreach ($fk as $table => [$column, $parent]) {
            $dangling = \Illuminate\Support\Facades\DB::table($table)
                ->where('tenant_id', $test->id)
                ->whereNotIn($column, \Illuminate\Support\Facades\DB::table($parent)->select('id'))
                ->count();
            $this->assertSame(0, $dangling, "dangling {$table}.{$column}");
        }

        // Original data untouched.
        $this->assertSame(1, Customer::query()->withoutTenantScope()->where('tenant_id', $live->id)->count());
    }

    public function test_sync_refreshes_test_rows(): void
    {
        $test = app(TestInstanceService::class)->create($this->tenantA);
        $testId = $test->id;

        app(CurrentContext::class)->runForTenant($this->tenantA->id, function () {
            \App\Models\Service::create([
                'business_id' => Business::first()->id,
                'name' => 'New Since Sync',
                'base_price' => 200,
            ]);
        });

        app(TestInstanceService::class)->syncFromLive($test);

        $synced = app(CurrentContext::class)->runForTenant($testId, function () {
            return \App\Models\Service::where('name', 'New Since Sync')->count();
        });

        $this->assertSame(1, $synced);
    }
}

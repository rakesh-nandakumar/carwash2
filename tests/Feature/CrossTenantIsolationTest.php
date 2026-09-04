<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Product;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\CurrentContext;
use Tests\TestCase;

class CrossTenantIsolationTest extends TestCase
{
    use CreatesTenancyFixtures;

    private const ROUTES = [
        ['dashboard', '/{slug}/dashboard'],
        ['jobs.index', '/{slug}/jobs'],
        ['customers.index', '/{slug}/customers'],
        ['vehicles.index', '/{slug}/vehicles'],
        ['appointments.index', '/{slug}/appointments'],
        ['invoices.index', '/{slug}/invoices'],
        ['inventory.index', '/{slug}/inventory'],
        ['services.index', '/{slug}/services'],
        ['cashier.index', '/{slug}/cashier'],
        ['users.index', '/{slug}/users'],
        ['categories.index', '/{slug}/categories'],
        ['reports', '/{slug}/reports'],
        ['jobs.board', '/{slug}/jobs/board'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();

        foreach ([$this->tenantA, $this->tenantB] as $tenant) {
            app(CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant) {
                $business = Business::create([
                    'name' => $tenant->name.' Main',
                    'code' => 'CODE-'.$tenant->id.'-'.rand(1, 99999),
                ]);

                $branch = Branch::create([
                    'business_id' => $business->id,
                    'name' => 'Main Branch',
                    'code' => 'BR-'.$tenant->id.'-'.rand(1, 99999),
                ]);

                $customer = Customer::create([
                    'business_id' => $business->id,
                    'customer_code' => 'CUS-'.$tenant->id,
                    'full_name' => 'Customer '.$tenant->name,
                    'phone' => '077'.$tenant->id.'000000',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'REG-'.$tenant->id,
                ]);

                $service = Service::create([
                    'business_id' => $business->id,
                    'name' => 'Wash '.$tenant->name,
                    'base_price' => 100,
                    'duration_minutes' => 30,
                ]);

                Product::create([
                    'business_id' => $business->id,
                    'sku' => 'SKU-'.$tenant->id,
                    'name' => 'Product '.$tenant->name,
                ]);

                $job = Job::create([
                    'business_id' => $business->id,
                    'branch_id' => $branch->id,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JB-'.$tenant->id,
                    'status' => 'in_service',
                ]);

                Invoice::create([
                    'business_id' => $business->id,
                    'branch_id' => $branch->id,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-'.$tenant->id,
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
        }
    }

    public function test_tenant_a_sees_only_its_own_rows_everywhere(): void
    {
        $markers = [
            'REG-2', 'CUS-2', 'SKU-2', 'JB-2', 'INV-2', 'Wash Beta Wash',
        ];

        foreach (self::ROUTES as [$name, $path]) {
            $url = str_replace('{slug}', 'alpha', $path);
            $response = $this->actingAs($this->ownerA)->get($url);

            $response->assertOk();

            $body = $response->getContent();
            foreach ($markers as $marker) {
                $this->assertStringNotContainsString(
                    $marker,
                    $body,
                    "Tenant B marker [{$marker}] leaked into {$url}",
                );
            }
        }

        $this->assertStringContainsString('REG-1', $this->actingAs($this->ownerA)->get('/alpha/vehicles')->getContent());
        $this->assertStringContainsString('REG-2', $this->actingAs($this->ownerB)->get('/beta/vehicles')->getContent());
    }

    public function test_tenant_a_cannot_read_tenant_b_row_by_id(): void
    {
        $jobB = Job::query()->withoutTenantScope()->where('tenant_id', $this->tenantB->id)->first();

        $response = $this->actingAs($this->ownerA)->get("/alpha/jobs/{$jobB->id}");
        $response->assertNotFound();
    }

    public function test_tenant_a_cannot_update_tenant_b_row(): void
    {
        $customerB = Customer::query()->withoutTenantScope()->where('tenant_id', $this->tenantB->id)->first();

        $response = $this->actingAs($this->ownerA)
            ->patch("/alpha/customers/{$customerB->id}", ['full_name' => 'Stolen']);

        $response->assertNotFound();

        $name = Customer::query()->withoutTenantScope()->find($customerB->id)->full_name;
        $this->assertSame('Customer Beta Wash', $name);
    }
}

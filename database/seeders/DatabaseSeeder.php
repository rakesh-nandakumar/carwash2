<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Job;
use App\Models\JobService;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\CurrentContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::demo();

        // Permissions (global catalog) + each tenant's private roles — AFTER
        // the demo tenant exists, so it gets its role copy too.
        $this->call(PermissionSeeder::class);
        app(SettingsSeeder::class)->run($tenant->id);

        app(CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant) {
            $b = Business::create([
                'name' => 'AutoCare Pro Service Center',
                'code' => 'AUTOPRO'.rand(1000, 9999),
                'phone' => '0710000000',
                'email' => 'admin@autocare.local',
                'currency' => 'LKR',
                'mode' => 'medium',
            ]);

            $br = Branch::create([
                'business_id' => $b->id,
                'name' => 'Main Branch',
                'code' => 'MAIN'.rand(1000, 9999),
                'phone' => '0710000000',
                'address' => 'Colombo, Sri Lanka',
            ]);

            // RBAC roles (see RoleSeeder) — legacy `role` column alone gives no
            // permissions; isFullAdmin()/hasPermissionTo() only ever consult
            // the roles() relation, so every seeded user needs one attached.
            $this->call(RoleSeeder::class);
            $rbacRoles = \App\Models\Role::where('business_id', $b->id)->get()->keyBy('slug');

            foreach ([
                ['System Administrator', 'admin@autocare.local', 'super_admin', 'full-administrator'],
                ['Business Owner', 'owner@autocare.local', 'owner', 'owner'],
                ['Receptionist', 'reception@autocare.local', 'receptionist', 'service-advisor'],
                ['Technician', 'tech@autocare.local', 'technician', 'technician'],
                ['Cashier', 'cashier@autocare.local', 'cashier', 'cashier'],
            ] as [$n, $e, $role, $roleSlug]) {
                $user = User::create([
                    'business_id' => $b->id,
                    'branch_id' => $br->id,
                    'name' => $n,
                    'email' => $e,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'active' => true,
                ]);

                if ($rbacRoles->has($roleSlug)) {
                    $user->roles()->attach($rbacRoles[$roleSlug]->id);
                }
            }

            $cats = [];
            foreach (['Car Wash', 'Maintenance', 'Detailing', 'Brakes', 'Electrical', 'AC', 'Body Work'] as $n) {
                $cats[$n] = ServiceCategory::create(['business_id' => $b->id, 'name' => $n]);
            }

            $services = [
                ['Basic Exterior Wash', 'Car Wash', 2500],
                ['Interior Cleaning', 'Car Wash', 3500],
                ['Full Service', 'Maintenance', 15000],
                ['Oil Change', 'Maintenance', 6500],
                ['Brake Inspection', 'Brakes', 3000],
                ['AC Service', 'AC', 7500],
                ['Full Detailing', 'Detailing', 25000],
            ];
            foreach ($services as [$n, $c, $p]) {
                Service::create([
                    'business_id' => $b->id,
                    'service_category_id' => $cats[$c]->id,
                    'name' => $n,
                    'base_price' => $p,
                    'labor_cost' => $p * .2,
                    'duration_minutes' => 60,
                ]);
            }

            $products = [
                ['ENG-OIL-5W30', '5W-30 Engine Oil', 'Oil', 'Castrol', 4500, 6000, 5],
                ['OIL-FILTER-01', 'Oil Filter', 'Filter', 'Bosch', 1800, 2500, 5],
                ['BRAKE-PAD-01', 'Front Brake Pad Set', 'Brake', 'Brembo', 8000, 11000, 3],
                ['SHAMPOO-01', 'Premium Car Shampoo', 'Chemical', '3M', 2200, 3200, 4],
            ];
            foreach ($products as [$sku, $n, $cat, $brand, $cost, $sell, $min]) {
                $p = Product::create([
                    'tenant_id' => $tenant->id,
                    'business_id' => $b->id,
                    'sku' => $sku,
                    'name' => $n,
                    'category' => $cat,
                    'brand' => $brand,
                    'cost_price' => $cost,
                    'selling_price' => $sell,
                    'minimum_stock' => $min,
                ]);
                Inventory::create([
                    'tenant_id' => $tenant->id,
                    'business_id' => $b->id,
                    'product_id' => $p->id,
                    'branch_id' => $br->id,
                    'quantity' => 20,
                    'reserved_quantity' => 0,
                ]);
            }

            $c = Customer::create([
                'business_id' => $b->id,
                'branch_id' => $br->id,
                'customer_code' => 'CUS-000001',
                'full_name' => 'Kasun Perera',
                'phone' => '0771234567',
                'whatsapp' => '94771234567',
                'email' => 'kasun@example.com',
            ]);

            $v = Vehicle::create([
                'customer_id' => $c->id,
                'registration_number' => 'CAB-1234',
                'make' => 'Toyota',
                'model' => 'Aqua',
                'category' => 'Small Car',
                'mileage' => 56000,
                'fuel_level' => 60,
            ]);

            $s = Service::where('name', 'Full Service')->first();
            $j = Job::create([
                'business_id' => $b->id,
                'branch_id' => $br->id,
                'customer_id' => $c->id,
                'vehicle_id' => $v->id,
                'job_number' => 'JOB-2026-000001',
                'status' => 'in_service',
                'priority' => 'normal',
                'customer_complaint' => 'Routine service',
            ]);
            JobService::create([
                'job_id' => $j->id,
                'service_id' => $s->id,
                'name_snapshot' => $s->name,
                'unit_price' => $s->base_price,
                'approval_status' => 'approved',
            ]);

            $inv = Invoice::create([
                'business_id' => $b->id,
                'branch_id' => $br->id,
                'customer_id' => $c->id,
                'job_id' => $j->id,
                'invoice_number' => 'INV-2026-000001',
                'status' => 'issued',
                'subtotal' => $s->base_price,
                'total' => $s->base_price,
                'balance' => $s->base_price,
            ]);
            InvoiceItem::create([
                'invoice_id' => $inv->id,
                'item_type' => 'service',
                'item_id' => $s->id,
                'description' => $s->name,
                'quantity' => 1,
                'unit_price' => $s->base_price,
                'line_total' => $s->base_price,
            ]);
        });
    }
}

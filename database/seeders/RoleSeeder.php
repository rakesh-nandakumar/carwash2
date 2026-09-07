<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Services\CurrentContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::query()->get();

        foreach ($tenants as $tenant) {
            app(CurrentContext::class)->runForTenant(
                $tenant->id,
                function () {
                    $this->seedRolesForCurrentTenant();
                }
            );
        }
    }

    private function seedRolesForCurrentTenant(): void
    {
        $businesses = Business::query()->get();

        foreach ($businesses as $business) {
            $this->seedRolesForBusiness($business);
        }
    }

    public function seedRolesForBusiness(Business $business): void
    {
        $permissions = Permission::query()
            ->get()
            ->keyBy('slug');

        $roles = [

            /*
             * Full Administrator
             *
             * Special RBAC role.
             * is_full_admin = true gives unrestricted access
             * through User::isFullAdmin().
             */
            'Full Administrator' => [
                'description' => 'Full system administrator with unrestricted access.',
                'is_full_admin' => true,
                'permissions' => $permissions->keys()->all(),
            ],

            /*
             * Owner
             *
             * Has all permissions but is not a Full Administrator.
             */
            'Owner' => [
                'description' => 'Business owner with full operational access.',
                'is_full_admin' => false,
                'permissions' => $permissions->keys()->all(),
            ],

            'Manager' => [
                'description' => 'Manager with broad operational and reporting access.',
                'is_full_admin' => false,
                'permissions' => [
                    'dashboard.access',

                    'reception.access',
                    'reception.create_job',

                    'live_job_board.access',

                    'customers.access',
                    'customers.create',
                    'customers.edit',
                    'customers.delete',
                    'customers.export',

                    'vehicles.access',
                    'vehicles.create',
                    'vehicles.edit',
                    'vehicles.delete',
                    'vehicles.transfer_ownership',

                    'appointments.access',
                    'appointments.create',
                    'appointments.edit',
                    'appointments.delete',
                    'appointments.cancel',

                    'job_cards.access',
                    'job_cards.create',
                    'job_cards.edit',
                    'job_cards.change_status',
                    'job_cards.request_additional_work',
                    'job_cards.approve',
                    'job_cards.consume_parts',
                    'job_cards.edit_inspection',

                    'inventory.access',
                    'inventory.create',
                    'inventory.edit',
                    'inventory.adjust_stock',
                    'inventory.transfer_stock',

                    'stock_adjustments.access',
                    'stock_adjustments.create',
                    'stock_adjustments.reverse',

                    'categories.access',
                    'categories.create',
                    'categories.edit',
                    'categories.delete',

                    'services.access',
                    'services.create',
                    'services.edit',
                    'services.delete',
                    'services.toggle',

                    'invoices.access',
                    'invoices.create',
                    'invoices.edit',
                    'invoices.pay',
                    'invoices.print',
                    'invoices.void',
                    'invoices.refund',
                    'invoices.export',

                    'cashier.access',
                    'cashier.search',
                    'cashier.payment',
                    'cashier.print_options',
                    'cashier.open_shift',
                    'cashier.close_shift',
                    'cashier.cash_drop',
                    'cashier.cash_in',
                    'cashier.cash_out',

                    'cash_movements.access',

                    'reports.access',
                    'reports.sales',
                    'reports.stock',
                    'reports.stock_movement',
                    'reports.services',
                    'reports.customers',
                    'reports.export',

                    'users.access',
                    'users.create',
                    'users.edit',

                    'roles.access',
                    'roles.create',
                    'roles.edit',

                    'settings.access',
                    'settings.edit_billing',
                    'settings.edit_reception',
                ],
            ],

            'Service Advisor' => [
                'description' => 'Handles reception, customers, vehicles, appointments and job cards.',
                'is_full_admin' => false,
                'permissions' => [
                    'dashboard.access',

                    'reception.access',
                    'reception.create_job',

                    'live_job_board.access',

                    'customers.access',
                    'customers.create',
                    'customers.edit',

                    'vehicles.access',
                    'vehicles.create',
                    'vehicles.edit',
                    'vehicles.transfer_ownership',

                    'appointments.access',
                    'appointments.create',
                    'appointments.edit',
                    'appointments.delete',
                    'appointments.cancel',

                    'job_cards.access',
                    'job_cards.create',
                    'job_cards.edit',
                    'job_cards.change_status',
                    'job_cards.request_additional_work',
                    'job_cards.approve',
                    'job_cards.edit_inspection',

                    'services.access',

                    'invoices.access',
                    'invoices.create',
                    'invoices.print',
                ],
            ],

            'Technician' => [
                'description' => 'Technician access for job execution and parts consumption.',
                'is_full_admin' => false,
                'permissions' => [
                    'dashboard.access',

                    'live_job_board.access',

                    'job_cards.access',
                    'job_cards.edit',
                    'job_cards.change_status',
                    'job_cards.consume_parts',
                    'job_cards.edit_inspection',

                    'inventory.access',
                ],
            ],

            'Cashier' => [
                'description' => 'Handles cashier operations, payments and invoices.',
                'is_full_admin' => false,
                'permissions' => [
                    'dashboard.access',

                    'invoices.access',
                    'invoices.pay',
                    'invoices.print',

                    'cashier.access',
                    'cashier.search',
                    'cashier.payment',
                    'cashier.print_options',
                    'cashier.open_shift',
                    'cashier.close_shift',
                    'cashier.cash_drop',
                    'cashier.cash_in',
                    'cashier.cash_out',

                    'cash_movements.access',
                ],
            ],

            'Auditor' => [
                'description' => 'Read-only reporting and operational visibility.',
                'is_full_admin' => false,
                'permissions' => [
                    'dashboard.access',

                    'customers.access',
                    'vehicles.access',
                    'appointments.access',
                    'job_cards.access',
                    'inventory.access',
                    'services.access',
                    'invoices.access',

                    'reports.access',
                    'reports.sales',
                    'reports.stock',
                    'reports.stock_movement',
                    'reports.services',
                    'reports.customers',
                    'reports.export',
                ],
            ],
        ];

        foreach ($roles as $name => $definition) {

            /*
             * Only use permissions that actually exist in the database.
             */
            $permissionIds = collect($definition['permissions'])
                ->filter(fn ($slug) => $permissions->has($slug))
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->values()
                ->all();

            /*
             * roles has a unique constraint on:
             *
             * tenant_id + name
             *
             * Therefore name must be part of the lookup.
             */
            $role = Role::updateOrCreate(
                [
                    'tenant_id' => $business->tenant_id,
                    'business_id' => $business->id,
                    'name' => $name,
                ],
                [
                    'slug' => Str::slug($name),
                    'description' => $definition['description'],
                    'is_system' => true,
                    'is_active' => true,
                    'is_full_admin' => $definition['is_full_admin'],
                ]
            );

            /*
             * Synchronize the role's permissions.
             */
            $role->permissions()->sync($permissionIds);

            /*
             * Clear cached permissions for users assigned to this role.
             */
            $role->users->each(
                fn ($user) => $user->clearPermissionCache()
            );
        }
    }
}
<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public function syncDefaultPermissions(): void
    {
        // First, delete all existing permissions to clean up
        Permission::truncate();

        $modules = [
            'reception' => ['view'],
            'dashboard' => ['view'],
            'live_job_board' => ['view'],
            'job_cards' => ['view', 'create', 'edit', 'delete', 'change_status', 'request_additional_work', 'approve', 'consume_parts', 'edit_inspection'],
            'customers' => ['view', 'create', 'edit', 'delete'],
            'vehicles' => ['view', 'create', 'edit', 'delete'],
            'appointments' => ['view', 'create', 'edit', 'delete'],
            'item_master' => ['view', 'create', 'edit', 'delete', 'adjust_stock'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'invoices' => ['view', 'pay', 'print'],
            'cashier' => ['view', 'search', 'payment', 'print_options'],
            'reports' => ['view'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit_billing'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = ucfirst($action) . ' ' . ucfirst($module);
                $slug = $action . '_' . $module;

                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'module' => $module,
                        'description' => "Permission to {$action} {$module}"
                    ]
                );
            }
        }
        
        // Sync role permissions after recreating permissions
        $this->syncDefaultRoles();
    }

    public function syncDefaultRoles(): void
    {
        $rolePermissions = [
            'super_admin' => [], // All permissions
            'owner' => [
                'view_reception', 'view_dashboard', 'view_live_job_board', 'view_job_cards', 'create_job_cards', 'edit_job_cards', 'delete_job_cards', 'change_status_job_cards', 'request_additional_work_job_cards', 'approve_job_cards', 'consume_parts_job_cards', 'edit_inspection_job_cards',
                'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
                'view_vehicles', 'create_vehicles', 'edit_vehicles', 'delete_vehicles',
                'view_appointments', 'create_appointments', 'edit_appointments', 'delete_appointments',
                'view_item_master', 'create_item_master', 'edit_item_master', 'delete_item_master', 'adjust_stock_item_master',
                'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
                'view_invoices', 'pay_invoices', 'print_invoices',
                'view_cashier', 'search_cashier', 'payment_cashier', 'print_options_cashier',
                'view_reports',
                'view_users', 'create_users', 'edit_users', 'delete_users',
                'view_settings', 'edit_billing_settings'
            ],
            'manager' => [
                'view_reception', 'view_dashboard', 'view_live_job_board', 'view_job_cards', 'create_job_cards', 'edit_job_cards', 'delete_job_cards', 'change_status_job_cards', 'request_additional_work_job_cards', 'approve_job_cards', 'consume_parts_job_cards', 'edit_inspection_job_cards',
                'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
                'view_vehicles', 'create_vehicles', 'edit_vehicles', 'delete_vehicles',
                'view_appointments', 'create_appointments', 'edit_appointments', 'delete_appointments',
                'view_item_master', 'create_item_master', 'edit_item_master', 'delete_item_master', 'adjust_stock_item_master',
                'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
                'view_invoices', 'pay_invoices', 'print_invoices',
                'view_cashier', 'search_cashier', 'payment_cashier', 'print_options_cashier',
                'view_reports'
            ],
            'receptionist' => [
                'view_reception', 'view_dashboard', 'view_job_cards', 'create_job_cards',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments',
                'view_invoices', 'pay_invoices', 'print_invoices',
                'view_cashier', 'search_cashier', 'payment_cashier', 'print_options_cashier'
            ],
            'cashier' => [
                'view_dashboard', 'view_invoices', 'pay_invoices', 'print_invoices',
                'view_cashier', 'search_cashier', 'payment_cashier', 'print_options_cashier',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments', 'view_job_cards', 'create_job_cards'
            ],
            'technician' => [
                'view_dashboard', 'view_job_cards', 'view_live_job_board', 'change_status_job_cards', 'consume_parts_job_cards'
            ],
            'staff' => [
                'view_dashboard', 'view_job_cards', 'view_live_job_board',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments'
            ],
        ];

        foreach ($rolePermissions as $roleName => $modules) {
            $role = Role::firstOrCreate(
                ['slug' => $roleName],
                [
                    'name' => ucfirst(str_replace('_', ' ', $roleName)),
                    'is_system' => true
                ]
            );

            if ($roleName === 'super_admin') {
                // Super admin gets all permissions
                $allPermissions = Permission::all();
                $role->permissions()->sync($allPermissions->pluck('id'));
            } else {
                // Use the permission slugs directly from the array
                $permissions = Permission::whereIn('slug', $modules)->get();
                $role->permissions()->sync($permissions->pluck('id'));
            }
        }
    }

    public function userHasPermission(?\App\Models\User $user, string $permissionSlug): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return Cache::remember("user.{$user->id}.permissions", 3600, function () use ($user) {
            return $user->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('slug')
                ->toArray();
        }) && in_array($permissionSlug, Cache::get("user.{$user->id}.permissions", []));
    }
}

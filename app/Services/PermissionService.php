<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

/**
 * The permission catalog is CODE, not tenant data: fixed rows generated from
 * a hardcoded module map. `permissions` therefore stays GLOBAL/unscoped; only
 * `roles` is tenant-scoped (a tenant only ever sees its own grants through
 * the scoped `role_user`/`permission_role` joins).
 */
class PermissionService
{
    /**
     * (Re)seeds the global permission catalog. Idempotent upsert + prune —
     * the old `Permission::truncate()` threw on MySQL forever (truncate on a
     * FK-referenced table is rejected, errno 1701), so RBAC has never run.
     */
    public function syncDefaultPermissions(): void
    {
        $wanted = [];

        foreach ($this->modules() as $module => $actions) {
            foreach ($actions as $action) {
                $slug = $action.'_'.$module;

                Permission::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => ucfirst($action).' '.ucfirst($module),
                        'module' => $module,
                        'description' => "Permission to {$action} {$module}",
                    ],
                );

                $wanted[] = $slug;
            }
        }

        // FK cascade tidies permission_role.
        Permission::whereNotIn('slug', $wanted)->delete();

        TenantModules::flushPermissionModuleMap();
    }

    /**
     * The tenant's own private copy of the 7 system roles, with their
     * permission grants. Runs inside the tenant's context (runForTenant),
     * so the Role rows land on the right tenant_id.
     */
    public function syncDefaultRoles(int $tenantId): void
    {
        $rolePermissions = $this->rolePermissionMap();

        foreach ($rolePermissions as $roleName => $modules) {
            $role = Role::query()->withoutTenantScope()->firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $roleName],
                [
                    'name' => ucfirst(str_replace('_', ' ', $roleName)),
                    'is_system' => true,
                ],
            );

            if ($roleName === 'super_admin') {
                $role->permissions()->sync(Permission::all()->pluck('id'));
            } else {
                $role->permissions()->sync(
                    Permission::whereIn('slug', $modules)->pluck('id'),
                );
            }
        }
    }

    public function userHasPermission(?\App\Models\User $user, string $permissionSlug): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $permissions = Cache::remember("user.{$user->id}.permissions", 3600, function () use ($user) {
            return $user->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('slug')
                ->toArray();
        });

        return in_array($permissionSlug, $permissions, true);
    }

    /**
     * @return array<string, list<string>>
     */
    private function modules(): array
    {
        return [
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
            'services' => ['view', 'create', 'edit', 'delete'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function rolePermissionMap(): array
    {
        return [
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
                'view_services', 'create_services', 'edit_services', 'delete_services',
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
                'view_reports',
                'view_services', 'create_services', 'edit_services', 'delete_services',
            ],
            'receptionist' => [
                'view_reception', 'view_dashboard', 'view_job_cards', 'create_job_cards',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments',
                'view_invoices', 'pay_invoices', 'print_invoices',
                'view_cashier', 'search_cashier', 'payment_cashier', 'print_options_cashier',
            ],
            'cashier' => [
                'view_dashboard', 'view_invoices', 'pay_invoices', 'print_invoices',
                'view_cashier', 'search_cashier', 'payment_cashier', 'print_options_cashier',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments', 'view_job_cards', 'create_job_cards',
            ],
            'technician' => [
                'view_dashboard', 'view_job_cards', 'view_live_job_board', 'change_status_job_cards', 'consume_parts_job_cards',
            ],
            'staff' => [
                'view_dashboard', 'view_job_cards', 'view_live_job_board',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments',
            ],
        ];
    }
}

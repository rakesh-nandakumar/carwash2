<?php

namespace App\Services;

class PermissionCatalog
{
    public static function all(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'actions' => [
                    'access',
                ],
            ],

            'reception' => [
                'label' => 'Reception',
                'actions' => [
                    'access',
                    'create_job',
                ],
            ],

            'live_job_board' => [
                'label' => 'Live Job Board',
                'actions' => [
                    'access',
                ],
            ],

            'customers' => [
                'label' => 'Customers',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'export',
                ],
            ],

            'vehicles' => [
                'label' => 'Vehicles',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'transfer_ownership',
                ],
            ],

            'appointments' => [
                'label' => 'Appointments',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'cancel',
                ],
            ],

            'job_cards' => [
                'label' => 'Job Cards',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'change_status',
                    'request_additional_work',
                    'approve',
                    'consume_parts',
                    'edit_inspection',
                ],
            ],

            'inventory' => [
                'label' => 'Inventory',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'adjust_stock',
                    'transfer_stock',
                ],
            ],

            'stock_adjustments' => [
                'label' => 'Stock Adjustments',
                'actions' => [
                    'access',
                    'create',
                    'reverse',
                ],
            ],

            'categories' => [
                'label' => 'Categories',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                ],
            ],

            'services' => [
                'label' => 'Services',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'toggle',
                ],
            ],

            'invoices' => [
                'label' => 'Invoices',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'pay',
                    'print',
                    'void',
                    'refund',
                    'export',
                ],
            ],

            'cashier' => [
                'label' => 'Cashier',
                'actions' => [
                    'access',
                    'search',
                    'payment',
                    'print_options',
                    'open_shift',
                    'close_shift',
                    'cash_drop',
                    'cash_in',
                    'cash_out',
                ],
            ],

            'cash_movements' => [
                'label' => 'Cash Movements',
                'actions' => [
                    'access',
                ],
            ],

            'reports' => [
                'label' => 'Reports',
                'actions' => [
                    'access',
                    'sales',
                    'stock',
                    'stock_movement',
                    'services',
                    'customers',
                    'export',
                ],
            ],

            'audit_logs' => [
                'label' => 'Audit Logs',
                'actions' => [
                    'access',
                ],
            ],

            'users' => [
                'label' => 'Users',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'reset_password',
                    'set_pin',
                    'manage_permissions',
                ],
            ],

            'roles' => [
                'label' => 'Roles',
                'actions' => [
                    'access',
                    'create',
                    'edit',
                    'delete',
                    'assign',
                ],
            ],

            'settings' => [
                'label' => 'Settings',
                'actions' => [
                    'access',
                    'edit_billing',
                    'edit_reception',
                ],
            ],
        ];
    }

    public static function permissions(): array
    {
        $permissions = [];

        foreach (self::all() as $module => $definition) {
            foreach ($definition['actions'] as $action) {
                $permissions[] = [
                    'module' => $module,
                    'action' => $action,
                    'slug' => "{$module}.{$action}",
                    'name' => "{$definition['label']} - " . self::actionLabel($action),
                    'description' => "Can {$action} {$definition['label']}",
                ];
            }
        }

        return $permissions;
    }

    private static function actionLabel(string $action): string
    {
        return ucwords(str_replace('_', ' ', $action));
    }
}
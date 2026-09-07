<?php

/*
|--------------------------------------------------------------------------
| Tenant clone topology (Test Instances)                                        |
| ----------------------------------------------------------------------------- |
|                                                                               |
| The schema map used by App\Services\Tenancy\TestInstanceService to copy every |
| tenant-scoped row of a live tenant into its isolated test instance, remapping |
| primary keys so both environments coexist in the same tables.                 |
|                                                                               |
| Rules encoded here:                                                           |
| - Only columns listed as `fks` are remapped; everything else (global          |
| references like permission ids / central_admins.id) is copied verbatim        |
| on purpose.                                                                   |
| - Tables listed in `excluded` are operational, audit- or security-related     |
| rows that must NOT leak into a test environment (audit trails are             |
| per-environment history; impersonation tokens are security credentials        |
| whose duplication would defeat their one-time nature; notifications,          |
| cache/sessions/queue rows are transient).                                     |
| - `pivots` are tables without tenant_id whose isolation is inherited from     |
| their tenant-scoped parents; rows are copied/erased by membership in the      |
| mapped id sets; keys listed in `skip_remap` reference global tables           |
| (permissions) and are copied verbatim.                                        |
| - `polymorphic` maps morph-type columns whose `*_id` may reference one of     |
| several cloned tables; `polymorphic_aliases` maps the STRING literals the     |
| type columns hold ('service'/'part') to tables, since carwash never uses      |
| Relation::morphMap().                                                         |
*/

return [

    'tables' => [
        // --- Users / roles ---
        'roles' => ['fks' => []],
        'users' => ['fks' => []],
        'settings' => ['fks' => ['updated_by' => 'users']],
        'tenant_modules' => ['fks' => []],
        'businesses' => ['fks' => []],
        'branches' => ['fks' => ['business_id' => 'businesses']],
        'categories' => ['fks' => ['business_id' => 'businesses', 'parent_id' => 'categories']],

        // --- People / CRM ---
        'customers' => ['fks' => ['business_id' => 'businesses', 'branch_id' => 'branches']],
        'vehicles' => ['fks' => ['customer_id' => 'customers']],
        'service_categories' => ['fks' => ['business_id' => 'businesses']],
        'services' => ['fks' => ['business_id' => 'businesses', 'service_category_id' => 'service_categories']],

        // --- Catalog & pricing ---
        'service_prices' => ['fks' => ['service_id' => 'services', 'branch_id' => 'branches']],
        'vehicle_categories' => ['fks' => ['business_id' => 'businesses']],
        'service_vehicle_pricing' => ['fks' => ['service_id' => 'services', 'vehicle_category_id' => 'vehicle_categories', 'branch_id' => 'branches']],
        'service_packages' => ['fks' => ['business_id' => 'businesses']],
        'package_items' => ['fks' => ['service_package_id' => 'service_packages', 'service_id' => 'services']],
        'products' => ['fks' => ['business_id' => 'businesses', 'category_id' => 'categories']],
        'inventory' => ['fks' => ['product_id' => 'products', 'branch_id' => 'branches']],
        'inventory_movements' => ['fks' => ['product_id' => 'products', 'branch_id' => 'branches', 'user_id' => 'users']],

        // --- Purchasing ---
        'suppliers' => ['fks' => ['business_id' => 'businesses']],
        'purchase_orders' => ['fks' => ['business_id' => 'businesses', 'branch_id' => 'branches', 'supplier_id' => 'suppliers']],
        'purchase_order_items' => ['fks' => ['purchase_order_id' => 'purchase_orders', 'product_id' => 'products']],
        'goods_receipts' => ['fks' => ['purchase_order_id' => 'purchase_orders', 'branch_id' => 'branches']],
        'supplier_returns' => ['fks' => ['supplier_id' => 'suppliers', 'purchase_order_id' => 'purchase_orders', 'branch_id' => 'branches']],

        // --- Appointments & jobs ---
        'appointments' => ['fks' => ['business_id' => 'businesses', 'branch_id' => 'branches', 'customer_id' => 'customers', 'vehicle_id' => 'vehicles']],
        'jobs' => [
            'fks' => [
                'business_id' => 'businesses', 'branch_id' => 'branches',
                'customer_id' => 'customers', 'vehicle_id' => 'vehicles',
                'appointment_id' => 'appointments', 'technician_id' => 'users',
            ],
        ],
        'inspections' => ['fks' => ['job_id' => 'jobs']],
        'inspection_photos' => ['fks' => ['job_id' => 'jobs', 'uploaded_by' => 'users']],
        'damage_records' => ['fks' => ['job_id' => 'jobs']],
        'job_services' => ['fks' => ['job_id' => 'jobs', 'service_id' => 'services']],
        'job_parts' => ['fks' => ['job_id' => 'jobs', 'product_id' => 'products']],
        'job_status_history' => ['fks' => ['job_id' => 'jobs', 'changed_by' => 'users']],
        'service_approvals' => ['fks' => ['job_service_id' => 'job_services', 'approved_by' => 'users']],
        'additional_work_requests' => ['fks' => ['job_id' => 'jobs', 'requested_by' => 'users', 'approved_by' => 'users']],
        'customer_supplied_parts' => ['fks' => ['job_id' => 'jobs', 'product_id' => 'products']],
        'emergency_purchases' => ['fks' => ['job_id' => 'jobs', 'product_id' => 'products', 'purchased_by' => 'users']],
        'quality_checks' => ['fks' => ['job_id' => 'jobs', 'checked_by' => 'users']],
        'work_time_logs' => ['fks' => ['job_id' => 'jobs', 'technician_id' => 'users']],
        'service_bay_assignments' => ['fks' => ['job_id' => 'jobs', 'service_bay_id' => 'service_bays', 'assigned_by' => 'users']],
        'warranties' => ['fks' => ['invoice_id' => 'invoices', 'product_id' => 'products', 'job_id' => 'jobs']],
        'complaints' => ['fks' => ['customer_id' => 'customers', 'job_id' => 'jobs', 'assigned_to' => 'users', 'resolved_by' => 'users']],
        'warranty_claims' => ['fks' => ['warranty_id' => 'warranties', 'job_id' => 'jobs', 'processed_by' => 'users']],

        // --- Invoices & money ---
        'invoices' => [
            'fks' => [
                'business_id' => 'businesses', 'branch_id' => 'branches',
                'customer_id' => 'customers', 'job_id' => 'jobs',
            ],
        ],
        'invoice_items' => ['fks' => ['invoice_id' => 'invoices']],
        'invoice_versions' => ['fks' => ['invoice_id' => 'invoices', 'changed_by' => 'users']],
        'payments' => ['fks' => ['invoice_id' => 'invoices', 'received_by' => 'users']],
        'refunds' => ['fks' => ['invoice_id' => 'invoices', 'payment_id' => 'payments', 'requested_by' => 'users', 'approved_by' => 'users', 'processed_by' => 'users']],
        'credit_notes' => ['fks' => ['invoice_id' => 'invoices', 'customer_id' => 'customers', 'created_by' => 'users']],
        'discounts' => ['fks' => ['business_id' => 'businesses']],

        // --- Tills & cash movements ---
        'tills' => [
            'fks' => [],
        ],
        'cash_movements' => [
            'fks' => [
                'till_id' => 'tills',
                'user_id' => 'users',
            ],
        ],

        // --- Loyalty / memberships ---
        'loyalty_accounts' => ['fks' => ['customer_id' => 'customers']],
        'loyalty_transactions' => ['fks' => ['loyalty_account_id' => 'loyalty_accounts', 'created_by' => 'users']],
        'memberships' => ['fks' => ['customer_id' => 'customers']],

        // --- Operations ---
        'service_bays' => ['fks' => ['branch_id' => 'branches']],
        'equipment' => ['fks' => ['branch_id' => 'branches']],
        'equipment_maintenance' => ['fks' => ['equipment_id' => 'equipment']],
        'expenses' => ['fks' => ['branch_id' => 'branches', 'created_by' => 'users', 'approved_by' => 'users']],
        'cash_registers' => ['fks' => ['branch_id' => 'branches', 'user_id' => 'users', 'closed_by' => 'users']],
        'cash_register_transactions' => ['fks' => ['cash_register_id' => 'cash_registers']],
        'stock_transfers' => [
            'fks' => [
                'from_branch_id' => 'branches', 'to_branch_id' => 'branches',
                'requested_by' => 'users', 'approved_by' => 'users', 'received_by' => 'users',
            ],
        ],
        'stock_transfer_items' => ['fks' => ['stock_transfer_id' => 'stock_transfers', 'product_id' => 'products']],
        'technician_skills' => ['fks' => ['technician_id' => 'users', 'service_category_id' => 'service_categories']],
        'communication_templates' => ['fks' => []],
    ],

    'pivots' => [
        'role_user' => ['fks' => ['role_id' => 'roles', 'user_id' => 'users']],
        'permission_role' => ['fks' => ['role_id' => 'roles'], 'skip_remap' => ['permission_id']],
    ],

    /*
    | Tables intentionally not cloned into a test instance. Audit trails are
    | per-environment operational history; impersonation tokens are security
    | credentials whose duplication would defeat impersonation's one-time
    | nature; notifications/communications/queue/session/cache rows are
    | transient operational state.
    */

    'excluded' => [
        'audit_logs',
        'notifications',
        'notifications_log',
        'communications',
        'impersonation_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'queue_jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ],

    /*
    | Morph-type id columns. The id is remapped only when the type resolves to
    | a cloned table; references to global/other values keep their value.
    | polymorphic_aliases maps the STRING literals the type columns hold to
    | their tables ('service' → services, 'part' → products).
    */

    'polymorphic' => [
        'inventory_movements' => ['type' => 'reference_type', 'id' => 'reference_id'],
        'invoice_items' => ['type' => 'item_type', 'id' => 'item_id'],
        'loyalty_transactions' => ['type' => 'reference_type', 'id' => 'reference_id'],
    ],

    'polymorphic_aliases' => [
        'service' => 'services',
        'part' => 'products',
        'product' => 'products',
        'invoice' => 'invoices',
        'job' => 'jobs',
        'loyalty_account' => 'loyalty_accounts',
        'purchase_order' => 'purchase_orders',
        'customer' => 'customers',
    ],
];
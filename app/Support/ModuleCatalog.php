<?php

namespace App\Support;

/**
 * The licensable feature groups a tenant can be given access to from master
 * control — coarser than the ~15 fine-grained permission modules (each
 * catalog module bundles several). A fine module_key not listed under any
 * catalog module here is "core" — always enabled, never gated, so a platform
 * operator can never accidentally lock a tenant out of its own login, user
 * management, or the CRM spine (customers/vehicles) every other module joins
 * to.
 *
 * See App\Services\TenantModules for the enforcement side.
 */
class ModuleCatalog
{
    public const RECEPTION = 'reception';

    public const WORKSHOP = 'workshop';

    public const SERVICE_CATALOG = 'service_catalog';

    public const INVENTORY = 'inventory';

    public const BILLING = 'billing';

    public const REPORTS = 'reports';

    /**
     * @return array<string, array{name: string, description: string, module_keys: list<string>}>
     */
    public static function definitions(): array
    {
        return [
            self::RECEPTION => [
                'name' => 'Reception & Appointments',
                'description' => 'Reception desk, customer check-in, appointments and the vehicle photo capture flow.',
                'module_keys' => ['reception', 'appointments'],
            ],
            self::WORKSHOP => [
                'name' => 'Job Cards & Live Board',
                'description' => 'Job cards, inspection, approval workflow, parts consumption and the live job board.',
                'module_keys' => ['job_cards', 'live_job_board'],
            ],
            self::SERVICE_CATALOG => [
                'name' => 'Service Catalog & Pricing',
                'description' => 'The service catalog, vehicle-category pricing and service categories.',
                'module_keys' => ['services'],
            ],
            self::INVENTORY => [
                'name' => 'Inventory & Item Master',
                'description' => 'Item master, products, stock control categories and purchasing.',
                'module_keys' => ['item_master', 'categories'],
            ],
            self::BILLING => [
                'name' => 'Billing & Cashier',
                'description' => 'Invoicing, payments, refunds and the cashier counter.',
                'module_keys' => ['invoices', 'cashier'],
            ],
            self::REPORTS => [
                'name' => 'Reports & Analytics',
                'description' => 'Sales, stock and service reporting.',
                'module_keys' => ['reports'],
            ],
        ];
    }

    /**
     * The catalog module key a fine-grained module_key belongs to, or null
     * when it's core (always enabled — not covered by any catalog module).
     */
    public static function catalogKeyFor(string $fineModuleKey): ?string
    {
        foreach (self::definitions() as $catalogKey => $definition) {
            if (in_array($fineModuleKey, $definition['module_keys'], true)) {
                return $catalogKey;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }
}

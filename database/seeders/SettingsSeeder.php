<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Tenant;
use App\Support\SettingType;
use Illuminate\Database\Seeder;

/**
 * Default business settings, seeded per tenant — `key` alone is no longer
 * unique (the same key now exists once per tenant; unique on (tenant_id,
 * key)), so every lookup/create here is keyed on (tenant_id, key). Idempotent
 * by design (create-if-missing, never overwrite): re-running this seeder
 * must never clobber a value a platform operator has since edited via the
 * central Settings screen.
 */
class SettingsSeeder extends Seeder
{
    public function run(?int $tenantId = null): void
    {
        $tenantId ??= Tenant::demo()->id;

        foreach (self::definitions() as $definition) {
            Setting::query()->withoutTenantScope()->firstOrCreate(
                ['tenant_id' => $tenantId, 'key' => $definition['key']],
                [
                    'value' => json_encode($definition['value']),
                    'type' => $definition['type'],
                    'category' => $definition['category'],
                    'label' => $definition['label'],
                    'hint' => $definition['hint'] ?? null,
                    // Legacy `group` column, kept for one release — it holds
                    // the category prefix (dropped in Phase 9).
                    'group' => $definition['category'],
                ],
            );
        }
    }

    /**
     * The single source of truth for every business setting key.
     *
     * @return list<array{key: string, value: mixed, type: string, category: string, label: string, hint?: string}>
     */
    public static function definitions(): array
    {
        return [
            // ----- Business identity -----
            ['key' => 'business.company_name', 'value' => 'AutoCare Pro', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Company Name', 'hint' => 'Shown in the sidebar, login screen and printed documents.'],
            ['key' => 'business.address', 'value' => '', 'type' => SettingType::TEXTAREA, 'category' => 'business', 'label' => 'Address'],
            ['key' => 'business.phone', 'value' => '', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Phone'],
            ['key' => 'business.email', 'value' => '', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Email'],
            ['key' => 'business.website', 'value' => '', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Website'],
            ['key' => 'business.tax_id', 'value' => '', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Tax ID / Registration No.'],
            ['key' => 'business.currency_code', 'value' => 'LKR', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Currency Code'],
            ['key' => 'business.currency_symbol', 'value' => 'Rs.', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Currency Symbol'],
            ['key' => 'business.timezone', 'value' => 'Asia/Colombo', 'type' => SettingType::TEXT, 'category' => 'business', 'label' => 'Timezone', 'hint' => 'Applied to the tenant\'s whole request pipeline — dates and times render in this zone.'],

            // ----- Branding -----
            ['key' => 'branding.logo_path', 'value' => '', 'type' => SettingType::IMAGE, 'category' => 'branding', 'label' => 'Logo', 'hint' => 'Shown in the sidebar, on the login screen and printed documents.'],
            ['key' => 'branding.logo_size_a4', 'value' => 60, 'type' => SettingType::NUMBER, 'category' => 'branding', 'label' => 'A4 Logo Size (px)', 'hint' => 'Between 30 and 150.'],
            ['key' => 'branding.logo_size_thermal', 'value' => 40, 'type' => SettingType::NUMBER, 'category' => 'branding', 'label' => 'Thermal Logo Size (px)', 'hint' => 'Between 20 and 80.'],

            // ----- Billing -----
            ['key' => 'billing.invoice_prefix', 'value' => 'INV-', 'type' => SettingType::TEXT, 'category' => 'billing', 'label' => 'Invoice Number Prefix'],
            ['key' => 'billing.receipt_prefix', 'value' => 'REC-', 'type' => SettingType::TEXT, 'category' => 'billing', 'label' => 'Receipt Number Prefix'],
            ['key' => 'billing.footer_text', 'value' => "Thank you for your business!!\nFor any enquiries, Email us on prasadauticare@gmail.com or call us on 0115 66 88 88", 'type' => SettingType::TEXTAREA, 'category' => 'billing', 'label' => 'Invoice Footer Text'],
            ['key' => 'billing.terms_conditions', 'value' => 'Payment due upon receipt. Valid for 30 days.', 'type' => SettingType::TEXTAREA, 'category' => 'billing', 'label' => 'Terms & Conditions'],
            ['key' => 'billing.default_print_format', 'value' => 'a4', 'type' => SettingType::SELECT, 'category' => 'billing', 'label' => 'Default Print Format', 'hint' => 'a4 or thermal.'],
            ['key' => 'billing.a4_enabled', 'value' => true, 'type' => SettingType::BOOLEAN, 'category' => 'billing', 'label' => 'Enable A4 Printing'],
            ['key' => 'billing.thermal_enabled', 'value' => true, 'type' => SettingType::BOOLEAN, 'category' => 'billing', 'label' => 'Enable Thermal Printing'],
            ['key' => 'billing.tax_rate', 'value' => 0, 'type' => SettingType::PERCENT, 'category' => 'billing', 'label' => 'Tax Rate %'],

            // ----- Reception -----
            ['key' => 'reception.background_type', 'value' => 'image', 'type' => SettingType::SELECT, 'category' => 'reception', 'label' => 'Background Type', 'hint' => 'image or color.'],
            ['key' => 'reception.background_image', 'value' => '', 'type' => SettingType::IMAGE, 'category' => 'reception', 'label' => 'Background Image'],
            ['key' => 'reception.background_color', 'value' => '', 'type' => SettingType::TEXT, 'category' => 'reception', 'label' => 'Background Color', 'hint' => 'Holds one of the 14 preset gradient strings.'],
            ['key' => 'reception.custom_background_color', 'value' => '', 'type' => SettingType::COLOR, 'category' => 'reception', 'label' => 'Custom Background Color'],

            // ----- Loyalty -----
            ['key' => 'loyalty.points_per_rupee', 'value' => 0.1, 'type' => SettingType::NUMBER, 'category' => 'loyalty', 'label' => 'Points Earned per Rupee'],
            ['key' => 'loyalty.rupees_per_point', 'value' => 1.0, 'type' => SettingType::NUMBER, 'category' => 'loyalty', 'label' => 'Rupees per Point'],
            ['key' => 'loyalty.points_expiry_months', 'value' => 12, 'type' => SettingType::NUMBER, 'category' => 'loyalty', 'label' => 'Points Expiry (months)'],
            ['key' => 'loyalty.membership_discount_percent', 'value' => 10, 'type' => SettingType::PERCENT, 'category' => 'loyalty', 'label' => 'Membership Discount %'],

            // ----- WhatsApp -----
            ['key' => 'whatsapp.provider', 'value' => 'none', 'type' => SettingType::SELECT, 'category' => 'whatsapp', 'label' => 'WhatsApp Provider', 'hint' => 'none or meta.'],
            ['key' => 'whatsapp.api_url', 'value' => '', 'type' => SettingType::TEXT, 'category' => 'whatsapp', 'label' => 'WhatsApp Cloud API URL'],
            ['key' => 'whatsapp.api_token', 'value' => '', 'type' => SettingType::TEXT, 'category' => 'whatsapp', 'label' => 'WhatsApp Cloud API Token', 'hint' => 'Rendered masked in the panel.'],
        ];
    }
}

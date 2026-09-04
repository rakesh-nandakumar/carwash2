<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Services\Settings;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Phase-5 compatibility shim — assembles the legacy 20-key array from the
     * new Settings:: reads so the pre-tenancy blob consumers keep working
     * byte-identically. Delete in Phase 9, once every consumer has been
     * rewritten to Settings:: directly.
     *
     * @return array<string, mixed>
     */
    public function getBillingSettings()
    {
        $defaults = $this->defaultSettings();

        $settings = [];
        foreach ($defaults as $key => $_) {
            $settings[$key] = $this->readSetting($key);
        }

        return $settings;
    }

    /**
     * @return array<string, array{0: string, 1: mixed, 2: string}> legacy key => [catalog key, default, read type]
     */
    private function defaultSettings(): array
    {
        return [
            'company_name' => ['business.company_name', 'AutoCare Pro', 'string'],
            'address' => ['business.address', '', 'string'],
            'phone' => ['business.phone', '', 'string'],
            'email' => ['business.email', '', 'string'],
            'website' => ['business.website', '', 'string'],
            'tax_id' => ['business.tax_id', '', 'string'],
            'invoice_prefix' => ['billing.invoice_prefix', 'INV-', 'string'],
            'receipt_prefix' => ['billing.receipt_prefix', 'REC-', 'string'],
            'footer_text' => ['billing.footer_text', 'Thank you for your business!', 'string'],
            'terms_conditions' => ['billing.terms_conditions', 'Payment due upon receipt. Valid for 30 days.', 'string'],
            'logo_path' => ['branding.logo_path', '', 'string'],
            'reception_background_image' => ['reception.background_image', '', 'string'],
            'background_type' => ['reception.background_type', 'image', 'string'],
            'background_color' => ['reception.background_color', '', 'string'],
            'custom_background_color' => ['reception.custom_background_color', '', 'string'],
            'a4_enabled' => ['billing.a4_enabled', true, 'bool'],
            'thermal_enabled' => ['billing.thermal_enabled', true, 'bool'],
            'default_format' => ['billing.default_print_format', 'a4', 'string'],
            'logo_size_a4' => ['branding.logo_size_a4', 60, 'number'],
            'logo_size_thermal' => ['branding.logo_size_thermal', 40, 'number'],
        ];
    }

    private function readSetting(string $key): mixed
    {
        [$catalogKey, $default, $type] = $this->defaultSettings()[$key];

        return match ($type) {
            'bool' => Settings::bool($catalogKey, $default),
            'number' => Settings::num($catalogKey, $default),
            default => Settings::str($catalogKey, $default),
        };
    }
}

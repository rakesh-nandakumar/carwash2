<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Services\CurrentContext;
use App\Services\Settings;
use Tests\TestCase;

class SettingsMigrationTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
        app(\Database\Seeders\SettingsSeeder::class)->run($this->tenantA->id);
    }

    public function test_catalog_is_seeded_one_row_per_key(): void
    {
        $rows = \App\Models\Setting::query()->withoutTenantScope()
            ->where('tenant_id', $this->tenantA->id)->count();

        $this->assertSame(count(\Database\Seeders\SettingsSeeder::definitions()), $rows);

        // No legacy blob rows survive.
        $legacy = \App\Models\Setting::query()->withoutTenantScope()
            ->where('key', 'like', 'business\_%')->count();
        $this->assertSame(0, $legacy);
    }

    public function test_shim_assembles_identical_legacy_array_after_writing_via_settings(): void
    {
        Settings::set('business.company_name', 'Acme Wash Ltd.', null, $this->tenantA->id);
        Settings::set('billing.invoice_prefix', 'ACME-', null, $this->tenantA->id);
        Settings::set('billing.a4_enabled', false, null, $this->tenantA->id);
        Settings::set('branding.logo_size_thermal', 55, null, $this->tenantA->id);

        $business = Business::query()->withoutTenantScope()->where('tenant_id', $this->tenantA->id)->first();

        $legacy = app(CurrentContext::class)->runForTenant($this->tenantA->id, function () use ($business) {
            return $business->getBillingSettings();
        });

        $this->assertSame('Acme Wash Ltd.', $legacy['company_name']);
        $this->assertSame('ACME-', $legacy['invoice_prefix']);
        $this->assertSame(false, $legacy['a4_enabled']);
        $this->assertSame(55.0, $legacy['logo_size_thermal']);
        $this->assertSame('REC-', $legacy['receipt_prefix']);
        $this->assertSame('a4', $legacy['default_format']);
        $this->assertSame('', $legacy['reception_background_image']);
    }

    public function test_settings_reads_are_tenant_keyed(): void
    {
        Settings::set('billing.tax_rate', 12.5, null, $this->tenantA->id);

        $aRate = app(CurrentContext::class)->runForTenant($this->tenantA->id, function () {
            return Settings::num('billing.tax_rate', 0);
        });
        $this->assertSame(12.5, $aRate);

        // Tenant B's own cache is separate — untouched.
        $bRate = app(CurrentContext::class)->runForTenant($this->tenantB->id, function () {
            return Settings::num('billing.tax_rate', 0);
        });
        $this->assertSame(0.0, $bRate);
    }

    public function test_unknown_key_cannot_be_created(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Settings::set('made.up.key', 'x', null, $this->tenantA->id);
    }
}

<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\AuditService;
use App\Services\CurrentContext;
use App\Services\Settings;
use App\Support\SettingType;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\Request;

/**
 * The relocated Settings admin — every business setting (branding, billing,
 * reception background, loyalty, whatsapp, ...) for ONE tenant, edited by a
 * platform operator on that tenant's behalf. Tenant staff have no
 * self-service settings screen anymore; see App\Services\Settings for the
 * read path every other part of the app still uses at runtime.
 */
class TenantSettingController extends Controller
{
    /**
     * Every known setting key (from the same catalog SettingsSeeder seeds
     * from), merged with this tenant's actual overrides — a key the tenant
     * has never had touched still shows its catalog default, unsaved.
     */
    public function index(Tenant $tenant)
    {
        $overrides = Setting::query()->withoutTenantScope()->where('tenant_id', $tenant->id)->get()->keyBy('key');

        $settings = collect(SettingsSeeder::definitions())->mapWithKeys(function (array $definition) use ($overrides) {
            $row = $overrides->get($definition['key']);

            return [$definition['key'] => [
                'key' => $definition['key'],
                'type' => $definition['type'],
                'category' => $definition['category'],
                'label' => $definition['label'],
                'hint' => $definition['hint'] ?? null,
                'value' => $row !== null
                    ? self::decode($row->value)
                    : $definition['value'],
                'overridden' => $row !== null,
                'updated_at' => $row?->updated_at,
            ]];
        })->all();

        return view('central.tenants.settings', [
            'tenant' => $tenant,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $submitted = $request->input('s', []);

        foreach ($submitted as $key => $value) {
            // Skip logo_path in the regular loop - it's handled by storeUploads
            if ($key === 'branding.logo_path') {
                continue;
            }

            // Unknown keys are never created — Settings::set throws (404) on
            // them, so a hand-crafted POST cannot invent rows.
            $definition = collect(SettingsSeeder::definitions())->firstWhere('key', $key);
            if ($definition === null) {
                continue;
            }

            Settings::set($key, $this->coerce($value, $definition['type']), null, $tenant->id);
        }

        $this->storeUploads($request, $tenant);

        Settings::invalidate($tenant->id);

        // Log settings change in the tenant's context
        app(AuditService::class)->log(
            'tenant.settings_changed',
            "Settings updated for tenant '{$tenant->name}'",
            'info',
            'central_admin',
            $request->user('central')->email,
            [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'changed_keys' => array_keys($request->input('s', [])),
            ],
            false,
            $tenant->id
        );

        return back()->with('success', 'Settings saved for '.$tenant->name.'.');
    }

    private function storeUploads(Request $request, Tenant $tenant): void
    {
        // Handle logo upload - always process if file is present
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("tenants/{$tenant->id}/branding", 'public');
            Settings::set('branding.logo_path', $path, null, $tenant->id);
        } elseif ($request->filled('remove_logo') && $request->input('remove_logo') == '1') {
            // Only remove if explicitly requested
            Settings::set('branding.logo_path', '', null, $tenant->id);
        }
        // If neither file upload nor remove_logo is present, keep existing value
        // (don't clear it based on empty hidden field) - do nothing here

        if ($request->hasFile('reception_background')) {
            $path = $request->file('reception_background')->store("tenants/{$tenant->id}/reception", 'public');
            Settings::set('reception.background_image', $path, null, $tenant->id);
        } elseif ($request->filled('remove_background') && $request->input('remove_background') == '1') {
            Settings::set('reception.background_image', '', null, $tenant->id);
        }
    }

    private function coerce(mixed $value, string $type): mixed
    {
        match ($type) {
            SettingType::BOOLEAN => $value = $value === '1' || $value === 'true' || $value === true,
            SettingType::NUMBER,
            SettingType::MONEY,
            SettingType::PERCENT => $value = $value === null || $value === '' ? 0 : (float) $value,
            SettingType::JSON => $value = is_string($value) ? json_decode($value, true) ?? $value : $value,
            default => $value,
        };

        return $value;
    }

    private static function decode(?string $raw): mixed
    {
        if ($raw === null) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
    }
}

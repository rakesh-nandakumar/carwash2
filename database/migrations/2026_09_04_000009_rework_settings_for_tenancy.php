<?php

use App\Models\Tenant;
use Database\Seeders\SettingsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Settings move wholesale into master control. The old model was ONE JSON
 * blob per business (key='business_{id}') with a read-modify-write on every
 * save — updateBilling dropped the reception keys every save, which is why
 * fix_reception_settings.php exists. Replace it with the catalog model: one
 * row per (tenant_id, key), each key its own row so no save can ever drop
 * another tab's keys — the split-brain bug dies structurally.
 *
 * Order matters:
 *   1. Add tenant_id/category/label/hint/updated_by; widen value.
 *   2. Split each business blob into one row per BLOB_MAP entry.
 *   3. Seed the catalog keys the blob didn't cover.
 *   4. THEN add unique(tenant_id, key) — any surviving business_* row would
 *      collide with the catalog's own keys (e.g. business.company_name).
 *
 * Keep the `group` column for one release (write the category prefix into
 * it); drop in Phase 9.
 */
return new class extends Migration
{
    /**
     * Legacy blob key => catalog key.
     *
     * @var array<string, string>
     */
    private const BLOB_MAP = [
        'company_name' => 'business.company_name',
        'address' => 'business.address',
        'phone' => 'business.phone',
        'email' => 'business.email',
        'website' => 'business.website',
        'tax_id' => 'business.tax_id',
        'invoice_prefix' => 'billing.invoice_prefix',
        'receipt_prefix' => 'billing.receipt_prefix',
        'footer_text' => 'billing.footer_text',
        'terms_conditions' => 'billing.terms_conditions',
        'default_format' => 'billing.default_print_format',
        'a4_enabled' => 'billing.a4_enabled',
        'thermal_enabled' => 'billing.thermal_enabled',
        'logo_path' => 'branding.logo_path',
        'logo_size_a4' => 'branding.logo_size_a4',
        'logo_size_thermal' => 'branding.logo_size_thermal',
        'reception_background_image' => 'reception.background_image',
        'background_type' => 'reception.background_type',
        'background_color' => 'reception.background_color',
        'custom_background_color' => 'reception.custom_background_color',
    ];

    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')
                ->constrained('tenants')->cascadeOnDelete();
            $table->string('category')->nullable()->index()->after('type');
            $table->string('label')->nullable()->after('category');
            $table->string('hint')->nullable()->after('label');
            $table->foreignId('updated_by')->nullable()->after('hint')
                ->constrained('users')->nullOnDelete();
            $table->longText('value')->nullable()->change();

            $table->dropIndex(['group', 'key']);
            $table->dropUnique('settings_key_unique');
        });

        $this->splitBlobs();
        $this->seedCatalog();

        Schema::table('settings', function (Blueprint $table) {
            $table->unique(['tenant_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'key']);
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['category', 'label', 'hint']);
        });
    }

    private function splitBlobs(): void
    {
        foreach (DB::table('businesses')->get() as $business) {
            $categoryPrefixes = [
                'business.' => 'business',
                'branding.' => 'branding',
                'billing.' => 'billing',
                'reception.' => 'reception',
            ];

            $blob = DB::table('settings')
                ->where('key', 'business_'.$business->id)
                ->first();

            if ($blob === null) {
                continue;
            }

            $values = json_decode((string) $blob->value, true) ?: [];

            foreach (self::BLOB_MAP as $oldKey => $catalogKey) {
                if (! array_key_exists($oldKey, $values)) {
                    continue;
                }

                $category = collect($categoryPrefixes)->first(
                    fn ($prefix, $key) => str_starts_with($catalogKey, $key),
                ) ?: 'business';

                DB::table('settings')->insert([
                    'tenant_id' => $business->tenant_id,
                    'group' => $category,
                    'key' => $catalogKey,
                    'value' => json_encode($values[$oldKey]),
                    'type' => $this->typeFor($catalogKey),
                    'category' => $category,
                    'label' => $catalogKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('settings')->where('id', $blob->id)->delete();
        }
    }

    private function seedCatalog(): void
    {
        foreach (DB::table('businesses')->pluck('tenant_id') as $tenantId) {
            // SettingsSeeder is idempotent (firstOrCreate on (tenant_id, key)).
            DB::transaction(function () use ($tenantId): void {
                $seeder = new SettingsSeeder;
                $seeder->run((int) $tenantId);
            });
        }
    }

    private function typeFor(string $catalogKey): string
    {
        return collect(SettingsSeeder::definitions())->firstWhere('key', $catalogKey)['type']
            ?? 'text';
    }
};

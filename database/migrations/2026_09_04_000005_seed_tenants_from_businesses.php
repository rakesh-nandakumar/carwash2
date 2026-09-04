<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Birth of the tenancy boundary: one Tenant row per existing businesses row,
 * then businesses.tenant_id nullable → backfilled → NOT NULL. Slugs come from
 * config('tenancy.bootstrap_slugs') keyed by business id when the operator
 * registered one, falling back to a ReservedSlug-checked Str::slug($name).
 *
 * NOTE: phase-0 data audit removed seeder-junk business rows before this ran,
 * so everything here is genuinely owned.
 */
return new class extends Migration
{
    public function up(): void
    {
        $reserved = array_merge([
            'uploads', 'css', 'js', 'build', 'storage', 'vendor', 'favicon.ico',
            'robots.txt', 'up', 'login', 'logout', 'admin', 'central',
        ], [strtolower((string) config('tenancy.central_prefix'))]);

        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
        });

        foreach (DB::table('businesses')->orderBy('id')->get() as $business) {
            $slug = (string) (config('tenancy.bootstrap_slugs')[$business->id] ?? '');
            $slug = strtolower(trim($slug));

            if ($slug === '' || in_array($slug, $reserved, true) || ! preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $slug)) {
                $slug = Str::slug($business->name);

                if ($slug === '' || in_array($slug, $reserved, true) || ! preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $slug)) {
                    $slug = 'tenant-'.$business->id;
                }
            }

            $tenantId = DB::table('tenants')->insertGetId([
                'name' => $business->name,
                'slug' => $slug,
                'status' => 'active',
                'environment' => 'live',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('businesses')->where('id', $business->id)->update(['tenant_id' => $tenantId]);
        }

        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });

        // businesses.code becomes tenant-scoped — two tenants may both have
        // the same internal business code.
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropUnique('businesses_code_unique');
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        DB::table('tenants')->delete();
    }
};

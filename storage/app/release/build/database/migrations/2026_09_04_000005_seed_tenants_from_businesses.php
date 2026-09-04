<?php

use App\Support\ModuleCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $reserved = array_merge([
            'uploads',
            'css',
            'js',
            'build',
            'storage',
            'vendor',
            'favicon.ico',
            'robots.txt',
            'up',
            'login',
            'logout',
            'admin',
            'central',
        ], [
            strtolower((string) config('tenancy.central_prefix')),
        ]);

        /*
         * Add businesses.tenant_id only if it does not already exist.
         *
         * MySQL can keep DDL changes from a migration that later failed,
         * so this migration must be safe to resume.
         */
        if (! Schema::hasColumn('businesses', 'tenant_id')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();
            });
        }

        foreach (DB::table('businesses')->orderBy('id')->get() as $business) {
            /*
             * If this business is already linked to a tenant, reuse it.
             */
            $tenantId = $business->tenant_id ?? null;

            /*
             * Otherwise resolve/create the tenant.
             */
            if ($tenantId === null) {
                $slug = (string) (
                    config('tenancy.bootstrap_slugs')[$business->id] ?? ''
                );

                $slug = strtolower(trim($slug));

                if (
                    $slug === ''
                    || in_array($slug, $reserved, true)
                    || ! preg_match(
                        '/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/',
                        $slug
                    )
                ) {
                    $slug = Str::slug($business->name);

                    if (
                        $slug === ''
                        || in_array($slug, $reserved, true)
                        || ! preg_match(
                            '/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/',
                            $slug
                        )
                    ) {
                        $slug = 'tenant-' . $business->id;
                    }
                }

                /*
                 * Reuse an existing tenant if the migration was partially
                 * completed before failing.
                 */
                $tenantId = DB::table('tenants')
                    ->where('slug', $slug)
                    ->value('id');

                if ($tenantId === null) {
                    $tenantId = DB::table('tenants')->insertGetId([
                        'name' => $business->name,
                        'slug' => $slug,
                        'status' => 'active',
                        'environment' => 'live',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('businesses')
                    ->where('id', $business->id)
                    ->update([
                        'tenant_id' => $tenantId,
                    ]);
            }

            /*
             * IMPORTANT:
             *
             * Existing tenants created by this migration must receive the
             * same default module entitlements as tenants created through
             * TenantProvisioning.
             */
            foreach (ModuleCatalog::keys() as $moduleKey) {
                DB::table('tenant_modules')->updateOrInsert(
                    [
                        'tenant_id' => $tenantId,
                        'module_key' => $moduleKey,
                    ],
                    [
                        'is_enabled' => true,
                        'granted_by' => null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        /*
         * tenant_id is already present when the migration was partially
         * executed, so only change it to NOT NULL when required.
         */
        if (Schema::hasColumn('businesses', 'tenant_id')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable(false)
                    ->change();
            });
        }

        /*
         * businesses.code becomes tenant-scoped.
         */
        $indexes = collect(DB::select("SHOW INDEX FROM businesses"))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();

        if (in_array('businesses_code_unique', $indexes, true)) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropUnique('businesses_code_unique');
            });
        }

        $indexes = collect(DB::select("SHOW INDEX FROM businesses"))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();

        if (! in_array('businesses_tenant_id_code_unique', $indexes, true)) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'code'],
                    'businesses_tenant_id_code_unique'
                );
            });
        }
    }

    public function down(): void
    {
        /*
         * Do not delete tenants here automatically. This migration may have
         * been used to establish the production tenancy boundary.
         */
        if (Schema::hasColumn('businesses', 'tenant_id')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        DB::table('tenants')->delete();
    }
};
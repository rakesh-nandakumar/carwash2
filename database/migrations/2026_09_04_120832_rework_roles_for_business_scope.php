<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Existing roles were tenant-level.
         *
         * Existing users belong to Business 1, so associate
         * existing legacy roles with Business 1.
         */
        DB::table('roles')
            ->whereNull('business_id')
            ->update([
                'business_id' => 1,
            ]);

        /*
         * The previous migration attempt already removed:
         *
         * roles_tenant_id_name_unique
         *
         * Add a standalone tenant_id index first because the
         * tenant_id foreign key currently depends on the existing
         * (tenant_id, slug) index.
         */
        if (! Schema::hasIndex('roles', 'roles_tenant_id_index')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->index(
                    'tenant_id',
                    'roles_tenant_id_index'
                );
            });
        }

        /*
         * Now the tenant_id foreign key has its own supporting index,
         * so the old tenant-wide slug uniqueness can be removed.
         */
        if (Schema::hasIndex('roles', 'roles_tenant_id_slug_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique('roles_tenant_id_slug_unique');
            });
        }

        /*
         * Roles are now unique within a business.
         */
        if (! Schema::hasIndex('roles', 'roles_tenant_business_name_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'business_id', 'name'],
                    'roles_tenant_business_name_unique'
                );
            });
        }

        if (! Schema::hasIndex('roles', 'roles_tenant_business_slug_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'business_id', 'slug'],
                    'roles_tenant_business_slug_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('roles', 'roles_tenant_business_name_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(
                    'roles_tenant_business_name_unique'
                );
            });
        }

        if (Schema::hasIndex('roles', 'roles_tenant_business_slug_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(
                    'roles_tenant_business_slug_unique'
                );
            });
        }

        if (! Schema::hasIndex('roles', 'roles_tenant_id_name_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'name'],
                    'roles_tenant_id_name_unique'
                );
            });
        }

        if (! Schema::hasIndex('roles', 'roles_tenant_id_slug_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'slug'],
                    'roles_tenant_id_slug_unique'
                );
            });
        }

        if (Schema::hasIndex('roles', 'roles_tenant_id_index')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex(
                    'roles_tenant_id_index'
                );
            });
        }
    }
};
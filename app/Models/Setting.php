<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Services\Settings;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per (tenant_id, key) — an OVERRIDE of the catalog default from
 * Database\Seeders\SettingsSeeder. There is no tenant-facing settings screen
 * anymore: values are edited only from master control
 * (App\Http\Controllers\Central\TenantSettingController) and read here via
 * App\Services\Settings at render time.
 */
class Setting extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}

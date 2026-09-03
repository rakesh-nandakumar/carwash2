<?php

namespace Database\Seeders;

use App\Services\PermissionService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionService = new PermissionService();
        $permissionService->syncDefaultPermissions();
        $permissionService->syncDefaultRoles();
    }
}

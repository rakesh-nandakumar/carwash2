<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Services\PermissionCatalog;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::permissions() as $definition) {
            Permission::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'module' => $definition['module'],
                    'description' => $definition['description'],
                ]
            );
        }
    }
}
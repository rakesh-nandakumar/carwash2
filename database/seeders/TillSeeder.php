<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Till;
use App\Services\CurrentContext;
use Illuminate\Database\Seeder;

class TillSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::query()->each(function (Tenant $tenant) {
            app(CurrentContext::class)->runForTenant(
                $tenant->id,
                function () {
                    Till::query()->firstOrCreate(
                        ['code' => 'MAIN'],
                        [
                            'name' => 'Main Till',
                            'description' => 'Main cashier till',
                            'opening_balance' => 0,
                            'is_active' => true,
                        ]
                    );
                }
            );
        });
    }
}
<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Till;
use App\Models\Branch;
use App\Services\CurrentContext;
use Illuminate\Database\Seeder;

class TestTillsSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::query()->each(function (Tenant $tenant) {
            app(CurrentContext::class)->runForTenant(
                $tenant->id,
                function () use ($tenant) {
                    $branch = Branch::first();

                    if (!$branch) {
                        $this->command->error('No branch found. Please seed branches first.');
                        return;
                    }

                    // Create main till if it doesn't exist
                    $mainTill = Till::firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'code' => 'MAIN'
                        ],
                        [
                            'branch_id' => $branch->id,
                            'name' => 'Main Till',
                            'description' => 'Primary cash register at front desk',
                            'location' => 'Front Desk',
                            'ip_address' => '192.168.1.10',
                            'opening_balance' => 5000.00,
                            'is_active' => true,
                        ]
                    );

                    $this->command->info("Main till created/updated: {$mainTill->name}");

                    // Create additional tills for different workstations
                    $tills = [
                        [
                            'code' => 'WASH_1',
                            'name' => 'Wash Station 1',
                            'description' => 'Till at washing bay 1',
                            'location' => 'Wash Bay 1',
                            'ip_address' => '192.168.1.11',
                            'opening_balance' => 2000.00,
                        ],
                        [
                            'code' => 'WASH_2',
                            'name' => 'Wash Station 2',
                            'description' => 'Till at washing bay 2',
                            'location' => 'Wash Bay 2',
                            'ip_address' => '192.168.1.12',
                            'opening_balance' => 2000.00,
                        ],
                        [
                            'code' => 'DETAIL',
                            'name' => 'Detailing Station',
                            'description' => 'Till at detailing section',
                            'location' => 'Detailing Area',
                            'ip_address' => '192.168.1.13',
                            'opening_balance' => 1500.00,
                        ],
                    ];

                    foreach ($tills as $tillData) {
                        $till = Till::firstOrCreate(
                            [
                                'tenant_id' => $tenant->id,
                                'code' => $tillData['code']
                            ],
                            array_merge($tillData, [
                                'branch_id' => $branch->id,
                                'is_active' => true,
                            ])
                        );

                        $this->command->info("Till created/updated: {$till->name} ({$till->code})");
                    }

                    $this->command->info('Test tills seeded successfully!');
                }
            );
        });
    }
}

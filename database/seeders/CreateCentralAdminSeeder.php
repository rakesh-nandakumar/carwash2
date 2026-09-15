<?php

namespace Database\Seeders;

use App\Models\CentralAdmin;
use Illuminate\Database\Seeder;

class CreateCentralAdminSeeder extends Seeder
{
    public function run(): void
    {
        CentralAdmin::create([
            'name' => 'Central Admin',
            'email' => 'admin@central.com',
            'password' => bcrypt('password'),
        ]);
    }
}

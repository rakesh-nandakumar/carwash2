<?php

namespace Database\Factories;

use App\Models\SupplierBankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierBankAccountFactory extends Factory
{
    protected $model = SupplierBankAccount::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) \Illuminate\Support\Str::ulid(),
            'supplier_id' => \App\Models\Supplier::factory(),
            'bank_name' => fake()->company(),
            'branch_name' => fake()->city(),
            'account_number' => fake()->bankAccountNumber(),
            'account_holder' => fake()->name(),
            'is_primary' => fake()->boolean(),
            'created_by' => 1,
        ];
    }
}

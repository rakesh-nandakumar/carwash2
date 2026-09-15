<?php

namespace Database\Factories;

use App\Models\SupplierAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierAddressFactory extends Factory
{
    protected $model = SupplierAddress::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) \Illuminate\Support\Str::ulid(),
            'supplier_id' => \App\Models\Supplier::factory(),
            'address_type' => fake()->randomElement(['billing', 'shipping']),
            'street_line1' => fake()->streetAddress(),
            'street_line2' => fake()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->country(),
            'is_primary' => fake()->boolean(),
            'created_by' => 1,
        ];
    }
}

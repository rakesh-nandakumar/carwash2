<?php

namespace Database\Factories;

use App\Models\SupplierContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierContactFactory extends Factory
{
    protected $model = SupplierContact::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) \Illuminate\Support\Str::ulid(),
            'supplier_id' => \App\Models\Supplier::factory(),
            'name' => fake()->name(),
            'designation' => fake()->jobTitle(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'is_primary' => fake()->boolean(),
            'created_by' => 1,
        ];
    }
}

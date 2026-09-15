<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) \Illuminate\Support\Str::ulid(),
            'name' => fake()->company(),
            'business_name' => fake()->company(),
            'registration_number' => fake()->numerify('REG-########'),
            'tax_number' => fake()->numerify('TAX-########'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'address' => fake()->address(),
            'payment_terms' => fake()->randomElement(['Net 30', 'Net 60', 'COD']),
            'credit_limit' => fake()->randomFloat(2, 1000, 50000),
            'outstanding_balance' => fake()->randomFloat(2, 0, 10000),
            'notes' => fake()->sentence(),
            'business_id' => 1, // Adjust based on your needs
            'active' => true,
            'created_by' => 1, // Adjust based on your needs
        ];
    }

    public function withContacts(int $count = 1): self
    {
        return $this->afterCreating(function (Supplier $supplier) use ($count) {
            \App\Models\SupplierContact::factory()->count($count)->create([
                'supplier_id' => $supplier->id,
            ]);
        });
    }

    public function withAddresses(int $count = 1): self
    {
        return $this->afterCreating(function (Supplier $supplier) use ($count) {
            \App\Models\SupplierAddress::factory()->count($count)->create([
                'supplier_id' => $supplier->id,
            ]);
        });
    }

    public function withBankAccounts(int $count = 1): self
    {
        return $this->afterCreating(function (Supplier $supplier) use ($count) {
            \App\Models\SupplierBankAccount::factory()->count($count)->create([
                'supplier_id' => $supplier->id,
            ]);
        });
    }

    public function blacklisted(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_blacklisted' => true,
            'blacklisted_reason' => fake()->sentence(),
            'blacklisted_at' => now(),
            'blacklisted_by' => 1,
        ]);
    }
}

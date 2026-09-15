<?php

namespace Database\Factories;

use App\Models\SupplierLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierLedgerFactory extends Factory
{
    protected $model = SupplierLedger::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) \Illuminate\Support\Str::ulid(),
            'supplier_id' => \App\Models\Supplier::factory(),
            'reference_type' => fake()->randomElement([\App\Models\GoodsReceipt::class, null]),
            'reference_id' => fake()->randomNumber(),
            'reference_number' => fake()->numerify('GRN-####-######'),
            'ledger_entry_type_id' => $this->getLedgerEntryTypeId(fake()->randomElement(['debit', 'credit'])),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'balance_after' => fake()->randomFloat(2, 0, 50000),
            'note' => fake()->sentence(),
            'created_by' => 1,
            'created_at' => now(),
        ];
    }

    public function debit(): self
    {
        return $this->state(fn (array $attributes) => [
            'ledger_entry_type_id' => $this->getLedgerEntryTypeId('debit'),
        ]);
    }

    public function credit(): self
    {
        return $this->state(fn (array $attributes) => [
            'ledger_entry_type_id' => $this->getLedgerEntryTypeId('credit'),
        ]);
    }

    private function getLedgerEntryTypeId(string $type): ?int
    {
        $setting = \App\Models\Setting::where('group', 'ledger_entry_types')
            ->where('key', $type)
            ->first();

        return $setting ? (int) $setting->id : null;
    }
}

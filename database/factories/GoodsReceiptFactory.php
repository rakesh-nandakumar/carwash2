<?php

namespace Database\Factories;

use App\Models\GoodsReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceiptFactory extends Factory
{
    protected $model = GoodsReceipt::class;

    public function definition(): array
    {
        return [
            'grn_number' => 'GRN-' . date('Y') . '-' . str_pad(fake()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
            'supplier_id' => \App\Models\Supplier::factory(),
            'purchase_order_id' => null,
            'reference' => fake()->optional()->numerify('REF-########'),
            'status_id' => null, // Will be set in states
            'note' => fake()->optional()->sentence(),
            'received_by' => 1,
            'received_at' => now(),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => $this->getStatusId('draft'),
        ]);
    }

    public function confirmed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => $this->getStatusId('confirmed'),
            'confirmed_by' => 1,
            'confirmed_at' => now(),
        ]);
    }

    public function deleted(): self
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => $this->getStatusId('deleted'),
            'deleted_by' => 1,
            'deleted_at' => now(),
        ]);
    }

    public function withItems(int $count = 1): self
    {
        return $this->afterCreating(function (GoodsReceipt $grn) use ($count) {
            \App\Models\GoodsReceiptItem::factory()->count($count)->create([
                'goods_receipt_id' => $grn->id,
            ]);
        });
    }

    private function getStatusId(string $status): ?int
    {
        $setting = \App\Models\Setting::where('group', 'grn_statuses')
            ->where('key', $status)
            ->first();

        return $setting ? (int) $setting->id : null;
    }
}

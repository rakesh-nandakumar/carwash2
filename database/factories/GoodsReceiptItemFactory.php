<?php

namespace Database\Factories;

use App\Models\GoodsReceiptItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceiptItemFactory extends Factory
{
    protected $model = GoodsReceiptItem::class;

    public function definition(): array
    {
        return [
            'goods_receipt_id' => \App\Models\GoodsReceipt::factory(),
            'product_id' => \App\Models\Product::factory(),
            'quantity' => fake()->randomFloat(3, 1, 100),
            'unit_cost' => fake()->randomFloat(2, 10, 1000),
            'sale_price' => fake()->randomFloat(2, 20, 2000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

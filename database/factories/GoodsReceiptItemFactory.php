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
            'quantity' => fake()->numberBetween(1, 100),
            'unit_cost' => fake()->randomFloat(2, 10, 100),
            'total_cost' => 0,
            'batch_number' => fake()->optional()->numerify('BATCH-####'),
            'expired_date' => fake()->optional()->dateTimeBetween('+1 month', '+2 years'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

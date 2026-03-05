<?php

namespace Database\Factories;

use App\Models\SaleOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleOrderItemFactory extends Factory
{
    protected $model = SaleOrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 10, 100);
        $discount = 0;
        $totalPrice = ($quantity * $unitPrice) - $discount;

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'total_price' => $totalPrice,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 100);
        $unitPrice = fake()->randomFloat(2, 10, 100);
        $discount = 0;
        $totalPrice = ($quantity * $unitPrice) - $discount;

        return [
            'quantity' => $quantity,
            'received_quantity' => 0,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'total_price' => $totalPrice,
        ];
    }
}

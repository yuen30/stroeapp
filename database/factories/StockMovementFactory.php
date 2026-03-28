<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SaleOrder;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'created_by' => User::factory(),
            'type' => StockMovementType::Purchase,
            'quantity' => fake()->numberBetween(1, 100),
            'stock_before' => fake()->numberBetween(0, 100),
            'stock_after' => fake()->numberBetween(0, 100),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function purchase(?PurchaseOrder $purchaseOrder = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovementType::Purchase,
            'purchase_order_id' => $purchaseOrder?->id,
            'goods_receipt_id' => null,
            'sale_order_id' => null,
        ]);
    }

    public function sale(?SaleOrder $saleOrder = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovementType::Sale,
            'purchase_order_id' => null,
            'goods_receipt_id' => null,
            'sale_order_id' => $saleOrder?->id,
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovementType::Adjustment,
            'purchase_order_id' => null,
            'goods_receipt_id' => null,
            'sale_order_id' => null,
        ]);
    }
}

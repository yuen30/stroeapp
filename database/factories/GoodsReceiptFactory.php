<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceiptFactory extends Factory
{
    protected $model = GoodsReceipt::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'supplier_id' => Supplier::factory(),
            'purchase_order_id' => null,
            'created_by' => User::factory(),
            'receipt_number' => fake()->unique()->numerify('GR-####'),
            'supplier_delivery_no' => fake()->optional()->numerify('SDN-####'),
            'is_standalone' => false,
            'document_date' => now(),
            'status' => OrderStatus::Confirmed,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function standalone(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_standalone' => true,
            'purchase_order_id' => null,
        ]);
    }

    public function fromPurchaseOrder(PurchaseOrder $purchaseOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'is_standalone' => false,
        ]);
    }
}

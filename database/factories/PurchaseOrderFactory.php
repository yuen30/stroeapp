<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'supplier_id' => Supplier::factory(),
            'created_by' => User::factory(),
            'order_number' => fake()->unique()->numerify('PO-####'),
            'order_date' => now(),
            'expected_date' => now()->addDays(7),
            'status' => OrderStatus::Draft,
            'payment_status_id' => PaymentStatus::first()?->id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 0,
            'payment_method_id' => PaymentMethod::first()?->id,
            'delivery_address' => fake()->address(),
            'contact_person' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'reference_number' => fake()->optional()->numerify('REF-####'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

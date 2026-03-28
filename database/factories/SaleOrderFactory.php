<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\SaleOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleOrderFactory extends Factory
{
    protected $model = SaleOrder::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'customer_id' => null,
            'created_by' => User::factory(),
            'salesman_id' => null,
            'invoice_number' => fake()->unique()->numerify('SO-####'),
            'order_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => OrderStatus::Draft,
            'payment_status_id' => PaymentStatus::first()?->id,
            'payment_method_id' => PaymentMethod::first()?->id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 0,
        ];
    }
}

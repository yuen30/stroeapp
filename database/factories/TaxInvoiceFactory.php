<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SaleOrder;
use App\Models\TaxInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxInvoiceFactory extends Factory
{
    protected $model = TaxInvoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 10000);
        $vatRate = 7.00;
        $vatAmount = $subtotal * ($vatRate / 100);
        $totalAmount = $subtotal + $vatAmount;

        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'customer_id' => Customer::factory(),
            'sale_order_id' => null,
            'created_by' => User::factory(),
            'tax_invoice_number' => fake()->unique()->numerify('TAX-####'),
            'document_date' => now(),
            'customer_name' => fake()->company(),
            'customer_tax_id' => fake()->numerify('##############'),
            'customer_address_line1' => fake()->address(),
            'customer_address_line2' => fake()->secondaryAddress(),
            'customer_amphoe' => fake()->city(),
            'customer_province' => fake()->state(),
            'customer_postal_code' => fake()->postcode(),
            'customer_is_head_office' => true,
            'customer_branch_no' => null,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forSaleOrder(SaleOrder $saleOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_order_id' => $saleOrder->id,
            'customer_id' => $saleOrder->customer_id,
            'subtotal' => $saleOrder->subtotal,
            'vat_amount' => $saleOrder->vat_amount,
            'total_amount' => $saleOrder->total_amount,
        ]);
    }
}

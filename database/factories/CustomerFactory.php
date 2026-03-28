<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => fake()->unique()->numerify('CUST-####'),
            'name' => fake()->company(),
            'address_0' => fake()->address(),
            'address_1' => fake()->secondaryAddress(),
            'amphoe' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'tel' => fake()->phoneNumber(),
            'fax' => fake()->optional()->phoneNumber(),
            'tax_id' => fake()->numerify('##############'),
            'credit_limit' => fake()->numberBetween(10000, 100000),
            'credit_days' => fake()->numberBetween(0, 90),
            'vat_rate' => 7,
            'is_head_office' => true,
            'branch_no' => null,
            'is_active' => true,
        ];
    }
}

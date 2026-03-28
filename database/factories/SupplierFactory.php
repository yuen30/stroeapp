<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => fake()->unique()->numerify('SUPP-####'),
            'name' => fake()->company(),
            'contact_name' => fake()->name(),
            'address_0' => fake()->address(),
            'address_1' => fake()->secondaryAddress(),
            'amphoe' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'tel' => fake()->phoneNumber(),
            'fax' => fake()->optional()->phoneNumber(),
            'tax_id' => fake()->numerify('##############'),
            'is_active' => true,
        ];
    }
}

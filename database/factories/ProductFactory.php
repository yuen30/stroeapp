<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => fake()->unique()->numerify('PROD-####'),
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'category_id' => null,
            'unit_id' => Unit::factory(),
            'brand_id' => Brand::factory(),
            'stock_quantity' => 100,
            'min_stock' => 10,
            'max_stock' => 200,
            'cost_price' => fake()->randomFloat(2, 10, 100),
            'selling_price' => fake()->randomFloat(2, 20, 150),
            'is_active' => true,
        ];
    }
}

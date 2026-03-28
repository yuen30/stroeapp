<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => fake()->unique()->numerify('CAT-####'),
            'name' => fake()->randomElement(['อะไหล่เครื่องยนต์', 'น้ำมันเครื่อง', 'ยางรถยนต์', 'แบตเตอรี่', 'ผ้าเบรก']),
            'description' => fake()->optional()->sentence(),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}

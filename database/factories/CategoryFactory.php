<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('CAT-####'),
            'name' => fake()->randomElement(['อะไหล่เครื่องยนต์', 'น้ำมันเครื่อง', 'ยางรถยนต์', 'แบตเตอรี่', 'ผ้าเบรก', 'ชิ้นส่วนอะไหล่', 'อุปกรณ์ตกแต่ง']),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}

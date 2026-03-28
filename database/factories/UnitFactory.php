<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'name' => fake()->unique()->randomElement(['ชิ้น', 'กล่อง', 'แพ็ค', 'โหล']).'-'.$counter,
            'code' => fake()->unique()->numerify('UNIT-####'),
            'is_active' => true,
        ];
    }
}

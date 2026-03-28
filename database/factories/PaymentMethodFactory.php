<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'code' => 'PM-'.$this->faker->unique()->randomNumber(4),
            'name' => $this->faker->randomElement(['เงินสด', 'โอนผ่านธนาคาร', 'บัตรเครดิต', 'QR Code']),
            'is_active' => true,
        ];
    }
}

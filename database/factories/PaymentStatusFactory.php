<?php

namespace Database\Factories;

use App\Models\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentStatusFactory extends Factory
{
    protected $model = PaymentStatus::class;

    public function definition(): array
    {
        $codes = ['PENDING', 'PAID', 'PARTIAL', 'OVERDUE', 'CANCELLED'];

        return [
            'code' => $this->faker->unique()->randomElement($codes),
            'name' => $this->faker->unique()->word(),
            'is_active' => true,
        ];
    }
}

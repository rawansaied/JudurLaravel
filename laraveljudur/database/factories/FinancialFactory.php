<?php

namespace Database\Factories;

use App\Models\Financial;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialFactory extends Factory
{
    protected $model = Financial::class;

    public function definition()
    {
        return [
            'donor_id' => \App\Models\Donor::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'currency' => $this->faker->currencyCode(),
            'payment_method' => $this->faker->randomElement(['Credit Card', 'Bank Transfer', 'Cash']),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\ItemStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemStatusFactory extends Factory
{

    public function definition()
    {
        return [
            'status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
        ];
    }
}

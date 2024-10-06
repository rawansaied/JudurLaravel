<?php

namespace Database\Factories;

use App\Models\LandStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class LandStatusFactory extends Factory
{
    protected $model = LandStatus::class;

    public function definition()
    {
        return [
            'status' => $this->faker->randomElement(['Available', 'Under Inspection', 'Donated']),
        ];
    }
}

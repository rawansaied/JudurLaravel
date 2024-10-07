<?php

namespace Database\Factories;

use App\Models\Donor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonorFactory extends Factory
{
    protected $model = Donor::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'donor_id_number' => $this->faker->uuid(),
        ];
    }
}

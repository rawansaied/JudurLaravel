<?php

namespace Database\Factories;

use App\Models\Land;
use Illuminate\Database\Eloquent\Factories\Factory;

class LandFactory extends Factory
{
    protected $model = Land::class;

    public function definition()
    {
        return [
            'donor_id' => \App\Models\Donor::factory(),
            'description' => $this->faker->text(200),
            'land_size' => $this->faker->randomFloat(2, 1, 100),
            'address' => $this->faker->address(),
            'proof_of_ownership' => $this->faker->word(),
            'status_id' => $this->faker->numberBetween(1, 3),
        ];
    }
}

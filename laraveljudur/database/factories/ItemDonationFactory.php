<?php

namespace Database\Factories;

use App\Models\ItemDonation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemDonationFactory extends Factory
{
    protected $model = ItemDonation::class;

    public function definition()
    {
        return [
            'donor_id' => \App\Models\Donor::factory(),
            'item_name' => $this->faker->word(),
            'value' => $this->faker->randomFloat(2, 10, 1000),
            'is_valuable' => $this->faker->boolean(),
            'condition' => $this->faker->randomElement(['New', 'Used', 'Refurbished']),
            'status_id' => $this->faker->numberBetween(1, 3),
        ];
    }
}

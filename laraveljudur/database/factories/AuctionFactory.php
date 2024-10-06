<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\ItemDonation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition()
    {
        return [
            'item_id' => ItemDonation::factory(), 
            'auction_status_id' => $this->faker->numberBetween(1, 4),  
            'start_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'end_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'starting_price' => $this->faker->randomFloat(2, 10, 1000), 
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
        ];
    }
}

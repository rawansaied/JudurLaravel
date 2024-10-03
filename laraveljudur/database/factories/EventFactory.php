<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'land_id' => \App\Models\Land::factory(),
            'description' => $this->faker->text(200),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'expected_organizer_number' => $this->faker->numberBetween(10, 100),
            'status' => $this->faker->randomElement(['Scheduled', 'Completed', 'Cancelled']),
            'image' => $this->faker->imageUrl(400, 300, 'events', true), 
            'location' => $this->faker->city(), 
            'duration' => $this->faker->randomElement(['30 minutes', '1 hour', '2 hours']),
            'people_helped' => $this->faker->numberBetween(0, 1000), 
            'goods_distributed' => $this->faker->randomFloat(2, 0, 10000), 
        ];
    }
}

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
        ];
    }
}

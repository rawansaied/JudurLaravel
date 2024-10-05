<?php

namespace Database\Factories;

use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerFactory extends Factory
{
    protected $model = Volunteer::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'skills' => $this->faker->text(50),
            'availability' => $this->faker->randomElement(['Weekdays', 'Weekends', 'Anytime']),
            'aim' => $this->faker->sentence(),
            'volunteer_status' => $this->faker->numberBetween(1, 3),
            'examiner' => $this->faker->boolean(), 

        ];
    }
}

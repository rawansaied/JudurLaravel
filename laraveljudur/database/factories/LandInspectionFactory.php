<?php

namespace Database\Factories;

use App\Models\Land;
use App\Models\LandInspection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LandInspectionFactory extends Factory
{
    protected $model = LandInspection::class;

    public function definition()
    {
        return [
            'land_id' => Land::factory(), 
            'date' => $this->faker->date(),
            'examiner_id' => User::factory(), 
            'hygiene' => $this->faker->word,
            'capacity' => $this->faker->numberBetween(1, 100), 
            'electricity_supply' => $this->faker->boolean,
            'general_condition' => $this->faker->sentence,
            'photo_path' => $this->faker->imageUrl(), 
        ];
    }
}

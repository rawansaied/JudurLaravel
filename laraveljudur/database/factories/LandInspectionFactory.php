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
            'summary' => $this->faker->paragraph(), // New field for summary
            'suggestions' => json_encode($this->generateSuggestions()),
        ];
    }
    private function generateSuggestions()
    {
        $suggestions = [
            "Consider adding more seating areas for participants.",
            "Improving access to water facilities would enhance the experience.",
            "Increase visibility and signage for easier navigation.",
            "Ensure regular maintenance of the site to keep it in good condition.",
            "Provide more bins for waste disposal to maintain cleanliness."
        ];

        // Return a random subset of suggestions
        return $this->faker->randomElements($suggestions, $this->faker->numberBetween(1, count($suggestions)));
    }
}

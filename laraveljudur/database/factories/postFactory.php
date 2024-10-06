<?php
namespace Database\Factories;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class postFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph, // Add content field
            'image' => $this->faker->imageUrl(),
            'category' => $this->faker->randomElement(['News', 'feeding-events', 'auction']), // Add category field with random choice
           
        ];
    }
}

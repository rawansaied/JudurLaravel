<?php

namespace Database\Factories;

use App\Models\Examiner;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExaminerFactory extends Factory
{
    protected $model = Examiner::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),  
            'education' => $this->faker->randomElement(['Bachelor\'s Degree', 'Master\'s Degree', 'PhD']),
            'reason' => $this->faker->text(100),
            'examiner_status' => $this->faker->numberBetween(1, 3),

        ];
    }
}

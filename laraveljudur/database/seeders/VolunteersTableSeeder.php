<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VolunteersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('volunteers')->insert([
            [
                'user_id' => 3, // Assuming user_id 3 is a Volunteer
                'skills' => 'First Aid, Cooking',
                'availability' => 'Weekends',
                'volunteer_status' => 1, // Assuming status 1 is Active
                'aim' => 'To help the community',
            ],
            // Add more volunteers as needed
        ]);
    }
}

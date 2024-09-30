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
                'user_id' => 2,
                'skills' => 'Event Planning, Organizing',
                'availability' => 'Weekends',
                'aim' => 'To support community events.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

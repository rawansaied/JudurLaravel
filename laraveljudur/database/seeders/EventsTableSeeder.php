<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('events')->insert([
            [
                'title' => 'Food Festival',
                'land_id' => 1,  // Make sure this ID exists in the 'lands' table
                'description' => 'Annual food festival to support the community.',
                'date' => now()->addDays(30),
                'expected_organizer_number' => 10,
                'status' => 'Planned',
                'time' => '10:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

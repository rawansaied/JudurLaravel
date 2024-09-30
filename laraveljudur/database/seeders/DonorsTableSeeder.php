<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonorsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('donors')->insert([
            [
                'user_id' => 2, // Assuming user_id 2 is a Donor
                'donor_id_number' => 'DON123456',
            ],
            // Add more donors as needed
        ]);
    }
}

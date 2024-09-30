<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandStatusesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('land_statuses')->insert([
            ['name' => 'Available'],
            ['name' => 'Occupied'],
            // Add more statuses as needed
        ]);
    }
}

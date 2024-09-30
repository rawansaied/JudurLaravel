<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VolunteerStatusesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('volunteer_statuses')->insert([
            ['name' => 'Accepted'],
            ['name' => 'Rejected'],
            ['name' => 'Pending'],
        ]);
    }
}

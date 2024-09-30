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
                'user_id' => 3,
                'donor_id_number' => 'D987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

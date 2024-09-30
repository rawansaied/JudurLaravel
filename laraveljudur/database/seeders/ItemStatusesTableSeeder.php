<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemStatusesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('item_statuses')->insert([
            ['status' => 'New'],
            ['status' => 'Used'],
            // Add more statuses as needed
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemDonationsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('item_donations')->insert([
            [
                'donor_id' => 1,  // Ensure this donor exists in the 'donors' table
                'item_name' => '50kg of Rice',
                'value' => 150.00,
                'is_valuable' => true,
                'condition' => 'New',
                'status_id' => 1,  // Ensure this status exists in the 'item_statuses' table
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

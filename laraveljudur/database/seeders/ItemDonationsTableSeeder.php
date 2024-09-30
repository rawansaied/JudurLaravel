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
                'donor_id' => 1, // Assuming donor_id 1 exists
                'item_name' => 'Old Laptop',
                'value' => 150.00,
                'is_valuable' => true,
                'condition' => 'Good',
                'status_id' => 1, // Assuming status_id 1 is New
            ],
            // Add more items as needed
        ]);
    }
}

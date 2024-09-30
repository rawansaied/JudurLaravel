<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('lands')->insert([
            [
                'donor_id' => 1, // Assuming donor_id 1 exists
                'description' => 'A fertile land suitable for farming',
                'land_size' => 10.5,
                'address' => '123 Main St, Springfield',
                'proof_of_ownership' => 'ownership_document.pdf',
                'status_id' => 1, // Assuming status_id 1 is Available
            ],
            // Add more lands as needed
        ]);
    }
}

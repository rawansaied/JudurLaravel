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
                'donor_id' => 1,  // Ensure that a donor with ID 1 exists in the 'donors' table
                'description' => 'A plot of land in the countryside.',
                'land_size' => 5000.00,
                'address' => '123 Countryside Lane',
                'proof_of_ownership' => 'landproof1.pdf',
                'status_id' => 1,  // Ensure that a status with ID 1 exists in the 'land_statuses' table
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

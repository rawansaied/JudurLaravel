<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('financials')->insert([
            [
                'donor_id' => 1,
                'amount' => 1000.00,
                'currency' => 'USD',
                'payment_method' => 'Credit Card',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}


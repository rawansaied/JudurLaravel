<?php

namespace Database\Seeders;

use App\Models\Treasury;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreasurySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Treasury::updateOrCreate(
            ['id' => 1], 
            ['money' => 0]
        );    }
}

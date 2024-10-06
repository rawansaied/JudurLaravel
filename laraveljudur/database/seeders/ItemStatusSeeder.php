<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('item_statuses')->insert([
            ['status' => 'pending'],
            ['status' => 'accepted'],
            ['status' => 'rejected'],
        ]);
    }
}

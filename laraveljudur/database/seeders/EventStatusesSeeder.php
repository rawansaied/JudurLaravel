<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('event_statuses')->insert([
            ['name' => 'Preparing'],
            ['name' => 'Scheduled'],
            ['name' => 'Completed'],
            ['name' => 'Cancelled'],
        ]);

      }
}

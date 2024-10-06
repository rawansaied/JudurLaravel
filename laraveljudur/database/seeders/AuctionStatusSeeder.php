<?php

namespace Database\Seeders;

use App\Models\AuctionStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuctionStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'Upcoming'],
            ['name' => 'Ongoing'],
            ['name' => 'Completed'],
            ['name' => 'Cancelled'],
        ];

        foreach ($statuses as $status) {
            AuctionStatus::create($status);
        }
    }
}

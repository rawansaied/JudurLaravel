<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ItemDonation;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Donor;
use App\Models\Event;
use App\Models\Examiner;
use App\Models\Financial;
use App\Models\Land;
use App\Models\LandInspection;
use App\Models\Notification;
use App\Models\Volunteer;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(ExaminerStatusesSeeder::class);
        $this->call(EventStatusesSeeder::class);
        User::factory(5)->create();

        ItemDonation::factory(5)->create();

        Auction::factory(3)->create();

        Bid::factory(2)->create();

        Event::factory(2)->create();
        Land::factory(2)->create();
        Notification::factory(2)->create();
        Volunteer::factory(2)->create();
        Donor::factory(2)->create();
        LandInspection::factory(2)->create();
        Financial::factory(2)->create();
        Examiner::factory(3)->create();

    }
}

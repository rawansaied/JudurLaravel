<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ItemDonation;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Donor;
use App\Models\Event;
use App\Models\Financial;
use App\Models\Land;
use App\Models\LandInspection;
use App\Models\Notification;
use App\Models\Volunteer;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
<<<<<<< HEAD
        $this->call([
            // RolesTableSeeder::class,              // Roles table first (used by Users table)
            // VolunteerStatusesTableSeeder::class,  // Volunteer statuses table
            // LandStatusesTableSeeder::class,       // Land statuses table
            // ItemStatusesTableSeeder::class,       // Item statuses table
            UsersTableSeeder::class,              // Users table next (depends on Roles)
            DonorsTableSeeder::class,             // Donors table (depends on Users)
            VolunteersTableSeeder::class,         // Volunteers table (depends on Users and VolunteerStatuses)
            LandsTableSeeder::class,              // Lands table (depends on Donors and LandStatuses)
            ItemDonationsTableSeeder::class,      // Item Donations table (depends on Donors and ItemStatuses)
        ]);
=======
        // Seed roles
        $this->call(RoleSeeder::class);

        $this->call(StatusSeeder::class);

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

>>>>>>> omar
    }
}

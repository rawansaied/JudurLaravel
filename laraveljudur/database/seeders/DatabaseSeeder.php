<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesTableSeeder::class,              // Roles table first (used by Users table)
            VolunteerStatusesTableSeeder::class,  // Volunteer statuses table
            LandStatusesTableSeeder::class,       // Land statuses table
            ItemStatusesTableSeeder::class,       // Item statuses table
            UsersTableSeeder::class,              // Users table next (depends on Roles)
            DonorsTableSeeder::class,             // Donors table (depends on Users)
            VolunteersTableSeeder::class,         // Volunteers table (depends on Users and VolunteerStatuses)
            LandsTableSeeder::class,              // Lands table (depends on Donors and LandStatuses)
            ItemDonationsTableSeeder::class,      // Item Donations table (depends on Donors and ItemStatuses)
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesTableSeeder::class,                     // Corrected seeder for the 'roles' table
            UsersTableSeeder::class,               // Seeder for the 'users' table
            VolunteerStatusesTableSeeder::class,   // Seeder for the 'volunteer_statuses' table
            VolunteersTableSeeder::class,          // Seeder for the 'volunteers' table
            DonorsTableSeeder::class,              // Seeder for the 'donors' table
            ItemDonationsTableSeeder::class,       // Seeder for the 'item_donations' table
                  // Seeder for the 'item_statuses' table
            FinancialsTableSeeder::class,          // Seeder for the 'financials' table
            LandsTableSeeder::class,               // Seeder for the 'lands' table
                      // Seeder for the 'events' table
            NotificationsTableSeeder::class,       // Seeder for the 'notifications' table
              
                     // Seeder for the 'bids' table
        ]);
    }
}

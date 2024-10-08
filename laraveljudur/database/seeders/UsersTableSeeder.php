<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Default User',
                'email' => 'user1@example.com',
                'password' => Hash::make('password'),
                'role_id' => 1, // Assuming role_id 1 is for Admin
                'age' => 30,
                'phone' => '1234567890',
            ],
            // Add more users as needed
        ]);
    }
}


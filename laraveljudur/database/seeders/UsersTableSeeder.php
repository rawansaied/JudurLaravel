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
                'name' => 'Admin User',
                'email' => 'admin064769@example.com',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'age' => 35,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Volunteer User',
                'email' => 'volunteer156@example.com',
                'password' => Hash::make('password'),
                'role_id' => 2,
                'age' => 27,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Donor User',
                'email' => 'donor9406@example.com',
                'password' => Hash::make('password'),
                'role_id' => 3,
                'age' => 45,
                
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

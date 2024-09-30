<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = ['Admin', 'Doner', 'Volunteer', 'User', 'Organizer', 'Mentor'];

        foreach ($roles as $role) {
            \App\Models\Role::create(['name' => $role]);
        }
    }
}
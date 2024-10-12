<?php

namespace Database\Seeders;

use App\Models\ItemStatus;
use Illuminate\Database\Seeder;
use App\Models\LandStatus;
use App\Models\VolunteerStatus;
use Illuminate\Container\Attributes\DB;

class StatusSeeder extends Seeder
{
    public function run()
    {
        LandStatus::create(['name' => 'Pending']);
        LandStatus::create(['name' => 'Accepted']);
        LandStatus::create(['name' => 'Rejected']);
        LandStatus::create(['name' => 'Scheduled']);

        VolunteerStatus::create(['name' => 'Pending']);
        VolunteerStatus::create(['name' => 'Accepted']);
        VolunteerStatus::create(['name' => 'Rejected']);

        ItemStatus::create(['status' => 'Pending']);
        ItemStatus::create(['status' => 'Accepted']);
        ItemStatus::create(['status' => 'Rejected']);
        ItemStatus::create(['status' => 'Normal']);

    }
}

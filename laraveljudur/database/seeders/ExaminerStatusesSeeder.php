<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExaminerStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('examiner_statuses')->insert([
            ['name' => 'Pending'],
            ['name' => 'Accepted'],
            ['name' => 'Rejected'],
        ]);
    }
}

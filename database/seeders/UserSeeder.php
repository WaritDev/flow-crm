<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::first();

        // Manager
        User::create([
            'name' => 'สมชาย สายบริหาร', 'email' => 'manager@thaicrm.com',
            'password' => bcrypt('password'), 'role' => 'manager', 'team_id' => $team->id
        ]);

        // Sales (use Fake kub P)
        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'name' => fake()->name(), 'email' => "sales$i@thaicrm.com",
                'password' => bcrypt('password'), 'role' => 'sales', 'team_id' => $team->id
            ]);
        }
    }
}

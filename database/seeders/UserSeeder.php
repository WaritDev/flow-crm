<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use App\Models\Organization;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org = Organization::first();
        $team = Team::where('organization_id', $org->id)->first();

        // Manager
        User::create([
            'organization_id' => $org->id,
            'name' => 'สมชาย สายบริหาร', 'email' => 'manager@thaicrm.com',
            'password' => bcrypt('password'), 'role' => 'manager', 'team_id' => $team->id
        ]);

        // Sales (use Fake kub P)
        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'organization_id' => $org->id,
                'name' => fake()->name(), 'email' => "sales$i@thaicrm.com",
                'password' => bcrypt('password'), 'role' => 'sales', 'team_id' => $team->id
            ]);
        }
    }
}

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
        $team1 = Team::where('organization_id', 1)->first();
        $team2 = Team::where('organization_id', 2)->first();

        // Manager
        User::create([
            'organization_id' => 1,
            'name' => 'Somchai Yachai', 'email' => 'manager@org1.com',
            'password' => bcrypt('password'), 'role' => 'manager'
        ]);

        User::create([
            'organization_id' => 2,
            'name' => 'Anusit Srikirin', 'email' => 'manager@org2.com',
            'password' => bcrypt('password'), 'role' => 'manager'
        ]);

        // sales (use Fake kub P)
        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'organization_id' => 1,
                'name' => fake()->firstName() . ' ' . fake()->lastName(), 'email' => "sales$i@org1.com",
                'password' => bcrypt('password'), 'role' => 'sales', 'team_id' => $team1->id
            ]);
        }

        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'organization_id' => 2,
                'name' => fake()->firstName() . ' ' . fake()->lastName(), 'email' => "sales$i@org2.com",
                'password' => bcrypt('password'), 'role' => 'sales', 'team_id' => $team2->id
            ]);
        }
    }
}

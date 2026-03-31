<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $org1 = Organization::query()->where('slug', 'clinic-s')->firstOrFail();
        $org2 = Organization::query()->where('slug', 'mala-a')->firstOrFail();

        $team1 = Team::query()->where('organization_id', $org1->id)->orderBy('id')->firstOrFail();
        $team2 = Team::query()->where('organization_id', $org2->id)->orderBy('id')->firstOrFail();

        User::firstOrCreate(
            ['email' => 'admin@flowcrm.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@org1.com'],
            [
                'organization_id' => $org1->id,
                'team_id' => $team1->id,
                'name' => 'Somchai Yachai',
                'password' => bcrypt('password'),
                'role' => 'manager',
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@org2.com'],
            [
                'organization_id' => $org2->id,
                'team_id' => $team2->id,
                'name' => 'Anusit Srikirin',
                'password' => bcrypt('password'),
                'role' => 'manager',
            ]
        );

        for ($i = 1; $i <= 4; $i++) {
            User::firstOrCreate(
                ['email' => "sales$i@org1.com"],
                [
                    'organization_id' => $org1->id,
                    'name' => fake()->firstName().' '.fake()->lastName(),
                    'password' => bcrypt('password'),
                    'role' => 'sales',
                    'team_id' => $team1->id,
                ]
            );
        }

        for ($i = 1; $i <= 4; $i++) {
            User::firstOrCreate(
                ['email' => "sales$i@org2.com"],
                [
                    'organization_id' => $org2->id,
                    'name' => fake()->firstName().' '.fake()->lastName(),
                    'password' => bcrypt('password'),
                    'role' => 'sales',
                    'team_id' => $team2->id,
                ]
            );
        }
    }
}

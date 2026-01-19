<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Team;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgA = Organization::create([
            'id' => 1,
            'name' => 'ร้านคลินิกความงาม A',
            'slug' => 'clinic-a'
        ]);
        $orgB = Organization::create([
            'id' => 2,
            'name' => 'ร้านคลินิกความงาม B',
            'slug' => 'clinic-b'
        ]);


        $this->seedOrganizationData($orgA);
        $this->seedOrganizationData($orgB);
    }

    private function seedOrganizationData($org)
    {

        $team = Team::create(['organization_id' => $org->id, 'name' => 'ทีมขายหลัก']);

        User::factory()->create([
            'organization_id' => $org->id,
            'team_id' => $team->id,
            'role' => 'manager'
        ]);
    }
}

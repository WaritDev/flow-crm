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
            'name' => 'Somhai Clinic',
            'slug' => 'clinic-s'
        ]);
        $orgB = Organization::create([
            'id' => 2,
            'name' => 'Aunsit Mala',
            'slug' => 'mala-a'
        ]);


        $this->seedOrganizationData($orgA);
        $this->seedOrganizationData($orgB);
    }

    private function seedOrganizationData($org)
    {
        $template = \App\Models\PipelineTemplate::firstOrCreate([
            'name' => 'Default Pipeline'
        ]);

        $team = Team::create([
            'organization_id' => $org->id,
            'name' => 'main sales team',
            'template_id' => $template->id
        ]);

        // Ensure template has stages if it was just created (fallback)
        if ($template->stages()->count() === 0) {
            $stages = [
                ['name' => 'สนใจ (Prospect)', 'position' => 1, 'is_won' => false],
                ['name' => 'ติดต่อแล้ว (Contacted)', 'position' => 2, 'is_won' => false],
                ['name' => 'เสนอราคา (Quoted)', 'position' => 3, 'is_won' => false],
                ['name' => 'ปิดการขาย (Won)', 'position' => 5, 'is_won' => true],
            ];
            foreach ($stages as $stage) {
                $template->stages()->create($stage);
            }
        }
    }
}

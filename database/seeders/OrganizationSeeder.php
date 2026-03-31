<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\PipelineTemplate;
use App\Models\Team;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $orgA = Organization::updateOrCreate(
            ['slug' => 'clinic-s'],
            [
                'name' => 'Somhai Clinic',
                'size' => 'medium',
                'description' => 'Leading beauty clinic — full-service treatments.',
                'invite_code' => 'CLINICA2026',
            ]
        );

        $orgB = Organization::updateOrCreate(
            ['slug' => 'mala-a'],
            [
                'name' => 'Aunsit Mala',
                'size' => 'small',
                'description' => 'Boutique clinic focused on personalized care.',
                'invite_code' => 'CLINICB2026',
            ]
        );

        $this->seedOrganizationData($orgA);
        $this->seedOrganizationData($orgB);
    }

    private function seedOrganizationData(Organization $org): void
    {
        $template = PipelineTemplate::query()
            ->whereNull('organization_id')
            ->where('name', 'Default Pipeline')
            ->firstOrFail();

        $team = Team::firstOrCreate(
            [
                'organization_id' => $org->id,
                'name' => 'main sales team',
            ],
            ['template_id' => $template->id]
        );

        if ($team->template_id === null) {
            $team->update(['template_id' => $template->id]);
        }

        if ($template->stages()->count() === 0) {
            $stages = [
                ['name' => 'Prospect', 'position' => 1, 'is_won' => false],
                ['name' => 'Contacted', 'position' => 2, 'is_won' => false],
                ['name' => 'Quoted', 'position' => 3, 'is_won' => false],
                ['name' => 'Closed Won', 'position' => 5, 'is_won' => true],
            ];
            foreach ($stages as $stage) {
                $template->stages()->create($stage);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\PipelineTemplate;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $template = PipelineTemplate::query()
            ->whereNull('organization_id')
            ->where('name', 'Default Pipeline')
            ->firstOrFail();

        $org1 = Organization::query()->where('slug', 'clinic-s')->first();
        $org2 = Organization::query()->where('slug', 'mala-a')->first();

        if ($org1) {
            Team::firstOrCreate(
                [
                    'organization_id' => $org1->id,
                    'name' => 'bangkok sales team',
                ],
                ['template_id' => $template->id]
            );
        }

        if ($org2) {
            Team::firstOrCreate(
                [
                    'organization_id' => $org2->id,
                    'name' => 'online sales team',
                ],
                ['template_id' => $template->id]
            );
        }
    }
}

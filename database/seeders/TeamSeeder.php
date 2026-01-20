<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Models\Organization;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = PipelineTemplate::first();
        $org = Organization::first();
        Team::create(['organization_id' => $org->id, 'name' => 'ทีมขายกรุงเทพ', 'template_id' => $template->id]);
        Team::create(['organization_id' => $org->id, 'name' => 'ทีมขายออนไลน์', 'template_id' => $template->id]);
    }
}

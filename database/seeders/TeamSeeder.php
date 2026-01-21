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
        Team::create(['organization_id' => 1, 'name' => 'bangkok sales team', 'template_id' => $template->id]);
        Team::create(['organization_id' => 2, 'name' => 'online sales team', 'template_id' => $template->id]);
    }
}

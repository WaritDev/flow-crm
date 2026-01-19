<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PipelineTemplate;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = PipelineTemplate::first();
        Team::create(['name' => 'ทีมขายกรุงเทพ', 'template_id' => $template->id]);
        Team::create(['name' => 'ทีมขายออนไลน์', 'template_id' => $template->id]);
    }
}

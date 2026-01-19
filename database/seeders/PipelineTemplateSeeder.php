<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PipelineTemplate;

class PipelineTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = PipelineTemplate::create(
            ['name' => 'Default Pipeline',]
        );

        $stages = [['name' => 'ลีดใหม่ (New Lead)', 'position' => 1, 'is_won' => false],
            ['name' => 'เจรจา/ยื่นกู้ (Negotiation)', 'position' => 3, 'is_won' => false],
            ['name' => 'ดีลหลุด (Lost)', 'position' => 5, 'is_won' => false]];

        foreach ($stages as $stage) {
            $template->stages()->create($stage);
        }
    }
}

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

        $stages = [
            ['name' => 'สนใจ (Prospect)', 'position' => 1, 'is_won' => false],
            ['name' => 'ติดต่อแล้ว (Contacted)', 'position' => 2, 'is_won' => false],
            ['name' => 'เสนอราคา (Quoted)', 'position' => 3, 'is_won' => false],
            ['name' => 'เจรจาต่อรอง (Negotiation)', 'position' => 4, 'is_won' => false],
            ['name' => 'ปิดการขาย (Won)', 'position' => 5, 'is_won' => true],
        ];

        foreach ($stages as $stage) {
            $template->stages()->create($stage);
        }
    }
}

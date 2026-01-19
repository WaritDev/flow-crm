<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PipelineStage;
use App\Models\Team;
use App\Models\LineScript;

class LineScriptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = PipelineStage::all();
        $team = Team::first();

        $scripts = [
            'New Lead' => 'สวัสดีครับคุณ [nickname] ขอบคุณที่สนใจโครงการนะครับ มิต้องการข้อมูลส่วนไหนเพิ่มไหมครับ?',
            'Site Visit' => 'คุณ [nickname] พรุ่งนี้เวลา 10 โมง สะดวกเจอกันที่โครงการไหมครับ? เดี๋ยวผมส่งโลเคชั่นให้นะครับ',
            'Negotiation' => 'สรุปเงื่อนไขและโปรโมชั่นประจำเดือนที่คุณ [nickname] จะได้รับ ตามรูปภาพที่ส่งให้นี้นะครับ',
        ];

        foreach ($stages as $stage) {
            foreach ($scripts as $key => $content) {
                if (str_contains($stage->name, $key)) {
                    LineScript::create(
                        [
                            'name' => $stage->name,
                            'stage_id' => $stage->id,
                            'team_id'  => $team->id,
                            'content' => $content,
                        ]
                    );
                }
            }
        }
    }
}

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
        $teams = Team::all();

        foreach ($teams as $team) {
            foreach ($stages as $stage) {
                $content = $this->scriptForStage($stage->name);

                if (!$content) {
                    $content = 'สวัสดีครับคุณ {nickname} ผมขอส่งรายละเอียดเพิ่มเติมเกี่ยวกับดีลของคุณ (มูลค่าโดยประมาณ {amount} บาท) รบกวนยืนยันขั้นตอนถัดไปได้ไหมครับ? หากสะดวกสามารถตอบกลับผ่านไลน์นี้ได้เลย: {line_id}';
                }

                LineScript::updateOrCreate(
                    [
                        'stage_id' => $stage->id,
                        'team_id' => $team->id,
                    ],
                    [
                        'name' => $stage->name,
                        'content' => $content,
                        'use_count' => 0,
                    ]
                );
            }
        }
    }

    private function scriptForStage(string $stageName): ?string
    {
        if (str_contains($stageName, 'สนใจ') || str_contains($stageName, 'Prospect')) {
            return 'สวัสดีครับคุณ {nickname} ขอบคุณที่สนใจโครงการครับ ผมขอข้อมูลเพิ่มเติม/นัดเวลาสั้นๆ ได้ไหมครับ? (ยอดดีลโดยประมาณ {amount} บาท) ถ้าสะดวกตอบกลับได้เลยผ่านไลน์นี้: {line_id}';
        }

        if (str_contains($stageName, 'ติดต่อแล้ว') || str_contains($stageName, 'Contacted')) {
            return 'ขอบคุณครับคุณ {nickname} สำหรับการติดต่อ วันนี้ผมขอสรุปขั้นตอนต่อไปและนัดเวลาพูดคุยให้เหมาะกับคุณครับ ยอดดีลโดยประมาณ {amount} บาท รบกวนยืนยันเวลาที่สะดวกได้เลย: {line_id}';
        }

        if (str_contains($stageName, 'เสนอราคา') || str_contains($stageName, 'Quoted')) {
            return 'สวัสดีครับคุณ {nickname} ผมส่งใบเสนอราคาให้แล้วนะครับ รบกวนตรวจสอบและบอกความคืบหน้าได้ไหมครับ? (มูลค่าประมาณ {amount} บาท) หากมีคำถามตอบกลับผ่านไลน์นี้ได้เลย: {line_id}';
        }

        if (str_contains($stageName, 'เจรจา') || str_contains($stageName, 'Negotiation')) {
            return 'สวัสดีครับคุณ {nickname} เพื่อความชัดเจน ผมขอสรุปเงื่อนไข/โปรโมชั่นที่เหมาะกับคุณ {customer_name} อีกครั้งครับ ยอดดีลโดยประมาณ {amount} บาท รบกวนยืนยันเอกสารขั้นตอนถัดไปได้ไหมครับ: {line_id}';
        }

        if (str_contains($stageName, 'ปิดการขาย') || str_contains($stageName, 'Won')) {
            return 'ยินดีด้วยครับคุณ {nickname} สำหรับขั้นตอนถัดไป ผมขอให้คุณยืนยันการเซ็นสัญญาและรับรายละเอียดการนัดหมายครับ ยอดดีลประมาณ {amount} บาท หากต้องการติดต่อเร่งด่วนตอบกลับไลน์นี้ได้เลย: {line_id}';
        }

        if (str_contains($stageName, 'สูญเสีย') || str_contains(mb_strtolower($stageName), 'lost')) {
            return 'สวัสดีครับคุณ {nickname} ผมขออนุญาตติดตามหลังปิดดีลนะครับ หากยังสนใจบริการ/ข้อเสนอเดิม ผมสามารถส่งรายละเอียดและทางเลือกเพิ่มเติมให้ได้เลยครับ ติดต่อกลับผ่านไลน์นี้: {line_id}';
        }

        return null;
    }
}

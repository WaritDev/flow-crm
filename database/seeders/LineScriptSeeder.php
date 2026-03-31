<?php

namespace Database\Seeders;

use App\Models\LineScript;
use App\Models\PipelineStage;
use App\Models\Team;
use Illuminate\Database\Seeder;

class LineScriptSeeder extends Seeder
{
    public function run(): void
    {
        $stages = PipelineStage::all();
        $teams = Team::all();

        foreach ($teams as $team) {
            foreach ($stages as $stage) {
                $content = $this->scriptForStage($stage->name);

                if (! $content) {
                    $content = 'Hi {nickname}, following up on your deal (about THB {amount}). Can you confirm the next step? Reply here on LINE: {line_id}';
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
        $sn = mb_strtolower($stageName);

        if (str_contains($stageName, 'สนใจ') || str_contains($sn, 'prospect') || str_contains($sn, 'lead') || str_contains($sn, 'inbound')) {
            return 'Hi {nickname}, thanks for your interest. Can I get a bit more context or a quick time to chat? Deal size roughly THB {amount}. Reply on LINE: {line_id}';
        }

        if (str_contains($stageName, 'ติดต่อแล้ว') || str_contains($sn, 'contact')) {
            return 'Hi {nickname}, thanks for connecting. I will confirm next steps and a time that works for you. Roughly THB {amount}. Reply on LINE: {line_id}';
        }

        if (str_contains($stageName, 'เสนอราคา') || str_contains($sn, 'quoted') || str_contains($sn, 'quote') || str_contains($sn, 'proposal')) {
            return 'Hi {nickname}, I sent the quote — please review and let me know where we stand (~THB {amount}). Questions welcome on LINE: {line_id}';
        }

        if (str_contains($stageName, 'เจรจา') || str_contains($sn, 'negotiation')) {
            return 'Hi {nickname}, summarizing terms for {customer_name} (~THB {amount}). Please confirm documents / next step on LINE: {line_id}';
        }

        if (str_contains($stageName, 'ปิดการขาย') || str_contains($sn, 'closed won') || (str_contains($sn, 'won') && ! str_contains($sn, 'lost'))) {
            return 'Hi {nickname}, congrats on moving forward. Please confirm signing and scheduling. Amount ~THB {amount}. Reach me on LINE: {line_id}';
        }

        if (str_contains($stageName, 'สูญเสีย') || (str_contains($sn, 'lost') && ! str_contains($sn, 'won'))) {
            return 'Hi {nickname}, checking in after we closed this one. If timing changes, I can resend options. LINE: {line_id}';
        }

        return null;
    }
}

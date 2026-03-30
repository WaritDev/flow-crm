<?php

namespace Database\Seeders;

use App\Models\PipelineTemplate;
use Illuminate\Database\Seeder;

/**
 * System templates: organization_id must stay null (visible to all orgs).
 * Stages follow common industry funnel naming (MAPP / MEDDPICC-inspired for SaaS, etc.).
 */
class PipelineTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Default Pipeline',
                'industry' => 'General B2B',
                'description' => 'เทมเพลตทั่วไปสำหรับทีมขาย B2B — Lead ถึงปิดดีล',
                'is_default' => true,
                'stages' => [
                    ['name' => 'สนใจ (Prospect)', 'position' => 1, 'is_won' => false],
                    ['name' => 'ติดต่อแล้ว (Contacted)', 'position' => 2, 'is_won' => false],
                    ['name' => 'คัดกรองคุณภาพ (Qualified)', 'position' => 3, 'is_won' => false],
                    ['name' => 'เสนอราคา (Quoted)', 'position' => 4, 'is_won' => false],
                    ['name' => 'เจรจาต่อรอง (Negotiation)', 'position' => 5, 'is_won' => false],
                    ['name' => 'ปิดการขาย (Won)', 'position' => 6, 'is_won' => true],
                ],
            ],
            [
                'name' => 'SaaS — Subscription sales',
                'industry' => 'SaaS',
                'description' => 'ลูกค้าซ้ำ / Subscription — โฟลว์คล้าย inbound + trial-to-paid',
                'is_default' => false,
                'stages' => [
                    ['name' => 'Lead / Inbound', 'position' => 1, 'is_won' => false],
                    ['name' => 'Discovery & qualification', 'position' => 2, 'is_won' => false],
                    ['name' => 'Demo / Pilot', 'position' => 3, 'is_won' => false],
                    ['name' => 'Proposal & security review', 'position' => 4, 'is_won' => false],
                    ['name' => 'Negotiation & commercials', 'position' => 5, 'is_won' => false],
                    ['name' => 'Closed Won — subscribed', 'position' => 6, 'is_won' => true],
                ],
            ],
            [
                'name' => 'Real estate — Buy / sell',
                'industry' => 'Real Estate',
                'description' => 'โฟลว์นิยม: inquiry → นัดดูทรัพย์ → offer → closing',
                'is_default' => false,
                'stages' => [
                    ['name' => 'New inquiry', 'position' => 1, 'is_won' => false],
                    ['name' => 'Qualified & pre-approved', 'position' => 2, 'is_won' => false],
                    ['name' => 'Showing / viewing', 'position' => 3, 'is_won' => false],
                    ['name' => 'Offer & acceptance', 'position' => 4, 'is_won' => false],
                    ['name' => 'Due diligence & financing', 'position' => 5, 'is_won' => false],
                    ['name' => 'Closed — transaction complete', 'position' => 6, 'is_won' => true],
                ],
            ],
            [
                'name' => 'Insurance — Personal lines',
                'industry' => 'Insurance',
                'description' => 'โฟลว์ agent: lead → needs analysis → quote → bind',
                'is_default' => false,
                'stages' => [
                    ['name' => 'Prospect', 'position' => 1, 'is_won' => false],
                    ['name' => 'Needs analysis (fact find)', 'position' => 2, 'is_won' => false],
                    ['name' => 'Quote presented', 'position' => 3, 'is_won' => false],
                    ['name' => 'Application submitted', 'position' => 4, 'is_won' => false],
                    ['name' => 'Underwriting', 'position' => 5, 'is_won' => false],
                    ['name' => 'Policy issued (Won)', 'position' => 6, 'is_won' => true],
                ],
            ],
        ];

        foreach ($definitions as $def) {
            $stages = $def['stages'];
            unset($def['stages']);

            $template = PipelineTemplate::firstOrCreate(
                [
                    'name' => $def['name'],
                    'organization_id' => null,
                ],
                array_merge($def, ['organization_id' => null])
            );

            if ($template->stages()->count() > 0) {
                continue;
            }

            foreach ($stages as $stage) {
                $template->stages()->create($stage);
            }
        }
    }
}

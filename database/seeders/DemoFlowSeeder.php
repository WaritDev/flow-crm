<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo สำหรับโชว์ฟลูว์: ทีมใหม่ที่ยังไม่มีลูกค้า/ดีล → ผูก pipeline ได้, สร้างลูกค้า/ดีลตาม Sales ใน SvelteKit ได้
 *
 * บัญชีทดลอง (หลัง db:seed):
 * - Manager org1: manager@org1.com / password — Blade: ทีม, pipeline templates, integrations
 * - Sales เต็มข้อมูล: sales1@org1.com / password — ลูกค้า+ดีลจาก seed ก่อนหน้า
 * - Sales ทีมสะอาด (ไม่มีดีล): demo.fresh@org1.com / password — invite org เดิมหรือ login หลัง seed
 *
 * รหัสเชิญองค์กร Somhai Clinic (สมัคร Sales ใน Laravel/Frontend): CLINICA2026
 * องค์กร Kasetsart Innovation Hub (Manager dashboard เดโม่): KU-DEMO2026
 */
class DemoFlowSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::query()->where('invite_code', 'CLINICA2026')->first();
        if (! $org) {
            return;
        }

        $template = PipelineTemplate::query()
            ->whereNull('organization_id')
            ->where('name', 'Default Pipeline')
            ->first();

        if (! $template) {
            return;
        }

        $team = Team::firstOrCreate(
            [
                'organization_id' => $org->id,
                'name' => 'Demo — ทีมใหม่ (ยังไม่มีดีล)',
            ],
            ['template_id' => $template->id]
        );

        if ($team->template_id === null) {
            $team->update(['template_id' => $template->id]);
        }

        $user = User::firstOrCreate(
            ['email' => 'demo.fresh@org1.com'],
            [
                'organization_id' => $org->id,
                'team_id' => $team->id,
                'name' => 'Demo Sales ทีมใหม่',
                'password' => bcrypt('password'),
                'role' => 'sales',
            ]
        );

        $user->forceFill([
            'organization_id' => $org->id,
            'team_id' => $team->id,
        ])->save();

        if ($user->wasRecentlyCreated || $user->customers()->count() === 0) {
            $demos = [
                ['name' => 'มาลี สุขสม', 'nickname' => 'มาลี', 'line_id' => 'line_demo_malee'],
                ['name' => 'สมชาย ใจดี', 'nickname' => 'ชาย', 'line_id' => 'line_demo_somchai'],
                ['name' => 'วิภา ศรีสุข', 'nickname' => 'แพร', 'line_id' => 'line_demo_prae'],
            ];
            foreach ($demos as $row) {
                Customer::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'team_id' => $team->id,
                        'user_id' => $user->id,
                        'line_id' => $row['line_id'],
                    ],
                    [
                        'name' => $row['name'],
                        'nickname' => $row['nickname'],
                        'province' => 'กรุงเทพฯ',
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}

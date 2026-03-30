<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ดีลสำหรับ demo Workflow 3 (Automated monitoring): open deals บน team_id 1 ที่ไม่ถูกแตะมาเกิน 48 ชม.
 *
 * เกณฑ์ API {@see \App\Http\Controllers\Api\Sales\SalesN8nController::inactiveDeals}:
 * won_at/lost_at ว่าง และ updated_at เก่ากว่า now - hours (ค่าเริ่มต้น 48)
 *
 * รัน: php artisan db:seed --class=MonitoringInactiveDemoSeeder
 */
class MonitoringInactiveDemoSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::query()->find(1);
        if (! $team) {
            $this->command?->warn('MonitoringInactiveDemoSeeder: team id 1 not found — skip.');

            return;
        }

        $sales = User::query()
            ->where('team_id', $team->id)
            ->where('role', 'sales')
            ->orderBy('id')
            ->first();

        if (! $sales) {
            $this->command?->warn('MonitoringInactiveDemoSeeder: no sales user on team 1 — skip.');

            return;
        }

        $team->load('pipelineTemplate.stages');
        $stage = $team->pipelineTemplate?->stages
            ?->first(fn ($s) => ! $s->is_won);

        if (! $stage) {
            $this->command?->warn('MonitoringInactiveDemoSeeder: no non-won pipeline stage — skip.');

            return;
        }

        $staleHours = 72;
        $updatedAt = now()->subHours($staleHours);
        $createdAt = now()->subHours($staleHours + 2);

        /** @var list<array{line_suffix: int, org: string, contact: string, nickname: string, deal_title: string}> $rows */
        $rows = [
            ['line_suffix' => 1, 'org' => 'คลินิกความงาม มินิมอล — สยาม', 'contact' => 'คุณปวีณา เจริญสุข', 'nickname' => 'หนิง', 'deal_title' => 'แพ็กโปรโมชันเลเซอร์หน้าใส รายไตรมาส'],
            ['line_suffix' => 2, 'org' => 'ร้านสปาและนวดแผนไทย สุขใจ ', 'contact' => 'คุณอรทัย มณีรัตน์', 'nickname' => 'หมวย', 'deal_title' => 'สัญญา corporate wellness 12 ครั้ง/เดือน'],
            ['line_suffix' => 3, 'org' => 'คลินิกทันตกรรม เดนท์แคร์ พระราม 9', 'contact' => 'คุณสมศักดิ์ วงศ์พัฒน์', 'nickname' => 'ต้น', 'deal_title' => 'เครื่องมือจัดฟันใส — ใบเสนอราคาแพ็กเกจ'],
            ['line_suffix' => 4, 'org' => 'ศูนย์ดูแลผิว Glow Skin Clinic', 'contact' => 'คุณมาลินี สุขประเสริฐ', 'nickname' => 'มิ้น', 'deal_title' => 'แพ็กหน้าใส + growth factor 10 ครั้ง'],
            ['line_suffix' => 5, 'org' => 'ร้านสปา ริมสวน ลาดพร้าว', 'contact' => 'คุณกันต์ เรืองแสง', 'nickname' => 'กันต์', 'deal_title' => 'จองห้อง VIP สำหรับอีเวนต์ลูกค้า VIP'],
            ['line_suffix' => 6, 'org' => 'คลินิกผิวหนังและเลเซอร์ เมืองทอง', 'contact' => 'คุณธารา ลือชา', 'nickname' => 'ตาล', 'deal_title' => 'แพ็กรักษาสิวเรื้อรัง + ติดตาม 6 เดือน'],
            ['line_suffix' => 7, 'org' => 'คลินิกกายภาพบำบัด ฟื้นฟูสุข', 'contact' => 'คุณนภัสสร ใจดี', 'nickname' => 'เป็ด', 'deal_title' => 'สัญญาบริษัทประกัน — ผู้ป่วยส่งตรวจรายเดือน'],
            ['line_suffix' => 8, 'org' => 'บริษัท เวลเนส เฮลท์แคร์ จำกัด', 'contact' => 'คุณวีรยุทธ์ ศรีสวัสดิ์', 'nickname' => 'เวล', 'deal_title' => 'โปรแพ็กตรวจสุขภาพพนักงาน 200 คน'],
        ];

        foreach ($rows as $row) {
            $n = $row['line_suffix'];
            $lineId = 'line_stale48_demo_t1_'.$n;

            $customer = Customer::firstOrCreate(
                [
                    'organization_id' => $team->organization_id,
                    'team_id' => $team->id,
                    'line_id' => $lineId,
                ],
                [
                    'user_id' => $sales->id,
                    'name' => $row['contact'],
                    'nickname' => $row['nickname'],
                    'province' => 'กรุงเทพฯ',
                    'status' => 'active',
                ]
            );

            $customer->forceFill([
                'user_id' => $sales->id,
                'name' => $row['contact'],
                'nickname' => $row['nickname'],
                'province' => 'กรุงเทพฯ',
                'status' => 'active',
            ])->save();

            $deal = Deal::firstOrCreate(
                ['customer_id' => $customer->id],
                [
                    'organization_id' => $team->organization_id,
                    'name' => $row['deal_title'],
                    'description' => 'องค์กร / สถานที่: '.$row['org'],
                    'user_id' => $sales->id,
                    'team_id' => $team->id,
                    'stage_id' => $stage->id,
                    'next_action' => 'โทรติดตามนัด / ทัก LINE หลังไม่มีความเคลื่อนไหว',
                    'next_action_date' => now()->addDay()->toDateString(),
                    'value' => 100000 + ($n * 11111),
                    'expected_close_date' => now()->addMonth()->toDateString(),
                    'won_at' => null,
                    'lost_at' => null,
                ]
            );

            $deal->forceFill([
                'organization_id' => $team->organization_id,
                'user_id' => $sales->id,
                'team_id' => $team->id,
                'stage_id' => $stage->id,
                'name' => $row['deal_title'],
                'description' => 'องค์กร / สถานที่: '.$row['org'],
                'next_action' => 'โทรติดตามนัด / ทัก LINE หลังไม่มีความเคลื่อนไหว',
                'won_at' => null,
                'lost_at' => null,
            ])->save();

            Deal::query()->whereKey($deal->id)->update([
                'updated_at' => $updatedAt,
                'created_at' => $createdAt,
            ]);
        }

        $this->command?->info(
            'MonitoringInactiveDemoSeeder: '.count($rows).' open deals on team_id '.$team->id
                .' with updated_at ≈ '.$staleHours.'h ago (owner '.$sales->email.').'
        );
    }
}

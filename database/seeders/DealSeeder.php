<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Team;
use Carbon\Carbon;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $now = Carbon::now();

        foreach ($customers as $customer) {
            $team = Team::find($customer->team_id);
            $template = $team?->pipelineTemplate;
            $stages = $template?->stages ?? collect();

            if ($stages->isEmpty()) {
                continue;
            }

            $stage = $stages->random();

            // ให้ next_action_date มีค่า Overdue / Today / Future สำหรับ demo Action Stream
            $bucket = fake()->randomElement(['overdue', 'today', 'future']);
            $timeHour = fake()->numberBetween(9, 17);
            $nextActionDate = match ($bucket) {
                'overdue' => $now->copy()->subDays(fake()->numberBetween(1, 5)),
                'today' => $now->copy(),
                default => $now->copy()->addDays(fake()->numberBetween(1, 5)),
            };
            $nextActionDate = $nextActionDate->setTime($timeHour, 0, 0);

            $nextActionsByStage = [
                'สนใจ' => 'ทักเพื่อขอข้อมูลเพิ่มเติมทาง LINE',
                'ติดต่อแล้ว' => 'นัดหมายเพื่อคุยรายละเอียดให้ชัดเจน',
                'เสนอราคา' => 'ส่งใบเสนอราคาและถามความคืบหน้าผ่าน LINE',
                'เจรจา' => 'ต่อรองราคา/เงื่อนไขและขอเอกสารเพิ่มเติม',
                'ปิดการขาย' => 'ยืนยันการเซ็นสัญญาและขั้นตอนถัดไป',
                'สูญเสีย' => 'ติดตามผลหลังดีลจบ',
            ];

            $nextAction = 'ทักเพื่อให้ข้อมูลเพิ่มเติม';
            foreach ($nextActionsByStage as $key => $value) {
                if (str_contains($stage->name, $key)) {
                    $nextAction = $value;
                    break;
                }
            }

            $value = fake()->numberBetween(30000, 450000);
            $expectedCloseDate = $nextActionDate->copy()->addDays(fake()->numberBetween(10, 45));

            $wonAt = null;
            $lostAt = null;
            if ((bool) $stage->is_won) {
                $wonAt = $nextActionDate->copy()->addDays(fake()->numberBetween(5, 30));
            }
            if (str_contains($stage->name, 'สูญเสีย') || str_contains(mb_strtolower($stage->name), 'lost')) {
                $lostAt = $nextActionDate->copy()->addDays(fake()->numberBetween(5, 30));
            }

            Deal::create([
                'organization_id' => $customer->organization_id,
                'name' => 'ดีลสำหรับ ' . $customer->name,
                'customer_id' => $customer->id,
                'user_id' => $customer->user_id,
                'team_id' => $customer->team_id,
                'stage_id' => $stage->id,
                'next_action' => $nextAction,
                'value' => $value,
                'expected_close_date' => $expectedCloseDate,
                'next_action_date' => $nextActionDate,
                'won_at' => $wonAt,
                'lost_at' => $lostAt,
            ]);
        }
    }
}

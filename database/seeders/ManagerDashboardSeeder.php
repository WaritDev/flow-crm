<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrCreate(
            ['slug' => 'ku-innovation'],
            [
                'name' => 'Kasetsart Innovation Hub',
                'size' => '11-50',
                'invite_code' => 'KU-DEMO2026',
                'description' => 'Web3 and Software Solutions provider',
            ]
        );

        $now = Carbon::now();
        $monthsToGenerate = 3;

        for ($i = $monthsToGenerate; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $org->targets()->updateOrCreate(
                [
                    'month' => $date->month,
                    'year' => $date->year,
                    'type' => 'revenue',
                ],
                [
                    'amount' => rand(4500000, 6000000),
                    'description' => 'เป้าหมายรายได้รวมบริษัทเดือน '.$date->format('M Y'),
                ]
            );
        }

        $template = PipelineTemplate::query()
            ->whereNull('organization_id')
            ->where('name', 'SaaS — Subscription sales')
            ->first()
            ?? PipelineTemplate::query()
                ->whereNull('organization_id')
                ->where('name', 'Default Pipeline')
                ->firstOrFail();

        $template->load(['stages' => fn ($q) => $q->orderBy('position')]);
        $stageModels = $template->stages->all();

        $lost = PipelineStage::query()
            ->where('template_id', $template->id)
            ->where('name', 'like', '%Lost%')
            ->first();

        if (! $lost) {
            $lost = PipelineStage::create([
                'template_id' => $template->id,
                'name' => 'สูญเสีย (Lost)',
                'position' => $template->stages->max('position') + 1,
                'is_won' => false,
            ]);
        }
        $stageModels[] = $lost;

        $team = Team::firstOrCreate(
            [
                'organization_id' => $org->id,
                'name' => 'Core Sales Team',
            ],
            ['template_id' => $template->id]
        );
        if ((int) $team->template_id !== (int) $template->id) {
            $team->update(['template_id' => $template->id]);
        }

        User::firstOrCreate(
            ['email' => 'manager@flowcrm.com'],
            [
                'name' => 'Warit Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'organization_id' => $org->id,
            ]
        );

        $salesNames = ['Alice', 'Bob', 'Charlie', 'David'];
        $salesReps = [];
        foreach ($salesNames as $name) {
            $sales = User::firstOrCreate(
                ['email' => strtolower($name).'@flowcrm.com'],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'sales',
                    'organization_id' => $org->id,
                    'team_id' => $team->id,
                ]
            );
            $sales->forceFill([
                'organization_id' => $org->id,
                'team_id' => $team->id,
                'role' => 'sales',
            ])->save();

            for ($i = $monthsToGenerate; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $sales->targets()->updateOrCreate(
                    [
                        'month' => $date->month,
                        'year' => $date->year,
                        'type' => 'revenue',
                    ],
                    [
                        'amount' => rand(800000, 1500000),
                        'description' => 'เป้าหมายยอดขายส่วนบุคคลเดือน '.$date->format('M Y'),
                    ]
                );
            }

            $salesReps[] = $sales;
        }

        if (Customer::query()->where('team_id', $team->id)->exists()) {
            return;
        }

        foreach ($salesReps as $sales) {
            $customers = Customer::factory(15)->create([
                'organization_id' => $org->id,
                'team_id' => $team->id,
                'user_id' => $sales->id,
            ]);

            foreach ($customers as $customer) {
                $numDeals = rand(1, 3);
                for ($i = 0; $i < $numDeals; $i++) {
                    $randomStage = $stageModels[array_rand($stageModels)];
                    $isWon = $randomStage->is_won;
                    $dealDate = Carbon::now()->subDays(rand(0, 180));
                    $wonAt = $isWon ? $dealDate->copy()->addDays(rand(5, 30)) : null;
                    $lostAt = $randomStage->name === 'สูญเสีย (Lost)' ? $dealDate->copy()->addDays(rand(5, 30)) : null;

                    $bucket = rand(0, 2); // 0 overdue, 1 today, 2 future
                    $timeHour = rand(9, 17);
                    $todayStart = Carbon::now()->startOfDay();
                    $nextActionDate = match ($bucket) {
                        0 => $todayStart->copy()->subDays(rand(1, 5)),
                        1 => $todayStart->copy(),
                        default => $todayStart->copy()->addDays(rand(1, 5)),
                    };
                    $nextActionDate = $nextActionDate->setTime($timeHour, 0, 0);
                    $nextAction = 'ทักเพื่อขอข้อมูลเพิ่มเติมทาง LINE';
                    $stageName = $randomStage->name;
                    if (str_contains($stageName, 'สนใจ')) {
                        $nextAction = 'ทักเพื่อขอข้อมูลเพิ่มเติมทาง LINE';
                    } elseif (str_contains($stageName, 'ติดต่อแล้ว')) {
                        $nextAction = 'นัดหมายเพื่อคุยรายละเอียดให้ชัดเจน';
                    } elseif (str_contains($stageName, 'เสนอราคา')) {
                        $nextAction = 'ส่งใบเสนอราคาและถามความคืบหน้าผ่าน LINE';
                    } elseif (str_contains($stageName, 'เจรจา')) {
                        $nextAction = 'ต่อรองราคา/เงื่อนไขและขอเอกสารเพิ่มเติม';
                    } elseif (str_contains($stageName, 'ปิดการขาย')) {
                        $nextAction = 'ยืนยันการเซ็นสัญญาและขั้นตอนถัดไป';
                    } elseif (str_contains($stageName, 'สูญเสีย') || str_contains(mb_strtolower($stageName), 'lost')) {
                        $nextAction = 'ติดตามผลหลังดีลจบ';
                    }

                    $deal = Deal::create([
                        'organization_id' => $org->id,
                        'customer_id' => $customer->id,
                        'user_id' => $sales->id,
                        'team_id' => $team->id,
                        'stage_id' => $randomStage->id,
                        'name' => 'Deal for '.$customer->name,
                        'value' => rand(30000, 450000),
                        'expected_close_date' => $dealDate->copy()->addDays(rand(10, 45)),
                        'next_action' => $nextAction,
                        'next_action_date' => $nextActionDate,
                        'won_at' => $wonAt,
                        'lost_at' => $lostAt,
                        'created_at' => $dealDate,
                        'updated_at' => $wonAt ?? ($lostAt ?? $dealDate->copy()->addDays(rand(1, 15))),
                    ]);

                    $stageLabel = $deal->lost_at !== null
                        ? 'Lost'
                        : ($deal->won_at !== null ? 'Won' : ($randomStage->name ?? 'Unknown'));

                    // Stage progress (timeline header)
                    Activity::create([
                        'deal_id' => $deal->id,
                        'customer_id' => $customer->id,
                        'user_id' => $sales->id,
                        'team_id' => $team->id,
                        'name' => 'Stage: '.$stageLabel,
                        'activity_type' => 'task',
                        'priority' => 1,
                        'is_completed' => true,
                        'description' => 'DEAL_STAGE_PROGRESS',
                        'created_at' => $dealDate->copy(),
                    ]);

                    // Progress task (Action Stream)
                    $priority = $nextActionDate->lt($todayStart) ? 3 : ($nextActionDate->isSameDay($todayStart) ? 2 : 1);
                    Activity::create([
                        'deal_id' => $deal->id,
                        'customer_id' => $customer->id,
                        'user_id' => $sales->id,
                        'team_id' => $team->id,
                        'name' => $nextAction,
                        'activity_type' => 'task',
                        'priority' => $priority,
                        'is_completed' => false,
                        'description' => 'DEAL_PROGRESS_TASK',
                        'created_at' => $dealDate->copy()->addDays(0),
                    ]);

                    Activity::create([
                        'deal_id' => $deal->id,
                        'customer_id' => $customer->id,
                        'user_id' => $sales->id,
                        'team_id' => $team->id,
                        'name' => 'Follow up with '.$customer->nickname,
                        'activity_type' => ['call', 'line', 'meeting', 'email'][rand(0, 3)],
                        'priority' => rand(1, 3),
                        'is_completed' => true,
                        'created_at' => $dealDate->copy()->addDays(rand(0, 10)),
                    ]);
                }
            }
        }
    }
}

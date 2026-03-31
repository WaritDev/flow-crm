<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

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

            // next_action_date: overdue / today / future for demo Action Stream
            $bucket = fake()->randomElement(['overdue', 'today', 'future']);
            $timeHour = fake()->numberBetween(9, 17);
            $nextActionDate = match ($bucket) {
                'overdue' => $now->copy()->subDays(fake()->numberBetween(1, 5)),
                'today' => $now->copy(),
                default => $now->copy()->addDays(fake()->numberBetween(1, 5)),
            };
            $nextActionDate = $nextActionDate->setTime($timeHour, 0, 0);

            $nextActionsByStage = [
                'Prospect' => 'Reach out on LINE for more context',
                'Qualified' => 'Confirm needs and decision makers',
                'Contacted' => 'Schedule a detail call',
                'Quoted' => 'Send quote and check progress on LINE',
                'Negotiation' => 'Align on price, terms, and paperwork',
                'Closed Won' => 'Confirm contract and handoff',
                'Closed lost' => 'Review closed-lost and log learnings',
                'สนใจ' => 'Reach out on LINE for more context',
                'คัดกรอง' => 'Confirm needs and decision makers',
                'ติดต่อแล้ว' => 'Schedule a detail call',
                'เสนอราคา' => 'Send quote and check progress on LINE',
                'เจรจา' => 'Align on price, terms, and paperwork',
                'ปิดการขาย' => 'Confirm contract and handoff',
                'สูญเสีย' => 'Review closed-lost and log learnings',
            ];

            $nextAction = 'Plan the next touchpoint';
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
            $sn = mb_strtolower((string) $stage->name);
            if ((str_contains($sn, 'lost') && ! str_contains($sn, 'won')) || str_contains($stage->name, 'สูญเสีย')) {
                $lostAt = $nextActionDate->copy()->addDays(fake()->numberBetween(5, 30));
            }

            Deal::create([
                'organization_id' => $customer->organization_id,
                'name' => 'Deal — '.$customer->name,
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

<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Resets customers / deals / activities for organization_id = 1 and seeds a concise
 * “~1 month of usage” story: readable pipeline, won revenue this month + prior months,
 * open tasks for the sales dashboard, and org + rep targets.
 */
class OneMonthOrganizationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::query()->find(1);
        if (! $org) {
            return;
        }

        $teams = Team::query()->where('organization_id', $org->id)->get();
        if ($teams->isEmpty()) {
            return;
        }

        $teamIds = $teams->pluck('id')->all();

        $dealIds = Deal::query()->whereIn('team_id', $teamIds)->pluck('id')->all();
        if ($dealIds !== []) {
            Activity::query()->whereIn('deal_id', $dealIds)->delete();
        }
        Activity::query()->whereIn('team_id', $teamIds)->delete();
        Deal::query()->whereIn('team_id', $teamIds)->delete();
        Customer::query()->whereIn('team_id', $teamIds)->delete();

        $team = $teams->first();
        $templateId = (int) $team->template_id;

        $maxPos = (int) PipelineStage::query()->where('template_id', $templateId)->max('position');
        PipelineStage::query()->firstOrCreate(
            [
                'template_id' => $templateId,
                'name' => 'Closed lost',
            ],
            [
                'position' => $maxPos + 1,
                'is_won' => false,
            ]
        );

        $stages = PipelineStage::query()
            ->where('template_id', $templateId)
            ->orderBy('position')
            ->get()
            ->keyBy(fn ($s) => strtolower((string) $s->name));

        $defaultStageId = $stages->values()->first()->id;

        $resolveStageId = function (array $spec) use ($stages, $defaultStageId): int {
            if (! empty($spec['won'])) {
                $won = $stages->first(fn ($s) => $s->is_won);

                return $won ? (int) $won->id : $defaultStageId;
            }
            if (! empty($spec['lost'])) {
                $lost = $stages->first(fn ($s) => str_contains(strtolower((string) $s->name), 'lost'));

                return $lost ? (int) $lost->id : $defaultStageId;
            }
            $key = strtolower((string) $spec['stageKey']);
            $match = $stages->get($key)
                ?? $stages->first(fn ($s) => str_contains(strtolower((string) $s->name), $key));

            return $match ? (int) $match->id : $defaultStageId;
        };

        $salesPrimary = User::query()
            ->where('organization_id', $org->id)
            ->where('role', 'sales')
            ->where('email', 'sales1@org1.com')
            ->first()
            ?? User::query()->where('organization_id', $org->id)->where('role', 'sales')->first();

        if (! $salesPrimary) {
            return;
        }

        $now = Carbon::now();

        // Targets: org + primary rep (current month)
        $org->targets()->updateOrCreate(
            [
                'month' => $now->month,
                'year' => $now->year,
                'type' => 'revenue',
            ],
            [
                'amount' => 4_500_000,
                'description' => 'Organization revenue target (demo)',
            ]
        );

        $salesPrimary->targets()->updateOrCreate(
            [
                'month' => $now->month,
                'year' => $now->year,
                'type' => 'revenue',
            ],
            [
                'amount' => 1_200_000,
                'description' => 'Rep target (demo)',
            ]
        );

        $customersSpec = [
            ['name' => 'Alex Wong', 'nickname' => 'Alex', 'line' => 'line_org1_alex', 'avatar' => 'https://i.pravatar.cc/150?u=flowcrm-alex'],
            ['name' => 'Jamie Lee', 'nickname' => 'Jay', 'line' => 'line_org1_jay', 'avatar' => 'https://i.pravatar.cc/150?u=flowcrm-jay'],
            ['name' => 'Sam Rivera', 'nickname' => 'Sam', 'line' => 'line_org1_sam', 'avatar' => 'https://i.pravatar.cc/150?u=flowcrm-sam'],
            ['name' => 'Taylor Brooks', 'nickname' => 'Tay', 'line' => 'line_org1_tay', 'avatar' => 'https://i.pravatar.cc/150?u=flowcrm-tay'],
            ['name' => 'Morgan Chen', 'nickname' => 'Mo', 'line' => 'line_org1_mo', 'avatar' => 'https://i.pravatar.cc/150?u=flowcrm-mo'],
            ['name' => 'Riley Patel', 'nickname' => 'Riley', 'line' => 'line_org1_riley', 'avatar' => null],
            ['name' => 'Casey Nguyen', 'nickname' => 'Casey', 'line' => 'line_org1_casey', 'avatar' => 'https://i.pravatar.cc/150?u=flowcrm-casey'],
            ['name' => 'Jordan Blake', 'nickname' => 'Jordan', 'line' => 'line_org1_jordan', 'avatar' => 'https://i.pravatar.cc/150?u=flowcrm-jordan'],
        ];

        $customers = collect($customersSpec)->map(function (array $row) use ($org, $team, $salesPrimary) {
            return Customer::create([
                'organization_id' => $org->id,
                'team_id' => $team->id,
                'user_id' => $salesPrimary->id,
                'name' => $row['name'],
                'nickname' => $row['nickname'],
                'line_id' => $row['line'],
                'phone_num' => '080'.random_int(1000000, 9999999),
                'email' => strtolower(str_replace(' ', '.', $row['name'])).'@example.com',
                'province' => 'Bangkok',
                'status' => 'active',
                'tags' => ['warm', 'org1-demo'],
                'img_profile' => $row['avatar'],
            ]);
        });

        $c = $customers->all();

        /** @var array<int, array<string, mixed>> $dealsBlueprint */
        $dealsBlueprint = [
            [
                'idx' => 0,
                'stageKey' => 'prospect',
                'value' => 185_000,
                'nextOffset' => -2,
                'openTask' => true,
                'prio' => 3,
                'won' => false,
                'lost' => false,
            ],
            [
                'idx' => 1,
                'stageKey' => 'contacted',
                'value' => 240_000,
                'nextOffset' => 0,
                'openTask' => true,
                'prio' => 2,
                'won' => false,
                'lost' => false,
            ],
            [
                'idx' => 2,
                'stageKey' => 'quoted',
                'value' => 412_000,
                'nextOffset' => 0,
                'openTask' => true,
                'prio' => 2,
                'won' => false,
                'lost' => false,
            ],
            [
                'idx' => 3,
                'stageKey' => 'negotiation',
                'value' => 510_000,
                'nextOffset' => 1,
                'openTask' => true,
                'prio' => 1,
                'won' => false,
                'lost' => false,
            ],
            [
                'idx' => 4,
                'stageKey' => 'quoted',
                'value' => 96_000,
                'nextOffset' => 3,
                'openTask' => false,
                'prio' => 1,
                'won' => false,
                'lost' => false,
            ],
            [
                'idx' => 5,
                'stageKey' => 'closed won',
                'value' => 320_000,
                'nextOffset' => 0,
                'openTask' => false,
                'prio' => 1,
                'won' => true,
                'lost' => false,
                'wonDaysAgo' => 6,
            ],
            [
                'idx' => 6,
                'stageKey' => 'closed won',
                'value' => 275_000,
                'nextOffset' => 0,
                'openTask' => false,
                'prio' => 1,
                'won' => true,
                'lost' => false,
                'wonDaysAgo' => 18,
            ],
            [
                'idx' => 7,
                'stageKey' => 'closed lost',
                'value' => 140_000,
                'nextOffset' => 0,
                'openTask' => false,
                'prio' => 1,
                'won' => false,
                'lost' => true,
                'lostDaysAgo' => 10,
            ],
        ];

        foreach ($dealsBlueprint as $spec) {
            $cust = $c[$spec['idx']];
            $stageId = $resolveStageId($spec);
            $created = $now->copy()->subDays(random_int(18, 32));

            $nextDate = $now->copy()->addDays((int) $spec['nextOffset'])->setTime(10, 0);

            $wonAt = null;
            $lostAt = null;
            if (! empty($spec['won'])) {
                $wonAt = $now->copy()->subDays((int) $spec['wonDaysAgo'])->setTime(15, 0);
                $nextDate = $wonAt->copy();
            }
            if (! empty($spec['lost'])) {
                $lostAt = $now->copy()->subDays((int) $spec['lostDaysAgo'])->setTime(11, 0);
                $nextDate = $lostAt->copy();
            }

            $deal = Deal::create([
                'organization_id' => $org->id,
                'customer_id' => $cust->id,
                'user_id' => $salesPrimary->id,
                'team_id' => $team->id,
                'stage_id' => $stageId,
                'name' => 'Deal — '.$cust->nickname,
                'value' => $spec['value'],
                'expected_close_date' => $nextDate->copy()->addDays(14),
                'next_action' => match ($spec['stageKey']) {
                    'prospect' => 'Send intro on LINE',
                    'contacted' => 'Book discovery call',
                    'quoted' => 'Follow up on proposal',
                    'negotiation' => 'Confirm legal & pricing',
                    'closed won' => 'Kickoff & onboarding',
                    'closed lost' => 'Log loss reason',
                    default => 'Next step',
                },
                'next_action_date' => $nextDate,
                'won_at' => $wonAt,
                'lost_at' => $lostAt,
                'created_at' => $created,
                'updated_at' => $wonAt ?? $lostAt ?? $now,
            ]);

            Activity::create([
                'deal_id' => $deal->id,
                'customer_id' => $cust->id,
                'user_id' => $salesPrimary->id,
                'team_id' => $team->id,
                'name' => 'Stage: '.($deal->won_at ? 'Won' : ($deal->lost_at ? 'Lost' : ($deal->stage?->name ?? 'Open'))),
                'activity_type' => 'task',
                'priority' => 1,
                'is_completed' => true,
                'description' => 'DEAL_STAGE_PROGRESS',
                'created_at' => $created,
                'updated_at' => $created,
            ]);

            if (! empty($spec['openTask'])) {
                Activity::create([
                    'deal_id' => $deal->id,
                    'customer_id' => $cust->id,
                    'user_id' => $salesPrimary->id,
                    'team_id' => $team->id,
                    'name' => $deal->next_action,
                    'activity_type' => 'line',
                    'priority' => (int) $spec['prio'],
                    'is_completed' => false,
                    'description' => 'DEAL_PROGRESS_TASK',
                    'created_at' => $created->copy()->addDay(),
                    'updated_at' => $now,
                ]);
            }

            // Light history: one completed touch ~1 week ago
            Activity::create([
                'deal_id' => $deal->id,
                'customer_id' => $cust->id,
                'user_id' => $salesPrimary->id,
                'team_id' => $team->id,
                'name' => 'Logged call — recap sent',
                'activity_type' => 'call',
                'priority' => 1,
                'is_completed' => true,
                'description' => null,
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(7),
            ]);
        }

        // Extra closed-won in prior months for dashboard chart (sales1 only)
        $wonStageId = $resolveStageId(['won' => true, 'stageKey' => '']);

        for ($m = 1; $m <= 4; $m++) {
            $monthStart = $now->copy()->subMonths($m)->startOfMonth()->addDays(10)->setTime(14, 0);
            Deal::create([
                'organization_id' => $org->id,
                'customer_id' => $c[4]->id,
                'user_id' => $salesPrimary->id,
                'team_id' => $team->id,
                'stage_id' => $wonStageId,
                'name' => 'Renewal touch — '.$monthStart->format('M'),
                'value' => 80_000 + ($m * 15_000),
                'expected_close_date' => $monthStart->copy()->addWeek(),
                'next_action' => 'Closed',
                'next_action_date' => $monthStart,
                'won_at' => $monthStart,
                'lost_at' => null,
                'created_at' => $monthStart->copy()->subWeek(),
                'updated_at' => $monthStart,
            ]);
        }
    }
}

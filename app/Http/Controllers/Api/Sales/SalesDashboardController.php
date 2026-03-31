<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use Illuminate\Http\Request;

class SalesDashboardController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $teamId = $user->getTeamId();

        if (!$teamId) {
            abort(403, 'Missing team_id.');
        }

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $todayStart->copy()->addDay();
        $lastMonthStart = $todayStart->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $todayStart->copy()->subMonth()->endOfMonth();
        $thisMonthStart = $todayStart->copy()->startOfMonth();
        $thisMonthEnd = $todayStart->copy()->endOfMonth();

        $activitiesQuery = Activity::query()
            ->where('team_id', $teamId)
            ->where('is_completed', false)
            ->with(['customer', 'deal.stage']);

        if ($user->role === 'sales') {
            $activitiesQuery->where('user_id', $user->id);
        }

        $openActivities = $activitiesQuery->get();

        $overdueDealsCount = 0;
        $todoTodayCount = 0;

        $todayTasks = $openActivities
            ->map(function (Activity $a) use ($todayStart, $todayEnd, &$overdueDealsCount, &$todoTodayCount) {
                $deal = $a->deal;
                $customer = $a->customer;

                $dueDate = $deal?->next_action_date;
                if (!$dueDate) {
                    return null;
                }

                $isOverdue = $dueDate->lt($todayStart);
                $isToday = $dueDate->gte($todayStart) && $dueDate->lt($todayEnd);

                if ($isOverdue) {
                    $overdueDealsCount++;
                }
                if ($isToday) {
                    $todoTodayCount++;
                }

                if (!$isToday) {
                    return null;
                }

                $priorityKey = match ((int) $a->priority) {
                    3 => 'urgent',
                    2 => 'medium',
                    default => 'normal',
                };

                $priorityLabel = match ($priorityKey) {
                    'urgent' => 'Urgent',
                    'medium' => 'Medium',
                    default => 'Normal',
                };

                $actionType = match ($a->activity_type) {
                    'call' => 'Call',
                    'message' => 'Message',
                    'line' => 'LINE',
                    'meeting' => 'Meeting',
                    'email' => 'Email',
                    'note' => 'Note',
                    'task' => 'Next action',
                    default => 'Task',
                };

                $amount = $deal?->value ? (float) $deal->value : 0;
                $lineId = $customer?->line_id ?? null;

                $script = '';
                $lineScript = $deal?->stage?->lineScripts?->first();
                if ($lineScript?->content) {
                    $script = str_replace(
                        ['{nickname}', '{customer_name}', '{line_id}', '{amount}'],
                        [
                            (string) ($customer?->nickname ?? $customer?->name ?? ''),
                            (string) ($customer?->name ?? ''),
                            (string) ($customer?->line_id ?? ''),
                            (string) $amount,
                        ],
                        (string) $lineScript->content
                    );
                }

                return [
                    'id' => (string) $a->id,
                    'priority_key' => $priorityKey,
                    'priority' => (int) $a->priority,
                    'priority_label' => $priorityLabel,
                    'action_type' => $actionType,
                    'customer_name' => (string) ($customer?->name ?? ''),
                    'customer_nickname' => (string) ($customer?->nickname ?? ''),
                    'title' => (string) ($a->name ?? ''),
                    'description' => $a->description,
                    'warning' => '',
                    'time' => 'Today',
                    'due_date' => $dueDate->toDateString(),
                    'amount' => $amount,
                    'line_id' => $lineId,
                    'script' => $script,
                ];
            })
            ->filter()
            ->values();

        $todayTasks = $todayTasks->sort(function (array $x, array $y) {
            if ($x['priority'] !== $y['priority']) {
                return $y['priority'] <=> $x['priority'];
            }
            return strcmp((string) ($x['due_date'] ?? ''), (string) ($y['due_date'] ?? ''));
        })->values();

        $dealsQuotedQuery = Deal::query()
            ->where('team_id', $teamId)
            ->whereNull('won_at')
            ->whereNull('lost_at')
            ->whereHas('stage', function ($q) {
                $q->whereRaw('LOWER(name) like ?', ['%quoted%'])
                    ->orWhereRaw('LOWER(name) like ?', ['%quote%']);
            });

        if ($user->role === 'sales') {
            $dealsQuotedQuery->where('user_id', $user->id);
        }

        $confirmedQuotes = $dealsQuotedQuery->count();

        $dealsThisMonth = Deal::query()
            ->where('team_id', $teamId)
            ->whereNotNull('won_at')
            ->whereBetween('won_at', [$thisMonthStart, $thisMonthEnd]);

        $dealsLastMonth = Deal::query()
            ->where('team_id', $teamId)
            ->whereNotNull('won_at')
            ->whereBetween('won_at', [$lastMonthStart, $lastMonthEnd]);

        if ($user->role === 'sales') {
            $dealsThisMonth->where('user_id', $user->id);
            $dealsLastMonth->where('user_id', $user->id);
        }

        $revenueMonth = (float) $dealsThisMonth->sum('value');
        $revenueLastMonth = (float) $dealsLastMonth->sum('value');
        $revenueGrowth = $revenueLastMonth > 0 ? (($revenueMonth - $revenueLastMonth) / $revenueLastMonth) * 100 : 0;

        $revenueToday = (float) Deal::query()
            ->where('team_id', $teamId)
            ->whereNotNull('won_at')
            ->whereBetween('won_at', [$todayStart, $todayEnd])
            ->when($user->isSales(), fn ($q) => $q->where('user_id', $user->id))
            ->sum('value');

        $targetRow = $user->isSales()
            ? $user->currentMonthTarget('revenue')
            : ($user->organization?->currentMonthTarget('revenue'));

        $targetAmount = $targetRow ? (float) $targetRow->amount : 0.0;
        $progressPercent = $targetAmount > 0
            ? (int) min(100, round(($revenueMonth / $targetAmount) * 100))
            : 0;

        $daysInMonth = (int) $now->daysInMonth;
        $dayOfMonth = (int) $now->day;
        $paceAmountToday = $targetAmount > 0 && $daysInMonth > 0
            ? $targetAmount * ($dayOfMonth / $daysInMonth)
            : 0.0;

        $chartLabels = collect(range(0, 5))
            ->map(fn ($i) => $now->copy()->subMonths(5 - $i)->format('M'))
            ->values();

        $chartData = collect(range(0, 5))
            ->map(function ($i) use ($now, $teamId, $user) {
                $monthStart = $now->copy()->subMonths(5 - $i)->startOfMonth();
                $monthEnd = $now->copy()->subMonths(5 - $i)->endOfMonth();

                $q = Deal::query()
                    ->where('team_id', $teamId)
                    ->whereNotNull('won_at')
                    ->whereBetween('won_at', [$monthStart, $monthEnd]);

                if ($user->role === 'sales') {
                    $q->where('user_id', $user->id);
                }

                return (float) $q->sum('value');
            })
            ->values();

        return response()->json([
            'stats' => [
                'todo_today' => $todoTodayCount,
                'overdue_deals' => $overdueDealsCount,
                'confirmed_quotes' => $confirmedQuotes,
                'revenue_month' => $revenueMonth,
                'revenue_growth' => (int) round($revenueGrowth),
            ],
            'chartData' => [
                'labels' => $chartLabels,
                'data' => $chartData,
                'projected' => [],
            ],
            'activities' => $todayTasks,
            'target_progress' => [
                'has_target' => $targetAmount > 0,
                'target_amount' => $targetAmount,
                'achieved_amount' => $revenueMonth,
                'progress_percent' => $progressPercent,
                'revenue_today' => $revenueToday,
                'pace_amount_by_today' => round($paceAmountToday, 2),
                'period_month' => $now->month,
                'period_year' => $now->year,
            ],
        ]);
    }
}


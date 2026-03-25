<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class SalesActivitiesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teamId = $user->getTeamId();

        if (!$teamId) {
            abort(403, 'Missing team_id.');
        }

        $completed = $request->query('completed', '0');
        $isCompleted = $completed === '1' || $completed === 'true' || $completed === 1 || $completed === true;

        $query = Activity::query()
            ->where('team_id', $teamId)
            ->where('is_completed', $isCompleted);

        if ($user->role === 'sales') {
            $query->where('user_id', $user->id);
        }

        $todayStart = now()->startOfDay();

        $activities = $query
            ->with([
                'customer.organization',
                'deal.stage.lineScripts' => function ($q) use ($teamId) {
                    $q->where('team_id', $teamId)->orderByDesc('use_count');
                },
            ])
            ->with([
                'deal.customer.organization',
                'deal.stage',
            ])
            ->get()
            ->map(function (Activity $a) use ($todayStart) {
                $deal = $a->deal;
                $customer = $a->customer;

                $dueDate = $deal?->next_action_date;

                $isOverdue = $dueDate ? $dueDate->lt($todayStart) : false;
                $isToday = $dueDate ? $dueDate->isSameDay($todayStart) : false;

                $bucketRank = $isOverdue ? 0 : ($isToday ? 1 : 2);

                $priorityKey = match ((int) $a->priority) {
                    3 => 'urgent',
                    2 => 'medium',
                    default => 'normal',
                };

                $priorityLabel = match ($priorityKey) {
                    'urgent' => 'ด่วน',
                    'medium' => 'ปานกลาง',
                    default => 'ปกติ',
                };

                $actionType = match ($a->activity_type) {
                    'call' => 'โทร',
                    'message' => 'ข้อความ',
                    'line' => 'ทัก LINE',
                    'meeting' => 'ประชุม',
                    'email' => 'อีเมล',
                    'note' => 'โน้ต',
                    'task' => 'งานต่อไป',
                    default => 'Task',
                };

                $customerNickname = (string) ($customer?->nickname ?? '');
                $customerName = (string) ($customer?->name ?? '');

                $warning = $isOverdue ? 'เลยกำหนดแล้ว' : '';

                if (!$dueDate) {
                    $timeLabel = '-';
                } elseif ($isOverdue) {
                    $timeLabel = 'เลยกำหนด';
                } elseif ($isToday) {
                    $timeLabel = 'วันนี้';
                } else {
                    $timeLabel = $dueDate->format('d M');
                }

                $amount = $deal?->value ? (float) $deal->value : 0;
                $lineId = $customer?->line_id ?? null;
                $lastContact = $deal?->updated_at ? $deal->updated_at->diffForHumans() : null;

                $lineScript = $deal?->stage?->lineScripts?->first();
                $script = $lineScript?->content ?? '';

                $script = str_replace(
                    ['{nickname}', '{customer_name}', '{line_id}', '{amount}'],
                    [
                        (string) ($customer?->nickname ?? $customer?->name ?? ''),
                        (string) ($customer?->name ?? ''),
                        (string) ($customer?->line_id ?? ''),
                        (string) $amount,
                    ],
                    $script
                );

                return [
                    'id' => (string) $a->id,
                    'priority_key' => $priorityKey,
                    'priority' => (int) $a->priority,
                    'priority_label' => $priorityLabel,
                    'action_type' => $actionType,
                    'customer_nickname' => $customerNickname,
                    'customer_name' => $customerName,
                    'title' => $a->name ?? '',
                    'description' => $a->description ?? null,
                    'warning' => $warning,
                    'time' => $timeLabel,
                    'due_date' => $dueDate ? $dueDate->toDateString() : null,
                    'amount' => $amount,
                    'line_id' => $lineId,
                    'last_contact' => $lastContact,
                    'script' => $script,
                    'bucket_rank' => $bucketRank,
                ];
            })
            ->sort(function ($x, $y) {
                if ($x['bucket_rank'] !== $y['bucket_rank']) {
                    return $x['bucket_rank'] <=> $y['bucket_rank'];
                }
                if ($x['priority'] !== $y['priority']) {
                    return $y['priority'] <=> $x['priority'];
                }
                return strcmp((string) ($x['due_date'] ?? ''), (string) ($y['due_date'] ?? ''));
            })
            ->values();

        return response()->json([
            'activities' => $activities,
        ]);
    }

    public function complete(Request $request, Activity $activity)
    {
        $user = $request->user();
        $teamId = $user->getTeamId();

        if (!$teamId) {
            abort(403, 'Missing team_id.');
        }

        if ((string) $activity->team_id !== (string) $teamId) {
            abort(403, 'Unauthorized.');
        }

        if ($user->role === 'sales' && (string) $activity->user_id !== (string) $user->id) {
            abort(403, 'Unauthorized.');
        }

        $activity->update(['is_completed' => true]);

        return response()->json(['ok' => true]);
    }
}


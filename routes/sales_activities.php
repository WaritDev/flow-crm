<?php

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// Note: this file is included from inside the existing auth middleware group
// in `routes/web.php`.

// -------------------- Action Stream / Activities API (Sales) --------------------
Route::get('/api/sales/activities', function (Request $request) {
    $user = $request->user();
    $teamId = $user->getTeamId();

    if (!$teamId) {
        abort(403, 'Missing team_id.');
    }

    $completed = $request->query('completed', '0');
    $isCompleted = $completed === '1' || $completed === 'true' || $completed === 1 || $completed === true;

    $query = \App\Models\Activity::query()
        ->where('team_id', $teamId)
        ->where('is_completed', $isCompleted);

    // Sales sees only their own activities
    if ($user->role === 'sales') {
        $query->where('user_id', $user->id);
    }

    $todayStart = now()->startOfDay();

    $activities = $query
        ->with([
            'customer.organization',
            'deal.stage.lineScripts' => function ($q) use ($teamId) {
                $q->where('team_id', $teamId)->orderByDesc('use_count');
            }
        ])
        ->with([
            'deal.customer.organization',
            'deal.stage'
        ])
        ->get()
        ->map(function (\App\Models\Activity $a) use ($todayStart) {
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

            $customerNickname = $customer?->nickname ?? $customer?->name ?? '';
            $customerName = $customer?->name ?? '';

            $warning = '';
            if ($isOverdue) {
                $warning = 'เลยกำหนดแล้ว';
            }

            // due label for UI
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

            // Pick script by deal.stage for the same team
            $lineScript = $deal?->stage?->lineScripts?->first();
            $script = $lineScript?->content ?? '';

            // Minimal variable replacement
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
            // overdue -> today -> future
            if ($x['bucket_rank'] !== $y['bucket_rank']) {
                return $x['bucket_rank'] <=> $y['bucket_rank'];
            }
            // urgent -> medium -> normal
            if ($x['priority'] !== $y['priority']) {
                return $y['priority'] <=> $x['priority'];
            }
            // earlier due date first
            return strcmp((string) ($x['due_date'] ?? ''), (string) ($y['due_date'] ?? ''));
        })
        ->values();

    return response()->json([
        'activities' => $activities
    ]);
});

Route::post('/api/sales/activities/{activity}/complete', function (Request $request, \App\Models\Activity $activity) {
    $user = $request->user();
    $teamId = $user->getTeamId();

    if (!$teamId) {
        abort(403, 'Missing team_id.');
    }

    if ($activity->team_id !== $teamId) {
        abort(403, 'Unauthorized.');
    }

    if ($user->role === 'sales' && $activity->user_id !== $user->id) {
        abort(403, 'Unauthorized.');
    }

    $activity->update(['is_completed' => true]);

    return response()->json(['ok' => true]);
});


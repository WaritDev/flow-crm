<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Deal;
use Illuminate\Support\Collection;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $teamId = $user?->getTeamId();

        if (!$teamId) {
            abort(403, 'Missing team_id.');
        }

        $todayStart = now()->startOfDay();

        $query = Activity::query()
            ->where('team_id', $teamId)
            ->where('is_completed', false)
            ->with([
                'customer',
                'deal.customer.organization',
                'deal.stage' ,
                'deal.stage.lineScripts' => function ($q) use ($teamId) {
                    $q->where('team_id', $teamId)->orderByDesc('use_count');
                },
            ]);

        if ($user->role === 'sales') {
            $query->where('user_id', $user->id);
        }

        /** @var Collection<int, array<string, mixed>> $activities */
        $activities = $query
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

                $actionType = match ($a->activity_type) {
                    'call' => 'Call',
                    'message' => 'Message',
                    'line' => 'LINE',
                    'meeting' => 'Meeting',
                    'email' => 'Email',
                    'note' => 'Note',
                    'task' => 'Next action',
                    default => 'Next action',
                };

                $customerNickname = $customer?->nickname ?? $customer?->name ?? '-';
                $customerName = $customer?->name ?? '-';

                $warning = '';
                if ($isOverdue) {
                    $warning = 'Overdue';
                } elseif ($isToday) {
                    $warning = 'Due today';
                }

                $time = $a->created_at ? $a->created_at->format('H:i') : '-';
                $amount = $deal?->value ? (int) $deal->value : 0;
                $lineId = $customer?->line_id ?? null;
                $lastContact = $deal?->updated_at ? $deal->updated_at->diffForHumans() : '-';

                $scriptTemplate = $deal?->stage?->lineScripts?->first()?->content ?? '';
                $script = str_replace(
                    ['{nickname}', '{customer_name}', '{line_id}', '{amount}'],
                    [
                        (string) ($customer?->nickname ?? $customer?->name ?? ''),
                        (string) ($customer?->name ?? ''),
                        (string) ($customer?->line_id ?? ''),
                        (string) $amount,
                    ],
                    $scriptTemplate
                );

                return [
                    'id' => (int) $a->id,
                    'priority' => $priorityKey,
                    'priority_int' => (int) $a->priority,
                    'bucket_rank' => $bucketRank,
                    'due_date' => $dueDate ? $dueDate->toDateString() : null,
                    'action_type' => $actionType,
                    'customer_nickname' => $customerNickname,
                    'customer_name' => $customerName,
                    'title' => (string) ($a->name ?? ''),
                    'warning' => $warning,
                    'time' => $time,
                    'amount' => $amount,
                    'line_id' => $lineId,
                    'last_contact' => $lastContact,
                    'script' => $script,
                ];
            })
            ->sort(function (array $x, array $y) {
                if ($x['bucket_rank'] !== $y['bucket_rank']) {
                    return $x['bucket_rank'] <=> $y['bucket_rank'];
                }
                // urgent -> medium -> normal
                if ($x['priority_int'] !== $y['priority_int']) {
                    return $y['priority_int'] <=> $x['priority_int'];
                }
                return strcmp((string) ($x['due_date'] ?? ''), (string) ($y['due_date'] ?? ''));
            })
            ->values()
            ->all();

        // Empty state placeholder when there is no activity data yet.
        if (count($activities) === 0) {
            $activities = [[
                'id' => 0,
                'priority' => 'normal',
                'priority_int' => 1,
                'bucket_rank' => 2,
                'due_date' => null,
                'action_type' => 'Next action',
                'customer_nickname' => '-',
                'customer_name' => '-',
                'title' => 'No tasks due',
                'warning' => '',
                'time' => '-',
                'amount' => 0,
                'line_id' => null,
                'last_contact' => '-',
                'script' => '',
            ]];
        }

        return view('activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

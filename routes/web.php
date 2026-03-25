<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\PipelineTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\InvitationController;
use App\Models\Invitation;

Route::get('/', function () {
    return view('index');
});

Route::middleware(['auth'])->get('/me', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'id' => (string) $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'team_id' => $user->team_id,
        'team_name' => $user->team?->name,
        'organization_id' => $user->organization_id,
    ]);
})->name('me');

Route::middleware(['auth'])->group(function () {
    Route::get('/api/sales/csrf', function () {
        return response()->json(['csrf_token' => csrf_token()]);
    });

    Route::get('/api/sales/pipeline-board', function (Request $request) {
        $user = $request->user();

        $scope = (string) $request->query('scope', 'mine'); // mine | team | all
        $requestedTeamId = $request->query('team_id');

        $myTeam = $user->team;
        if (!$myTeam && $user->organization) {
            $myTeam = $user->organization->teams()->first();
        }

        $orgTeams = $user->organization ? $user->organization->teams()->get() : collect([]);
        if ($orgTeams->isEmpty() && $myTeam) {
            $orgTeams = collect([$myTeam]);
        }

        $activeTeam = null;
        if ($scope === 'team' && $requestedTeamId) {
            $activeTeam = $orgTeams->firstWhere('id', (int) $requestedTeamId);
        }
        if (!$activeTeam) {
            $activeTeam = $myTeam ?? $orgTeams->first();
        }

        $readOnly = $scope !== 'mine';

        $stages = $activeTeam && $activeTeam->pipelineTemplate
            ? $activeTeam->pipelineTemplate->stages
            : collect([]);

        // Team ids that we should load deals from
        $teamIds = collect([]);
        if ($scope === 'mine' && $myTeam) {
            $teamIds = collect([$myTeam->id]);
        } elseif ($scope === 'team' && $activeTeam) {
            $teamIds = collect([$activeTeam->id]);
        } else {
            $teamIds = $orgTeams->pluck('id');
        }

        $deals = \App\Models\Deal::whereIn('team_id', $teamIds->all())
            ->with(['customer.organization', 'stage'])
            ->get()
            ->map(function ($deal) {
                $ageHours = $deal->updated_at ? $deal->updated_at->diffInHours(now()) : 0;
                $daysInStage = intdiv($ageHours, 24);
                $stagePosition = $deal->stage?->position;

                return [
                    'id' => (string) $deal->id,
                    'stage_id' => $deal->stage_id ? (string) $deal->stage_id : null,
                    'stage_position' => $stagePosition !== null ? (int) $stagePosition : null,
                    'name' => $deal->name,
                    'value' => (float) $deal->value,
                    'description' => $deal->description,
                    'expected_close_date' => $deal->expected_close_date ? $deal->expected_close_date->toDateString() : null,
                    'next_action' => $deal->next_action,
                    'next_action_date' => $deal->next_action_date ? $deal->next_action_date->toDateString() : null,
                    'lost_reason' => $deal->lost_reason,
                    'lost_at' => $deal->lost_at ? $deal->lost_at->toDateTimeString() : null,
                    'won_at' => $deal->won_at ? $deal->won_at->toDateTimeString() : null,
                    'customer' => [
                        'name' => $deal->customer->name ?? null,
                        'nickname' => $deal->customer->nickname ?? null,
                        'organization_name' => $deal->customer->organization->name ?? null,
                        'line_id' => $deal->customer->line_id ?? null,
                    ],
                    'is_stale' => $deal->isStale(),
                    'days_in_stage' => (int) $daysInStage,
                    'age_hours' => (int) $ageHours,
                    'updated_at' => $deal->updated_at ? $deal->updated_at->toDateTimeString() : null,
                ];
            });

        return response()->json([
            'scope' => $scope,
            'read_only' => (bool) $readOnly,
            'my_team_id' => $myTeam ? (string) $myTeam->id : null,
            'active_team_id' => $activeTeam ? (string) $activeTeam->id : null,
            'teams' => $orgTeams->map(fn ($t) => [
                'id' => (string) $t->id,
                'name' => $t->name,
            ])->values(),
            'stages' => $stages->map(fn ($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
            'deals' => $deals->values(),
        ]);
    });

    Route::get('/api/sales/deals/create-data', function (Request $request) {
        $user = $request->user();
        $team = $user->team;

        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $customers = \App\Models\Customer::where('team_id', $user->getTeamId())
            ->where('status', 'active')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => (string) $c->id,
                    'label' => "{$c->name} ({$c->nickname}) - {$c->line_id}",
                    'name' => $c->name,
                    'nickname' => $c->nickname,
                ];
            });

        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        return response()->json([
            'customers' => $customers->values(),
            'stages' => $stages->map(fn($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
        ]);
    });

    Route::get('/api/sales/deals/{deal}/edit-data', function (Request $request, \App\Models\Deal $deal) {
        $user = $request->user();
        Gate::authorize('view', $deal);

        $team = $user->team;
        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        $customers = \App\Models\Customer::all()->map(function ($c) {
            return [
                'id' => (string) $c->id,
                'label' => "{$c->name} ({$c->nickname}) - {$c->line_id}",
                'name' => $c->name,
                'nickname' => $c->nickname,
            ];
        });

        $deal->load(['customer.organization', 'stage']);

        $activities = $deal->activities()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($a) {
                $u = $a->user_id ? \App\Models\User::find($a->user_id) : null;
                return [
                    'id' => (string) $a->id,
                    'title' => $a->name,
                    'created_at' => $a->created_at ? $a->created_at->toDateTimeString() : null,
                    'is_completed' => (bool) $a->is_completed,
                    'user_name' => $u?->name ?? null,
                ];
            });

        return response()->json([
            'deal' => [
                'id' => (string) $deal->id,
                'name' => $deal->name,
                'value' => (float) $deal->value,
                'description' => $deal->description,
                'expected_close_date' => $deal->expected_close_date ? $deal->expected_close_date->toDateString() : null,
                'next_action' => $deal->next_action,
                'next_action_date' => $deal->next_action_date ? $deal->next_action_date->toDateString() : null,
                'stage_id' => $deal->stage_id ? (string) $deal->stage_id : null,
                'lost_reason' => $deal->lost_reason,
                'lost_at' => $deal->lost_at ? $deal->lost_at->toDateTimeString() : null,
                'won_at' => $deal->won_at ? $deal->won_at->toDateTimeString() : null,
                'customer' => [
                    'id' => $deal->customer ? (string) $deal->customer->id : null,
                    'name' => $deal->customer->name ?? null,
                    'nickname' => $deal->customer->nickname ?? null,
                    'organization_name' => $deal->customer->organization->name ?? null,
                ],
                'stage' => $deal->stage ? [
                    'id' => (string) $deal->stage->id,
                    'name' => $deal->stage->name,
                    'is_won' => (bool) $deal->stage->is_won,
                ] : null,
                'updated_at' => $deal->updated_at ? $deal->updated_at->toDateTimeString() : null,
                'created_at' => $deal->created_at ? $deal->created_at->toDateTimeString() : null,
            ],
            'customers' => $customers->values(),
            'stages' => $stages->map(fn($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
            'activities' => $activities->values(),
        ]);
    });

    // Move deal stage (pipeline board drag/drop)
    Route::put('/api/sales/deals/{deal}/move-stage', function (Request $request, \App\Models\Deal $deal) {
        Gate::authorize('update', $deal);

        $request->validate([
            'stage_id' => ['required'],
        ]);

        $stage = \App\Models\PipelineStage::findOrFail($request->input('stage_id'));

        $deal->update([
            'stage_id' => $stage->id,
            'lost_reason' => null,
            'lost_at' => null,
            'won_at' => $stage->is_won ? now() : null,
        ]);

        return response()->json(['ok' => true]);
    });

    // -------------------- Customers API (Sales) --------------------
    Route::get('/api/sales/customers', function (Request $request) {
        $user = $request->user();

        $team = $user->team;
        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        if (!$team) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1
            ]);
        }

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $query = \App\Models\Customer::query()->where('team_id', $team->id);
        if ($user->role === 'sales') {
            // Sales only sees their own customers
            $query->where('user_id', $user->id);
        }

        if ($status !== '') {
            $normalized = strtolower($status);
            if (in_array($normalized, ['active', '1', 'true'], true)) {
                $query->where('status', 'active');
            } elseif (in_array($normalized, ['inactive', '0', 'false'], true)) {
                $query->where('status', 'inactive');
            }
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('nickname', 'like', $like)
                    ->orWhere('line_id', 'like', $like)
                    ->orWhere('phone_num', 'like', $like);
            });
        }

        $customers = $query->orderBy('updated_at', 'desc')->paginate(10);
        $customers->getCollection()->transform(function ($c) use ($team) {
            $lifetimeValue = \App\Models\Deal::where('customer_id', $c->id)
                ->where('team_id', $team->id)
                ->whereHas('stage', fn ($q) => $q->where('is_won', true))
                ->sum('value');

            return [
                'id' => (int) $c->id,
                'name' => $c->name,
                'nickname' => $c->nickname,
                'is_active' => $c->status === 'active',
                'lifetime_value' => (float) $lifetimeValue,
                'organization_name' => $c->organization?->name,
            ];
        });

        return response()->json($customers->toArray());
    });

    Route::post('/api/sales/customers', function (Request $request) {
        $user = $request->user();

        $team = $user->team;
        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        if (!$team) {
            abort(403, 'No team found for this user.');
        }

        $organizationId = $team->organization_id ?? $user->getOrganizationId();
        if (!$organizationId) {
            abort(403, 'Missing organization_id for this team.');
        }

        if (!in_array($user->role, ['sales', 'manager'], true)) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'fullname' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'line_id' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'province' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'tags' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $tagsRaw = (string) $request->input('tags', '');
        $tags = [];
        if (trim($tagsRaw) !== '') {
            $parts = preg_split('/,/', $tagsRaw) ?: [];
            $tags = array_values(array_filter(array_map(fn ($t) => trim($t), $parts)));
        }

        $customer = new \App\Models\Customer();
        $customer->team_id = $team->id;
        $customer->user_id = $user->id;
        $customer->organization_id = $organizationId;
        $customer->name = $request->input('fullname');
        $customer->nickname = $request->input('nickname');
        $customer->phone_num = $request->input('phone');
        $customer->email = $request->input('email');
        $customer->line_id = $request->input('line_id');
        $customer->province = $request->input('province');
        $customer->address = $request->input('address');
        $customer->tags = $tags;
        $customer->status = $request->boolean('is_active') ? 'active' : 'inactive';

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('customers', 'public');
            $customer->img_profile = $path;
        }

        $customer->save();

        return response()->json(['ok' => true, 'id' => (int) $customer->id]);
    });

    Route::get('/api/sales/customers/{customer}', function (Request $request, \App\Models\Customer $customer) {
        $user = $request->user();
        $teamId = $user->getTeamId();

        if ($user->role === 'sales') {
            if ((string) $customer->user_id !== (string) $user->id) {
                abort(403, 'Unauthorized.');
            }
        } else {
            // Manager/admin scoped to their team
            if ($teamId !== null && (string) $customer->team_id !== (string) $teamId) {
                abort(403, 'Unauthorized.');
            }
        }

        $customer->load(['organization']);
        $avatarUrl = $customer->img_profile ? Storage::disk('public')->url($customer->img_profile) : null;

        $deals = \App\Models\Deal::where('customer_id', $customer->id)
            ->where('team_id', $customer->team_id);

        $lifetimeValue = (float) $deals
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->sum('value');

        $totalDeals = (int) $deals->count();

        $lastActivity = \App\Models\Activity::where('customer_id', $customer->id)
            ->where('team_id', $customer->team_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastContacted = $lastActivity?->created_at ? $lastActivity->created_at->toDateTimeString() : null;
        $lastContactedDiffHuman = $lastActivity?->created_at ? $lastActivity->created_at->diffForHumans() : null;

        $activities = \App\Models\Activity::where('customer_id', $customer->id)
            ->where('team_id', $customer->team_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($a) {
                $u = $a->user_id ? \App\Models\User::find($a->user_id) : null;
                return [
                    'id' => (string) $a->id,
                    'title' => $a->name,
                    'description' => $a->description,
                    'type' => $a->activity_type,
                    'created_at' => $a->created_at ? $a->created_at->toDateTimeString() : null,
                    'is_completed' => (bool) $a->is_completed,
                    'user' => $u ? ['name' => $u->name] : null,
                ];
            });

        return response()->json([
            'customer' => [
                'id' => (int) $customer->id,
                'fullname' => $customer->name,
                'nickname' => $customer->nickname,
                'phone' => $customer->phone_num,
                'email' => $customer->email,
                'line_id' => $customer->line_id,
                'province' => $customer->province,
                'address' => $customer->address,
                'tags' => $customer->tags,
                'is_active' => $customer->status === 'active',
                'avatar_url' => $avatarUrl,
            ],
            'statistics' => [
                'lifetime_value' => $lifetimeValue,
                'total_deals' => $totalDeals,
                'last_contacted' => $lastContacted,
                'last_contacted_diff_human' => $lastContactedDiffHuman,
            ],
            'deals' => [],
            'activities' => $activities->values(),
        ]);
    });

    Route::put('/api/sales/customers/{customer}', function (Request $request, \App\Models\Customer $customer) {
        $user = $request->user();
        $teamId = $user->getTeamId();

        if ($user->role === 'sales') {
            if ((string) $customer->user_id !== (string) $user->id) {
                abort(403, 'Unauthorized.');
            }
        } else {
            if ($teamId !== null && (string) $customer->team_id !== (string) $teamId) {
                abort(403, 'Unauthorized.');
            }
        }

        $request->validate([
            'fullname' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'line_id' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'province' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'tags' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $tagsRaw = (string) $request->input('tags', '');
        $tags = [];
        if (trim($tagsRaw) !== '') {
            $parts = preg_split('/,/', $tagsRaw) ?: [];
            $tags = array_values(array_filter(array_map(fn ($t) => trim($t), $parts)));
        }

        $customer->name = $request->input('fullname');
        $customer->nickname = $request->input('nickname');
        $customer->phone_num = $request->input('phone');
        $customer->email = $request->input('email');
        $customer->line_id = $request->input('line_id');
        $customer->province = $request->input('province');
        $customer->address = $request->input('address');
        $customer->tags = $tags;
        $customer->status = $request->boolean('is_active') ? 'active' : 'inactive';

        if ($request->hasFile('avatar')) {
            if ($customer->img_profile) {
                Storage::disk('public')->delete($customer->img_profile);
            }
            $path = $request->file('avatar')->store('customers', 'public');
            $customer->img_profile = $path;
        }

        $customer->save();

        return response()->json(['ok' => true]);
    });

    require __DIR__.'/sales_activities.php'; /* -------------------- Action Stream / Activities API (Sales) moved --------------------
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
*/
});

// -------------------- Existing routes --------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::get('/pipeline-stages', [PipelineStageController::class, 'index'])->name('pipeline-stages.index');
    Route::get('/pipeline-stages/create', [PipelineStageController::class, 'create'])->name('pipeline-stages.create');
    Route::resource('pipeline-stages', PipelineStageController::class);
    Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
    Route::get('/deals/create', [DealController::class, 'create'])->name('deals.create');
    Route::get('/deals/{id}/edit', [DealController::class, 'edit'])->name('deals.edit');
    Route::resource('deals', DealController::class);
    Route::get('/pipeline-templates', [PipelineTemplateController::class, 'index'])->name('pipeline-templates.index');
//    Route::get('/pipelines-templates/create', [PipelineTemplateController::class, 'create'])->name('pipelines.create');
    Route::post('/pipeline-templates/select', [PipelineTemplateController::class, 'select'])->name('pipeline-templates.select');
    Route::resource('pipeline-templates', PipelineTemplateController::class);
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
//    Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
//    Route::get('/activities/{id}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
    Route::resource('activities', ActivityController::class);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth', 'role:manager,admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // Manager
    Route::middleware(['role:manager'])->group(function () {

        // Team Management
        Route::resource('teams', TeamController::class);

        // Custom Routes
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.add_member');
        Route::delete('/teams/members/{user}', [TeamController::class, 'removeMember'])->name('teams.remove_member');

        // Invitation Management
        Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

        // Dashboard Targets Update
        Route::post('/dashboard/targets', [DashboardController::class, 'updateTargets'])->name('targets.update');
    });

    // Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('organizations', OrganizationController::class);
        Route::get('/organization-users', [OrganizationController::class, 'usersIndex'])->name('organization-users.index');
    });

    // Invitation Acceptance Route
    Route::get('/register/invite/{token}', function ($token) {
        $invitation = \App\Models\Invitation::where('token', $token)
                        ->where('expires_at', '>', now())
                        ->firstOrFail(); 

        return view('auth.register-invite', compact('invitation'));
    })->name('register.invite');
    Route::post('/register/invite', [InvitationController::class, 'accept'])->name('register.invite.submit');

require __DIR__.'/auth.php';

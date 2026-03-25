<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalesCustomersController extends Controller
{
    public function index(Request $request)
    {
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
                'last_page' => 1,
            ]);
        }

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $query = Customer::query()->where('team_id', $team->id);
        if ($user->role === 'sales') {
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
            $lifetimeValue = Deal::where('customer_id', $c->id)
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
    }

    public function store(Request $request)
    {
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

        $customer = new Customer();
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
    }

    public function show(Request $request, Customer $customer)
    {
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

        $customer->load(['organization']);
        $avatarUrl = $customer->img_profile ? Storage::disk('public')->url($customer->img_profile) : null;

        $deals = Deal::where('customer_id', $customer->id)
            ->where('team_id', $customer->team_id);

        $lifetimeValue = (float) $deals
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->sum('value');

        $totalDeals = (int) $deals->count();

        $dealsForTimeline = Deal::where('customer_id', $customer->id)
            ->where('team_id', $customer->team_id)
            ->with(['stage'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($deal) use ($customer) {
                $stageName = null;
                $isWon = false;
                if ($deal->won_at !== null) {
                    $stageName = 'Won';
                    $isWon = true;
                } elseif ($deal->lost_at !== null) {
                    $stageName = 'Lost';
                    $isWon = false;
                } else {
                    $stageName = $deal->stage?->name;
                    $isWon = (bool) ($deal->stage?->is_won ?? false);
                }

                $activities = $deal->activities()
                    ->where('team_id', $customer->team_id)
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->map(function ($a) {
                        $u = $a->user_id ? \App\Models\User::find($a->user_id) : null;
                        return [
                            'id' => (string) $a->id,
                            'title' => $a->name,
                            'type' => $a->activity_type,
                            'description' => $a->description,
                            'created_at' => $a->created_at ? $a->created_at->toDateTimeString() : null,
                            'is_completed' => (bool) $a->is_completed,
                            'user_name' => $u?->name ?? null,
                            'is_progress_task' => (string) $a->description === 'DEAL_PROGRESS_TASK',
                            'is_stage_progress' => (string) $a->description === 'DEAL_STAGE_PROGRESS',
                        ];
                    });

                return [
                    'id' => (string) $deal->id,
                    'name' => $deal->name,
                    'next_action' => $deal->next_action,
                    'next_action_date' => $deal->next_action_date ? $deal->next_action_date->toDateString() : null,
                    'stage' => [
                        'id' => $deal->stage_id ? (string) $deal->stage_id : null,
                        'name' => $stageName,
                        'is_won' => $isWon,
                    ],
                    'updated_at' => $deal->updated_at ? $deal->updated_at->toDateTimeString() : null,
                    'activities' => $activities->values(),
                ];
            });

        $lastActivity = Activity::where('customer_id', $customer->id)
            ->where('team_id', $customer->team_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastContacted = $lastActivity?->created_at ? $lastActivity->created_at->toDateTimeString() : null;
        $lastContactedDiffHuman = $lastActivity?->created_at ? $lastActivity->created_at->diffForHumans() : null;

        $activities = Activity::where('customer_id', $customer->id)
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
            'deals' => $dealsForTimeline->values(),
            'activities' => $activities->values(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
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
    }
}


<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Api\Concerns\ResolvesAutomationTeam;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesN8nController extends Controller
{
    use ResolvesAutomationTeam;

    public function inactiveDeals(Request $request)
    {
        $user = $request->user();
        $orgId = (int) $user->organization_id;
        if (! $orgId) {
            abort(403, 'Missing organization.');
        }

        $teamId = $this->resolveAutomationTeamId($user, $request, $orgId);

        $hours = (int) $request->query('hours', 48);
        $cutoff = now()->subHours(max($hours, 1));

        $q = Deal::query()
            ->where('team_id', $teamId)
            ->whereNull('won_at')
            ->whereNull('lost_at')
            ->where('updated_at', '<', $cutoff)
            ->with(['customer', 'stage']);

        if ($user->role === 'sales') {
            $q->where('user_id', $user->id);
        }

        $deals = $q->get()->map(function (Deal $d) {
            return [
                'deal_id' => (string) $d->id,
                'customer_id' => (string) $d->customer_id,
                'customer_name' => $d->customer?->name ?? null,
                'customer_nickname' => $d->customer?->nickname ?? null,
                'line_id' => $d->customer?->line_id ?? null,
                'stage_name' => $d->stage?->name ?? null,
                'next_action' => $d->next_action,
                'next_action_date' => $d->next_action_date ? $d->next_action_date->toDateString() : null,
                'updated_at' => $d->updated_at ? $d->updated_at->toDateTimeString() : null,
            ];
        });

        return response()->json(['deals' => $deals]);
    }

    public function upsertNextAction(Request $request, Deal $deal)
    {
        $user = $request->user();
        $orgId = (int) $user->organization_id;
        if (! $orgId) {
            abort(403, 'Missing organization.');
        }

        $teamId = $this->resolveAutomationTeamId($user, $request, $orgId);

        if ((string) $deal->team_id !== (string) $teamId) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'next_action' => 'required|string|max:1000',
            'next_action_date' => 'required|date',
            'priority' => 'nullable|integer|min:1|max:3',
        ]);

        $nextAction = (string) $request->input('next_action');
        $nextActionDate = (string) $request->input('next_action_date');
        $priorityInput = $request->input('priority');

        if ($user->role === 'sales' && (string) $deal->user_id !== (string) $user->id) {
            abort(403, 'Unauthorized.');
        }

        $deal->update([
            'next_action' => $nextAction,
            'next_action_date' => $nextActionDate,
        ]);

        Activity::where('deal_id', $deal->id)
            ->where('description', 'DEAL_PROGRESS_TASK')
            ->where('is_completed', false)
            ->update(['is_completed' => true]);

        $todayStart = now()->startOfDay();
        $due = Carbon::parse($nextActionDate)->startOfDay();
        $computedPriority = $due->lt($todayStart) ? 3 : ($due->isSameDay($todayStart) ? 2 : 1);
        $priority = $priorityInput !== null ? (int) $priorityInput : $computedPriority;

        Activity::create([
            'deal_id' => $deal->id,
            'customer_id' => $deal->customer_id,
            'user_id' => $user->id,
            'team_id' => $teamId,
            'activity_type' => 'task',
            'name' => $nextAction,
            'description' => 'DEAL_PROGRESS_TASK',
            'priority' => $priority,
            'is_completed' => false,
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}

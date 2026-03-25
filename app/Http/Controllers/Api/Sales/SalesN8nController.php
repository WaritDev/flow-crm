<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Api\Concerns\ResolvesAutomationTeam;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SalesN8nController extends Controller
{
    use ResolvesAutomationTeam;

    /**
     * Pipeline context for automation (n8n): current stage, ordered stages, suggested “next” stage
     * (first stage after current with higher position and not won).
     */
    public function dealPipelineContext(Request $request, Deal $deal)
    {
        $user = $request->user();
        Gate::authorize('view', $deal);

        $deal->load(['stage', 'customer']);

        $team = Team::query()
            ->where('organization_id', $deal->organization_id)
            ->whereKey($deal->team_id)
            ->with(['pipelineTemplate.stages'])
            ->firstOrFail();

        $stages = $team->pipelineTemplate
            ? $team->pipelineTemplate->stages->sortBy('position')->values()
            : collect();

        $current = $deal->stage;
        $currentPosition = $current ? (int) $current->position : -1;

        $nextStage = null;
        foreach ($stages as $s) {
            if ((int) $s->position > $currentPosition && ! $s->is_won) {
                $nextStage = $s;
                break;
            }
        }

        $template = $team->pipelineTemplate;

        return response()->json([
            'deal_id' => (string) $deal->id,
            'customer_id' => (string) $deal->customer_id,
            'customer_name' => $deal->customer?->name,
            'team_id' => (string) $deal->team_id,
            'line_id' => $deal->customer?->line_id,
            'customer' => [
                'id' => (string) $deal->customer_id,
                'name' => $deal->customer?->name,
                'nickname' => $deal->customer?->nickname,
                'line_id' => $deal->customer?->line_id,
            ],
            'deal' => [
                'name' => $deal->name,
                'value' => $deal->value !== null ? (float) $deal->value : null,
                'currency' => $deal->currency,
                'next_action' => $deal->next_action,
                'next_action_date' => $deal->next_action_date ? $deal->next_action_date->toDateString() : null,
            ],
            'template' => $template ? [
                'id' => (string) $template->id,
                'name' => $template->name,
                'industry' => $template->industry,
                'description' => $template->description,
            ] : null,
            'current_stage' => $current ? [
                'id' => (string) $current->id,
                'name' => $current->name,
                'position' => (int) $current->position,
                'description' => $current->description,
                'is_won' => (bool) $current->is_won,
            ] : null,
            'next_stage' => $nextStage ? [
                'id' => (string) $nextStage->id,
                'name' => $nextStage->name,
                'position' => (int) $nextStage->position,
                'description' => $nextStage->description,
                'is_won' => (bool) $nextStage->is_won,
            ] : null,
            'stages' => $stages->map(fn ($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'description' => $s->description,
                'is_won' => (bool) $s->is_won,
            ])->values(),
        ]);
    }

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

        // Ownership: assign Activity to the sales user responsible for this deal.
        // This ensures Sales Action Stream can see the tasks.
        $activityUserId = (int) $deal->user_id;
        // Always use the deal's team on the activity row (matches 403 check above).
        $activityTeamId = (int) $deal->team_id;

        $activity = Activity::create([
            'deal_id' => $deal->id,
            'customer_id' => $deal->customer_id,
            'user_id' => $activityUserId,
            'team_id' => $activityTeamId,
            'activity_type' => 'task',
            'name' => $nextAction,
            'description' => 'DEAL_PROGRESS_TASK',
            'priority' => $priority,
            'is_completed' => false,
            'created_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'activity_id' => (string) $activity->id,
            'deal_id' => (string) $deal->id,
            'assigned_user_id' => (string) $activityUserId,
            'team_id' => (string) $activityTeamId,
        ]);
    }
}

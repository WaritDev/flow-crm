<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SalesDealsController extends Controller
{
    public function createData(Request $request)
    {
        $user = $request->user();
        $team = $user->team;

        if (! $team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $customers = Customer::where('team_id', $user->getTeamId())
            ->where('status', 'active')
            ->get()
            ->map(function (Customer $c) {
                return [
                    'id' => (string) $c->id,
                    'label' => (string) $c->name,
                    'name' => $c->name,
                    'nickname' => $c->nickname,
                ];
            });

        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        return response()->json([
            'customers' => $customers->values(),
            'stages' => $stages->map(fn ($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
        ]);
    }

    public function editData(Request $request, Deal $deal)
    {
        $user = $request->user();
        Gate::authorize('view', $deal);

        $team = $user->team;
        if (! $team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        // NOTE: kept as-is from existing route for compatibility (was not team-scoped before)
        $customers = Customer::all()->map(function (Customer $c) {
            return [
                'id' => (string) $c->id,
                'label' => (string) $c->name,
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
                    'type' => $a->activity_type,
                    'description' => $a->description,
                    'created_at' => $a->created_at ? $a->created_at->toDateTimeString() : null,
                    'is_completed' => (bool) $a->is_completed,
                    'user_name' => $u?->name ?? null,
                    'is_progress_task' => (string) $a->description === 'DEAL_PROGRESS_TASK',
                    'is_stage_progress' => (string) $a->description === 'DEAL_STAGE_PROGRESS',
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
            'stages' => $stages->map(fn ($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
            'activities' => $activities->values(),
        ]);
    }

    public function moveStage(Request $request, Deal $deal)
    {
        $user = $request->user();
        Gate::authorize('update', $deal);

        $request->validate([
            'stage_id' => ['required'],
        ]);

        $stage = PipelineStage::findOrFail($request->input('stage_id'));

        $stageName = $stage->name ?? 'Unknown';
        $isLostStage = str_contains(mb_strtolower($stageName), 'สูญเสีย') || str_contains(mb_strtolower($stageName), 'lost');

        $deal->update([
            'stage_id' => $stage->id,
            'lost_reason' => null,
            'lost_at' => $isLostStage ? now() : null,
            'won_at' => $stage->is_won ? now() : null,
        ]);

        if ($stage->is_won || $isLostStage) {
            Activity::where('deal_id', $deal->id)
                ->where('activity_type', 'task')
                ->where('description', 'DEAL_PROGRESS_TASK')
                ->where('is_completed', false)
                ->update(['is_completed' => true]);
        }

        Activity::create([
            'deal_id' => $deal->id,
            'customer_id' => $deal->customer_id,
            'user_id' => $user?->id ?? 0,
            'team_id' => $user?->getTeamId(),
            'activity_type' => 'task',
            'name' => 'Stage: '.($isLostStage ? 'Lost' : $stageName),
            'description' => 'DEAL_STAGE_PROGRESS',
            'priority' => 1,
            'is_completed' => true,
        ]);

        return response()->json(['ok' => true]);
    }
}

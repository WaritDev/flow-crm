<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

class SalesPipelineBoardController extends Controller
{
    public function index(Request $request)
    {
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

        $teamIds = collect([]);
        if ($scope === 'mine' && $myTeam) {
            $teamIds = collect([$myTeam->id]);
        } elseif ($scope === 'team' && $activeTeam) {
            $teamIds = collect([$activeTeam->id]);
        } else {
            $teamIds = $orgTeams->pluck('id');
        }

        $deals = Deal::whereIn('team_id', $teamIds->all())
            ->with(['customer.organization', 'stage'])
            ->get()
            ->map(function (Deal $deal) {
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
    }
}


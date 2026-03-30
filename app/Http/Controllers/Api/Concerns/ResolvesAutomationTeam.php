<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Team;
use Illuminate\Http\Request;

trait ResolvesAutomationTeam
{
    /**
     * Team for n8n / automation: explicit team_id on request, else user's team, else first team in org.
     * Matches the dedicated n8n service user (often no team_id on User row).
     */
    protected function resolveAutomationTeamId(object $user, Request $request, int $orgId): int
    {
        $incoming = $request->input('team_id');
        if ($incoming !== null && $incoming !== '') {
            $team = Team::query()
                ->where('organization_id', $orgId)
                ->whereKey((int) $incoming)
                ->firstOrFail();

            return (int) $team->id;
        }

        if ($user->team_id) {
            $team = Team::query()
                ->where('organization_id', $orgId)
                ->whereKey((int) $user->team_id)
                ->first();
            if ($team) {
                return (int) $team->id;
            }
        }

        $first = Team::query()->where('organization_id', $orgId)->orderBy('id')->first();
        if (! $first) {
            abort(422, 'No team in organization; create a team first.');
        }

        return (int) $first->id;
    }
}

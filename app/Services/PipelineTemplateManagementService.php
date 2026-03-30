<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PipelineTemplateManagementService
{
    public function listForOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return PipelineTemplate::query()
            ->where(function ($q) use ($organizationId) {
                $q->whereNull('organization_id')
                    ->orWhere('organization_id', $organizationId);
            })
            ->withCount('stages')
            ->with(['stages' => fn ($q) => $q->orderBy('position')])
            ->orderByRaw('organization_id IS NULL DESC')
            ->orderBy('name')
            ->get();
    }

    public function templateVisibleToOrganization(PipelineTemplate $template, int $organizationId): bool
    {
        return $template->organization_id === null
            || (int) $template->organization_id === $organizationId;
    }

    public function teamMayChangePipelineTemplate(Team $team): bool
    {
        return ! Deal::query()->where('team_id', $team->id)->exists();
    }

    /**
     * @param  array<int, array{name: string, position: int, is_won?: bool}>  $stages
     */
    public function createCustomTemplate(User $user, string $name, ?string $industry, ?string $description, array $stages): PipelineTemplate
    {
        $this->assertManagerWithOrg($user);

        return DB::transaction(function () use ($user, $name, $industry, $description, $stages) {
            $template = PipelineTemplate::create([
                'name' => $name,
                'industry' => $industry,
                'description' => $description,
                'is_default' => false,
                'organization_id' => $user->organization_id,
            ]);

            foreach ($stages as $i => $row) {
                $template->stages()->create([
                    'name' => $row['name'],
                    'position' => $row['position'] ?? ($i + 1),
                    'is_won' => ! empty($row['is_won']),
                    'description' => $row['description'] ?? null,
                ]);
            }

            return $template->load('stages');
        });
    }

    /**
     * @param  array{name?: string, industry?: string|null, description?: string|null, stages?: array<int, array{name: string, position: int, is_won?: bool, description?: string|null}>}|array  $payload
     */
    public function updateTemplate(User $user, PipelineTemplate $template, array $payload): PipelineTemplate
    {
        $this->assertManagerWithOrg($user);
        $this->assertOrgOwnsTemplate($user, $template);

        $teamsAssigned = Team::query()->where('template_id', $template->id)->pluck('id');

        if (isset($payload['name'])) {
            $template->name = (string) $payload['name'];
        }
        if (array_key_exists('industry', $payload)) {
            $template->industry = $payload['industry'];
        }
        if (array_key_exists('description', $payload)) {
            $template->description = $payload['description'];
        }
        $template->save();

        if (! empty($payload['stages']) && is_array($payload['stages'])) {
            if ($teamsAssigned->isNotEmpty()) {
                abort(422, 'Cannot replace stages while this template is assigned to teams. Reassign teams first (only teams with no deals may switch template).');
            }

            DB::transaction(function () use ($template, $payload) {
                $template->stages()->delete();
                foreach ($payload['stages'] as $i => $row) {
                    $template->stages()->create([
                        'name' => $row['name'],
                        'position' => $row['position'] ?? ($i + 1),
                        'is_won' => ! empty($row['is_won']),
                        'description' => $row['description'] ?? null,
                    ]);
                }
            });
        }

        return $template->fresh()->load(['stages' => fn ($q) => $q->orderBy('position')]);
    }

    public function deleteTemplate(User $user, PipelineTemplate $template): void
    {
        $this->assertManagerWithOrg($user);
        $this->assertOrgOwnsTemplate($user, $template);

        if (Team::query()->where('template_id', $template->id)->exists()) {
            abort(422, 'Cannot delete template that is still assigned to a team. Assign teams to another template first (teams with no deals only).');
        }

        $template->delete();
    }

    public function assignTemplateToTeam(User $user, Team $team, PipelineTemplate $template): void
    {
        $this->assertManagerWithOrg($user);

        if ((int) $team->organization_id !== (int) $user->organization_id) {
            abort(403, 'Team does not belong to your organization.');
        }

        if (! $this->templateVisibleToOrganization($template, (int) $user->organization_id)) {
            abort(403, 'This pipeline template is not available for your organization.');
        }

        if (! $this->teamMayChangePipelineTemplate($team)) {
            abort(422, 'This team already has deals; pipeline template cannot be changed.');
        }

        $team->update(['template_id' => $template->id]);
    }

    private function assertManagerWithOrg(User $user): void
    {
        if (! $user->isManager() || ! $user->organization_id) {
            abort(403, 'Only managers with an organization can manage pipeline templates.');
        }
    }

    private function assertOrgOwnsTemplate(User $user, PipelineTemplate $template): void
    {
        if ($template->organization_id === null) {
            abort(403, 'System templates cannot be modified.');
        }
        if ((int) $template->organization_id !== (int) $user->organization_id) {
            abort(403, 'You cannot modify this template.');
        }
    }
}

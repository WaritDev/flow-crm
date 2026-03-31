<?php

namespace App\Http\Controllers;

use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Services\PipelineTemplateManagementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PipelineTemplateController extends Controller
{
    public function __construct(
        private PipelineTemplateManagementService $templates,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isManager() && $user->organization_id, 403);

        $orgId = (int) $user->organization_id;
        $dbTemplates = $this->templates->listForOrganization($orgId);
        $teams = Team::query()
            ->where('organization_id', $orgId)
            ->with('pipelineTemplate:id,name,organization_id')
            ->withCount('deals')
            ->orderBy('name')
            ->get();

        return view('pipeline-templates.index', [
            'templates' => $dbTemplates,
            'teams' => $teams,
        ]);
    }

    public function select(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isManager() && $user->organization_id, 403);

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'template_id' => ['required', 'integer', 'exists:pipeline_templates,id'],
        ]);

        $team = Team::findOrFail($validated['team_id']);
        $template = PipelineTemplate::findOrFail($validated['template_id']);

        $this->templates->assignTemplateToTeam($user, $team, $template);

        return redirect()
            ->route('pipeline-templates.index')
            ->with('success', 'Pipeline template assigned to team «'.$team->name.'».');
    }

    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user->isManager() && $user->organization_id, 403);

        return view('pipeline-templates.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isManager() && $user->organization_id, 403);

        $rawStages = array_values(array_filter(
            $request->input('stages', []),
            fn ($r) => is_array($r) && isset($r['name']) && trim((string) $r['name']) !== ''
        ));
        $request->merge(['stages' => $rawStages]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.is_won' => ['nullable', 'boolean'],
        ]);

        $stages = [];
        foreach ($validated['stages'] as $i => $row) {
            $stages[] = [
                'name' => $row['name'],
                'position' => $i + 1,
                'is_won' => ! empty($row['is_won']),
            ];
        }

        $this->templates->createCustomTemplate(
            $user,
            $validated['name'],
            $validated['industry'] ?? null,
            $validated['description'] ?? null,
            $stages,
        );

        return redirect()
            ->route('pipeline-templates.index')
            ->with('success', 'Organization pipeline template created.');
    }

    public function show(Request $request, PipelineTemplate $pipeline_template): View
    {
        $user = $request->user();
        abort_unless($user->isManager() && $user->organization_id, 403);
        abort_unless(
            $this->templates->templateVisibleToOrganization($pipeline_template, (int) $user->organization_id),
            404
        );

        $pipeline_template->load(['stages' => fn ($q) => $q->orderBy('position')]);
        $teamsUsing = Team::query()
            ->where('template_id', $pipeline_template->id)
            ->where('organization_id', $user->organization_id)
            ->withCount('deals')
            ->get();

        return view('pipeline-templates.show', [
            'template' => $pipeline_template,
            'teamsUsing' => $teamsUsing,
        ]);
    }

    public function edit(Request $request, PipelineTemplate $pipeline_template): View
    {
        $user = $request->user();
        abort_unless($user->isManager() && $user->organization_id, 403);

        if ($pipeline_template->isSystemTemplate()) {
            abort(403, 'System templates cannot be edited here.');
        }
        if ((int) $pipeline_template->organization_id !== (int) $user->organization_id) {
            abort(403);
        }

        $pipeline_template->load(['stages' => fn ($q) => $q->orderBy('position')]);
        $teamsAssigned = Team::query()
            ->where('template_id', $pipeline_template->id)
            ->where('organization_id', $user->organization_id)
            ->exists();

        return view('pipeline-templates.edit', [
            'template' => $pipeline_template,
            'teamsAssigned' => $teamsAssigned,
        ]);
    }

    public function update(Request $request, PipelineTemplate $pipeline_template)
    {
        $user = $request->user();
        abort_unless($user->isManager() && $user->organization_id, 403);

        if ($request->has('stages')) {
            $rawStages = array_values(array_filter(
                $request->input('stages', []),
                fn ($r) => is_array($r) && isset($r['name']) && trim((string) $r['name']) !== ''
            ));
            $request->merge(['stages' => $rawStages]);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stages' => ['sometimes', 'array', 'min:1'],
            'stages.*.name' => ['required_with:stages', 'string', 'max:255'],
            'stages.*.is_won' => ['nullable', 'boolean'],
        ]);

        if (isset($validated['stages'])) {
            $payloadStages = [];
            foreach ($validated['stages'] as $i => $row) {
                $payloadStages[] = [
                    'name' => $row['name'],
                    'position' => $i + 1,
                    'is_won' => ! empty($row['is_won']),
                ];
            }
            $validated['stages'] = $payloadStages;
        }

        $this->templates->updateTemplate($user, $pipeline_template, $validated);

        return redirect()
            ->route('pipeline-templates.index')
            ->with('success', 'Template saved.');
    }

    public function destroy(Request $request, PipelineTemplate $pipeline_template)
    {
        $user = $request->user();
        abort_unless($user->isManager() && $user->organization_id, 403);

        $this->templates->deleteTemplate($user, $pipeline_template);

        return redirect()
            ->route('pipeline-templates.index')
            ->with('success', 'Template deleted.');
    }
}

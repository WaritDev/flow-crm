<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Services\PipelineTemplateManagementService;
use Illuminate\Http\Request;

class SalesPipelineTemplatesController extends Controller
{
    public function __construct(
        private PipelineTemplateManagementService $templates,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->organization_id) {
            abort(403, 'Missing organization.');
        }

        $list = $this->templates->listForOrganization((int) $user->organization_id);

        return response()->json([
            'templates' => $list->map(fn (PipelineTemplate $t) => $this->serializeTemplate($t)),
        ]);
    }

    public function teams(Request $request)
    {
        $user = $request->user();
        if (! $user->organization_id) {
            abort(403, 'Missing organization.');
        }

        $teams = Team::query()
            ->where('organization_id', $user->organization_id)
            ->with(['pipelineTemplate:id,name,organization_id'])
            ->withCount('deals')
            ->orderBy('name')
            ->get();

        return response()->json([
            'teams' => $teams->map(fn (Team $team) => [
                'id' => (string) $team->id,
                'name' => $team->name,
                'template_id' => $team->template_id ? (string) $team->template_id : null,
                'template_name' => $team->pipelineTemplate?->name,
                'deals_count' => (int) $team->deals_count,
                'can_change_template' => $this->templates->teamMayChangePipelineTemplate($team),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.position' => ['nullable', 'integer', 'min:1'],
            'stages.*.is_won' => ['nullable', 'boolean'],
            'stages.*.description' => ['nullable', 'string', 'max:2000'],
        ]);

        $template = $this->templates->createCustomTemplate(
            $user,
            $validated['name'],
            $validated['industry'] ?? null,
            $validated['description'] ?? null,
            $validated['stages'],
        );

        return response()->json($this->serializeTemplate($template), 201);
    }

    public function show(Request $request, PipelineTemplate $pipelineTemplate)
    {
        $user = $request->user();
        if (! $user->organization_id) {
            abort(403, 'Missing organization.');
        }
        if (! $this->templates->templateVisibleToOrganization($pipelineTemplate, (int) $user->organization_id)) {
            abort(404);
        }

        $pipelineTemplate->load(['stages' => fn ($q) => $q->orderBy('position')]);

        return response()->json($this->serializeTemplate($pipelineTemplate));
    }

    public function update(Request $request, PipelineTemplate $pipelineTemplate)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stages' => ['sometimes', 'array', 'min:1'],
            'stages.*.name' => ['required_with:stages', 'string', 'max:255'],
            'stages.*.position' => ['nullable', 'integer', 'min:1'],
            'stages.*.is_won' => ['nullable', 'boolean'],
            'stages.*.description' => ['nullable', 'string', 'max:2000'],
        ]);

        $template = $this->templates->updateTemplate($user, $pipelineTemplate, $validated);

        return response()->json($this->serializeTemplate($template));
    }

    public function destroy(Request $request, PipelineTemplate $pipelineTemplate)
    {
        $user = $request->user();
        $this->templates->deleteTemplate($user, $pipelineTemplate);

        return response()->json(['ok' => true]);
    }

    public function assignTeam(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'template_id' => ['required', 'integer', 'exists:pipeline_templates,id'],
        ]);

        $team = Team::findOrFail($validated['team_id']);
        $template = PipelineTemplate::findOrFail($validated['template_id']);

        $this->templates->assignTemplateToTeam($user, $team, $template);

        return response()->json([
            'ok' => true,
            'team_id' => (string) $team->id,
            'template_id' => (string) $template->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeTemplate(PipelineTemplate $t): array
    {
        return [
            'id' => (string) $t->id,
            'name' => $t->name,
            'industry' => $t->industry,
            'description' => $t->description,
            'is_default' => (bool) $t->is_default,
            'is_system' => $t->isSystemTemplate(),
            'organization_id' => $t->organization_id ? (string) $t->organization_id : null,
            'stages_count' => isset($t->stages_count) ? (int) $t->stages_count : $t->stages->count(),
            'stages' => $t->relationLoaded('stages')
                ? $t->stages->map(fn ($s) => [
                    'id' => (string) $s->id,
                    'name' => $s->name,
                    'position' => (int) $s->position,
                    'is_won' => (bool) $s->is_won,
                    'description' => $s->description,
                ])->values()->all()
                : [],
        ];
    }
}

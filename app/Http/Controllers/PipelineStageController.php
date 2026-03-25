<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PipelineStage;
use App\Models\Deal;

class PipelineStageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $team = $user->team;

        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        // Fetch Stages from Team's Pipeline Template
        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]); // Handle case with no team/template gracefully

        // Load deals with customer and nested organization from the user's team
        $deals = Deal::where('team_id', $team ? $team->id : null)
            ->with(['customer.organization'])
            ->get();

        return view('pipeline-stages.index', compact('stages', 'deals'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Currently used by the Laravel UI, but not required by the SvelteKit Sales flow.
        // If you need HTML create page, implement a Blade view + form fields.
        return redirect()->route('pipeline-stages.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $team = $user->team;

        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        if (!$team || !$team->pipelineTemplate) {
            abort(403, 'No pipeline template found for this team.');
        }

        // Sales in the Svelte flow also needs to be able to create stages.
        if (!in_array($user->role, ['sales', 'manager'], true)) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
            'is_won' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $template = $team->pipelineTemplate;

        $position = $request->input('position');
        if ($position === null) {
            $maxPos = $template->stages()->max('position');
            $position = ($maxPos ?? -1) + 1;
        }

        $isWon = $request->boolean('is_won');

        PipelineStage::create([
            'template_id' => $template->id,
            'name' => $request->input('name'),
            'position' => $position,
            'is_won' => $isWon,
            'description' => $request->input('description'),
        ]);

        return redirect()->route('pipeline-stages.index')->with('success', 'สร้าง Stage เรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

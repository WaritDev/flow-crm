<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $team = $user->team;

        // Fetch Customers
        $customers = Customer::where('team_id', $user->getTeamId())
            ->where('status', 'active')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'label' => "{$c->name} ({$c->nickname}) - {$c->line_id}"
                ];
            });

        // Fetch Stages from Team's Pipeline Template
        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]); // Handle case with no team/template gracefully

        return view('deals.create', compact('customers', 'stages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->team;
        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $request->validate([
            'name' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'value' => 'required|numeric',
            'stage' => 'required',
            'next_action' => 'nullable|string',
            'next_action_date' => 'nullable|date',
            'description' => 'nullable|string',
            'expected_close_date' => 'nullable|date',
            'lost_reason' => 'nullable|string',
        ]);

        if (!$team || !$team->pipelineTemplate) {
            abort(403, 'No pipeline template found for this team.');
        }

        // Customer must belong to the current team (prevents creating deals for other teams)
        $customer = Customer::findOrFail($request->customer_id);
        if ((string) $customer->team_id !== (string) $user->getTeamId()) {
            abort(403, 'Unauthorized customer for this team.');
        }

        $stageId = $request->stage;
        $lostReason = null;
        $lostAt = null;
        $wonAt = null;

        // Handle 'Lost' State
        if ($request->stage === 'lost') {
            $request->validate([
                'lost_reason' => 'required|string',
            ]);

            $lostReason = $request->lost_reason;
            $lostAt = now();
            // Fallback to the first stage ID for database constraint (since stage_id is foreign key)
            $stageId = null;

            if (Auth::user()->team && Auth::user()->team->pipelineTemplate) {
                $firstStage = Auth::user()->team->pipelineTemplate->stages->first();
                $stageId = $firstStage ? $firstStage->id : null;
            }

            if (!$stageId) {
                abort(403, 'No pipeline stages found for this team.');
            }

            if (!$stageId) {
                return back()->withErrors(['stage' => 'No pipeline stages found in system. Please contact admin.']);
            }
        } else {
            // Check if selected stage is 'Won'
            $stage = PipelineStage::where('template_id', $team->pipelineTemplate->id)->find($request->stage);
            if (!$stage) {
                abort(403, 'Unauthorized stage for this team.');
            }
            if ($stage && $stage->is_won) {
                $wonAt = now();
            } else {
                $request->validate([
                    'next_action' => 'required|string',
                    'next_action_date' => 'required|date',
                ]);
            }
        }

        // At this point $stageId should be either a valid stage_id from this team,
        // or resolved to the team's first stage when lost.
        $deal = Deal::create([
            'organization_id' => Auth::user()->getOrganizationId(),
            'team_id' => Auth::user()->getTeamId(),
            'user_id' => Auth::id(),
            'customer_id' => $customer->id,
            'stage_id' => $stageId,
            'name' => $request->name,
            'value' => $request->value,
            'expected_close_date' => $request->expected_close_date,
            'description' => $request->description,
            'next_action' => $request->next_action,
            'next_action_date' => $request->next_action_date,
            'lost_reason' => $lostReason,
            'lost_at' => $lostAt,
            'won_at' => $wonAt,
        ]);

        Activity::create([
            'deal_id' => $deal->id,
            'user_id' => Auth::id(),
            'customer_id' => $customer->id,
            'team_id' => Auth::user()->getTeamId(),
            'activity_type' => 'task',
            'name' => $request->next_action ?? 'Deal Created',
            'due_date' => $request->next_action_date ?? now(),
            'priority' => 1,
            'is_completed' => false
        ]);

        return redirect()->route('pipeline-stages.index')->with('success', 'สร้างดีลเรียบร้อยแล้ว');
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
    public function edit(Deal $deal)
    {
        Gate::authorize('view', $deal);
        $customers = Customer::all();

        $user = Auth::user();
        $team = $user->team;
        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        // ดึง Timeline กิจกรรม
        $activities = $deal->activities()->orderBy('created_at', 'desc')->get();

        return view('deals.edit', compact('deal', 'customers', 'activities', 'stages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Deal $deal)
    {
        Gate::authorize('update', $deal);
        $request->validate([
            'name' => 'required',
            'customer_id' => 'required|exists:customers,id',
            'value' => 'required',
            'stage' => 'required',
            'next_action' => 'nullable|string',
            'next_action_date' => 'nullable|date',
            'description' => 'nullable|string',
            'expected_close_date' => 'nullable|date',
            'lost_reason' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        if ((string) $customer->team_id !== (string) Auth::user()->getTeamId()) {
            return back()->withErrors(['customer_id' => 'ลูกค้านี้ไม่อยู่ในทีมของคุณ']);
        }

        $stageId = $request->stage;
        $data = [
            'name' => $request->name,
            'customer_id' => $request->customer_id,
            'value' => $request->value,
            'expected_close_date' => $request->expected_close_date,
            'description' => $request->description,
            'next_action' => $request->next_action,
            'next_action_date' => $request->next_action_date,
        ];

        if ($request->stage === 'lost') {
            $request->validate([
                'lost_reason' => 'required|string',
            ]);

            $resolvedStageId = null;
            if (Auth::user()->team && Auth::user()->team->pipelineTemplate) {
                $firstStage = Auth::user()->team->pipelineTemplate->stages->first();
                $resolvedStageId = $firstStage ? $firstStage->id : null;
            }
            if (!$resolvedStageId) {
                $fallbackStage = PipelineStage::first();
                $resolvedStageId = $fallbackStage ? $fallbackStage->id : null;
            }
            if (!$resolvedStageId) {
                return back()->withErrors(['stage' => 'No pipeline stages found in system. Please contact admin.']);
            }

            $data['stage_id'] = $resolvedStageId;
            $data['lost_reason'] = $request->lost_reason;
            $data['lost_at'] = $deal->lost_at ?? now();
            $data['won_at'] = null;
        } else {
            $stage = PipelineStage::find($request->stage);
            $stageId = $request->stage;
            $data['lost_at'] = null;
            $data['lost_reason'] = null;
            $data['won_at'] = null;

            if ($stage && $stage->is_won) {
                $data['won_at'] = now();
            } else {
                $request->validate([
                    'next_action' => 'required|string',
                    'next_action_date' => 'required|date',
                ]);
            }

            $data['stage_id'] = $stageId;
        }

        $deal->update($data);

        return redirect()->route('pipeline-stages.index');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deal = Deal::findOrFail($id);
        Gate::authorize('delete', $deal);

        $deal->delete();

        return redirect()->route('pipeline-stages.index')->with('success', 'ลบดีลเรียบร้อยแล้ว');
    }
}

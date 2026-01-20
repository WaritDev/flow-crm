<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'name' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'value' => 'required|numeric',
            'stage' => 'required',
            'next_action' => 'required_unless:stage,lost,won',
            'next_action_date' => 'required_unless:stage,lost,won',
            'expected_close_date' => 'nullable|date',
        ]);

        $stageId = $request->stage;
        $lostReason = null;
        $lostAt = null;
        $wonAt = null;

        // Handle 'Lost' State
        // Handle 'Lost' State
        if ($request->stage === 'lost') {
            $lostReason = $request->lost_reason;
            $lostAt = now();
            // Fallback to the first stage ID for database constraint (since stage_id is foreign key)
            $stageId = null;

            if (Auth::user()->team && Auth::user()->team->pipelineTemplate) {
                $firstStage = Auth::user()->team->pipelineTemplate->stages->first();
                $stageId = $firstStage ? $firstStage->id : null;
            }

            if (!$stageId) {
                // Try to find ANY stage to satisfy FK constraint if specific team template fails
                $fallbackStage = PipelineStage::first();
                $stageId = $fallbackStage ? $fallbackStage->id : null;
            }

            if (!$stageId) {
                return back()->withErrors(['stage' => 'No pipeline stages found in system. Please contact admin.']);
            }
        } else {
            // Check if selected stage is 'Won'
            $stage = PipelineStage::find($request->stage);
            if ($stage && $stage->is_won) {
                $wonAt = now();
            }
        }

        $deal = Deal::create([
            'team_id' => Auth::user()->getTeamId(),
            'user_id' => Auth::id(),
            'customer_id' => $request->customer_id,
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
            'customer_id' => $request->customer_id,
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
        $customers = Customer::all(); // (In real app, filter by team)

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
        // Validation handled simpler here for brevity, matching store logic
        $request->validate([
            'name' => 'required',
            'value' => 'required',
            'stage' => 'required',
        ]);

        $stageId = $request->stage;
        $data = [
            'name' => $request->name,
            'value' => $request->value,
            'expected_close_date' => $request->expected_close_date,
            'description' => $request->description,
            'next_action' => $request->next_action,
            'next_action_date' => $request->next_action_date,
        ];

        if ($request->stage === 'lost') {
            $data['lost_reason'] = $request->lost_reason;
            $data['lost_at'] = $deal->lost_at ?? now();
            // Don't change stage_id if it's already set to a valid stage, just mark as lost
            // OR if strictly enforcing, keep current stage_id
        } else {
            $stage = PipelineStage::find($request->stage);
            $stageId = $request->stage;

            // Clear Lost/Won state if moving back to normal stage
            $data['lost_at'] = null;
            $data['lost_reason'] = null;
            $data['won_at'] = null;

            if ($stage && $stage->is_won) {
                $data['won_at'] = now();
            }

            $data['stage_id'] = $stageId;
        }

        $deal->update($data);

        return redirect()->route('pipeline-stages.index'); // Updated redirect to board
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

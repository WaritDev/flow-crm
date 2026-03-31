<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use App\Models\Target;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Organization;
use App\Models\Team;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->isManager()) {
            return $this->managerDashboard($user);
        }

        return $this->salesDashboard($user);
    }

    private function managerDashboard(User $user)
    {
        $orgId = $user->organization_id;
        $currentMonth = request('month', now()->month);
        $currentYear = request('year', now()->year);
        $reqMonth = str_pad($currentMonth, 2, '0', STR_PAD_LEFT); 
        $reqYear = $currentYear;
        $cacheKey = "manager_dashboard_sales_{$orgId}_{$currentYear}_{$reqMonth}";
        $expiresAt = now()->endOfDay();
        $cachedData = Cache::remember($cacheKey, $expiresAt, function () use ($orgId, $currentMonth, $currentYear) {
            
            $selectedDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
            $lastMonthDate = $selectedDate->copy()->subMonth();

            $thisMonthRev = Deal::where('organization_id', $orgId)
                                ->whereNotNull('won_at')
                                ->whereMonth('won_at', $currentMonth)
                                ->whereYear('won_at', $currentYear)
                                ->sum('value');

            $lastMonthRev = Deal::where('organization_id', $orgId)
                                ->whereNotNull('won_at')
                                ->whereMonth('won_at', $lastMonthDate->month)
                                ->whereYear('won_at', $lastMonthDate->year)
                                ->sum('value');
            
            $growth = $lastMonthRev > 0 ? round((($thisMonthRev - $lastMonthRev) / $lastMonthRev) * 100, 1) : 0;
            
            $activeDealsQuery = Deal::where('organization_id', $orgId)
                ->whereHas('stage', fn ($q) => $q->where('is_won', false)
                    ->whereRaw('LOWER(COALESCE(name, "")) NOT LIKE ?', ['%lost%'])
                    ->where('name', 'not like', '%สูญเสีย%'));
            
            $activeDealsCount = $activeDealsQuery->count();
            $activeDealsValue = $activeDealsQuery->sum('value');

            $totalCompletedDeals = Deal::where('organization_id', $orgId)
                ->where(function($q) use ($currentMonth, $currentYear) {
                    $q->where(fn($qWon) => $qWon->whereNotNull('won_at')->whereMonth('won_at', $currentMonth)->whereYear('won_at', $currentYear))
                        ->orWhere(fn($qLost) => $qLost->whereNotNull('lost_at')->whereMonth('lost_at', $currentMonth)->whereYear('lost_at', $currentYear));
                })->count();

            $totalWonDeals = Deal::where('organization_id', $orgId)
                                ->whereNotNull('won_at')
                                ->whereMonth('won_at', $currentMonth)
                                ->whereYear('won_at', $currentYear)
                                ->count();
                                
            $avgConversion = $totalCompletedDeals > 0 ? round(($totalWonDeals / $totalCompletedDeals) * 100, 1) : 0;

            $lastMonthCompletedDeals = Deal::where('organization_id', $orgId)
                ->where(function($q) use ($lastMonthDate) {
                    $q->where(fn($qWon) => $qWon->whereNotNull('won_at')->whereMonth('won_at', $lastMonthDate->month)->whereYear('won_at', $lastMonthDate->year))
                        ->orWhere(fn($qLost) => $qLost->whereNotNull('lost_at')->whereMonth('lost_at', $lastMonthDate->month)->whereYear('lost_at', $lastMonthDate->year));
                })->count();

            $lastMonthWonDeals = Deal::where('organization_id', $orgId)
                                ->whereNotNull('won_at')
                                ->whereMonth('won_at', $lastMonthDate->month)
                                ->whereYear('won_at', $lastMonthDate->year)
                                ->count();

            $lastMonthConversion = $lastMonthCompletedDeals > 0 ? round(($lastMonthWonDeals / $lastMonthCompletedDeals) * 100, 1) : 0;
            $conversionGrowth = $avgConversion - $lastMonthConversion;

            $team = Team::where('organization_id', $orgId)->first();
            $templateId = $team ? $team->template_id : 1; 

            $stages = PipelineStage::where('template_id', $templateId)
                                    ->withCount(['deals' => fn($q) => $q->where('organization_id', $orgId)])
                                    ->withSum(['deals' => fn($q) => $q->where('organization_id', $orgId)], 'value')
                                    ->orderBy('position')->get();

            $maxStageValue = $stages->max('deals_sum_value') ?: 1; 
            $pipelineSummary = $stages->map(fn($stage) => [
                'stage' => $stage->name,
                'count' => $stage->deals_count,
                'value' => $stage->deals_sum_value ?: 0,
                'max_value' => $maxStageValue 
            ]);

            $salesPerformance = User::where('organization_id', $orgId)
                                    ->where('role', 'sales')
                                    ->withSum(['deals' => function($q) use ($currentMonth, $currentYear) {
                                        $q->whereHas('stage', fn($sq) => $sq->where('is_won', true))
                                            ->whereMonth('won_at', $currentMonth)
                                            ->whereYear('won_at', $currentYear);
                                    }], 'value')
                                    ->orderByDesc('deals_sum_value')
                                    ->take(5)
                                    ->get();

            return compact(
                'thisMonthRev', 'growth', 'activeDealsCount', 'activeDealsValue', 
                'avgConversion', 'conversionGrowth', 'pipelineSummary', 'salesPerformance'
            );
        });

        $orgTarget = Target::where('targetable_id', $orgId)
                            ->where('targetable_type', Organization::class)
                            ->where('month', $currentMonth)
                            ->where('year', $currentYear)
                            ->where('type', 'revenue')
                            ->first();

        $companyTargetAmount = $orgTarget ? $orgTarget->amount : 0;
        $targetAchievement = $companyTargetAmount > 0 ? round(($cachedData['thisMonthRev'] / $companyTargetAmount) * 100, 1) : 0;

        $stats = [
            'total_revenue' => $cachedData['thisMonthRev'],
            'target_achievement' => $targetAchievement, 
            'active_deals' => $cachedData['activeDealsCount'],
            'active_deals_value' => $cachedData['activeDealsValue'], 
            'avg_conversion' => $cachedData['avgConversion'],
            'revenue_growth' => $cachedData['growth'],
            'conversion_growth' => $cachedData['conversionGrowth']
        ];

        $allSales = User::where('organization_id', $orgId)
                        ->where('role', 'sales')
                        ->with(['targets' => fn($q) => $q->where('month', $currentMonth)->where('year', $currentYear)])
                        ->get();

        $salesTargetsLookup = $allSales->keyBy('id')->map(function($user) {
            return $user->targets->first()->amount ?? 0;
        });

        $topPerformers = [];
        $teamLabels = [];
        $teamData = [];
        $teamTargets = []; 

        foreach ($cachedData['salesPerformance'] as $salesUser) {
            $actualAmount = $salesUser->deals_sum_value ?: 0;
            $targetAmount = $salesTargetsLookup->get($salesUser->id, 0); 

            $topPerformers[] = [
                'name' => $salesUser->name,
                'amount' => $actualAmount,
                'avatar' => substr($salesUser->name, 0, 1),
            ];

            $teamLabels[] = $salesUser->name;
            $teamData[] = $actualAmount;
            $teamTargets[] = $targetAmount;
        }

        $teamPerformance = [
            'labels' => $teamLabels,
            'data' => $teamData,
            'targets' => $teamTargets
        ];

        $pipelineSummary = $cachedData['pipelineSummary'];

        return view('dashboard.manager', compact('stats', 'teamPerformance', 'pipelineSummary', 'topPerformers', 'allSales', 'companyTargetAmount', 'reqMonth', 'reqYear'));
    }

    private function salesDashboard()
    {
        // 1. Mock Top Stats
        $stats = [
            'todo_today' => 8,
            'overdue_deals' => 3,
            'confirmed_quotes' => 5,
            'revenue_month' => 420000,
            'revenue_growth' => 12, // %
        ];

        // 2. Mock daily activities
        $activities = [
            [
                'id' => 1,
                'action_type' => 'LINE',
                'customer_name' => 'Somchai K.',
                'description' => 'Send Spa package quote',
                'priority' => 'urgent', // urgent, medium, normal
                'time' => '10:00',
                'status' => 'pending'
            ],
            [
                'id' => 2,
                'action_type' => 'Follow-up',
                'customer_name' => 'Nok S.',
                'description' => 'Book facial treatment appointment',
                'priority' => 'urgent',
                'time' => '11:30',
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'action_type' => 'LINE',
                'customer_name' => 'Wipha R.',
                'description' => 'Notify product ready to ship',
                'priority' => 'medium',
                'time' => '14:00',
                'status' => 'pending'
            ],
            [
                'id' => 4,
                'action_type' => 'Close deal',
                'customer_name' => 'Manop T.',
                'description' => 'Confirm order and collect payment',
                'priority' => 'normal',
                'time' => '16:00',
                'status' => 'pending'
            ],
        ];

        // 3. Mock chart data (revenue snapshot)
        $chartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            'data' => [120000, 150000, 220000, 310000, 280000, 390000, 420000],
            'projected' => [120000, 155000, 230000, 320000, 350000, 410000, 450000]
        ];

        return view('dashboard.sales', compact('stats', 'activities', 'chartData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

    public function updateTargets(Request $request)
    {
        $user = $request->user();
        $orgId = $user->organization_id;
        $month = $request->input('month');
        $year = $request->input('year');

        if ($request->filled('org_target')) {
            Target::updateOrCreate(
                [
                    'targetable_id' => $orgId,
                    'targetable_type' => \App\Models\Organization::class,
                    'month' => $month,
                    'year' => $year,
                    'type' => 'revenue',
                ],
                ['amount' => $request->input('org_target')]
            );
        }

        if ($request->has('user_targets')) {
            foreach ($request->input('user_targets') as $userId => $amount) {
                $targetAmount = $amount !== null && $amount !== '' ? $amount : 0;
                
                Target::updateOrCreate(
                    [
                        'targetable_id' => $userId,
                        'targetable_type' => \App\Models\User::class,
                        'month' => $month,
                        'year' => $year,
                        'type' => 'revenue',
                    ],
                    ['amount' => $targetAmount]
                );
            }
        }

        return back()->with('success', 'save information successfully!');
    }
}

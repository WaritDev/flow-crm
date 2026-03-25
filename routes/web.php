<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\PipelineTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvitationController;
use App\Models\Invitation;

Route::get('/', function () {
    return view('index');
});

Route::middleware(['auth'])->get('/me', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'id' => (string) $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'team_id' => $user->team_id,
        'organization_id' => $user->organization_id,
    ]);
})->name('me');

Route::middleware(['auth'])->group(function () {
    Route::get('/api/sales/csrf', function () {
        return response()->json(['csrf_token' => csrf_token()]);
    });

    Route::get('/api/sales/pipeline-board', function (Request $request) {
        $user = $request->user();
        $team = $user->team;

        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        $deals = \App\Models\Deal::where('team_id', $team ? $team->id : null)
            ->with(['customer.organization', 'stage'])
            ->get()
            ->map(function ($deal) {
                $daysInStage = $deal->updated_at ? $deal->updated_at->diffInDays(now()) : 0;

                return [
                    'id' => (string) $deal->id,
                    'stage_id' => $deal->stage_id ? (string) $deal->stage_id : null,
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
                    'days_in_stage' => $daysInStage,
                    'updated_at' => $deal->updated_at ? $deal->updated_at->toDateTimeString() : null,
                ];
            });

        return response()->json([
            'stages' => $stages->map(fn($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
            'deals' => $deals->values(),
        ]);
    });

    Route::get('/api/sales/deals/create-data', function (Request $request) {
        $user = $request->user();
        $team = $user->team;

        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $customers = \App\Models\Customer::where('team_id', $user->getTeamId())
            ->where('status', 'active')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => (string) $c->id,
                    'label' => "{$c->name} ({$c->nickname}) - {$c->line_id}",
                    'name' => $c->name,
                    'nickname' => $c->nickname,
                ];
            });

        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        return response()->json([
            'customers' => $customers->values(),
            'stages' => $stages->map(fn($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
        ]);
    });

    Route::get('/api/sales/deals/{deal}/edit-data', function (Request $request, \App\Models\Deal $deal) {
        $user = $request->user();
        Gate::authorize('view', $deal);

        $team = $user->team;
        if (!$team && $user->organization) {
            $team = $user->organization->teams()->first();
        }

        $stages = $team && $team->pipelineTemplate
            ? $team->pipelineTemplate->stages
            : collect([]);

        $customers = \App\Models\Customer::all()->map(function ($c) {
            return [
                'id' => (string) $c->id,
                'label' => "{$c->name} ({$c->nickname}) - {$c->line_id}",
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
                    'created_at' => $a->created_at ? $a->created_at->toDateTimeString() : null,
                    'is_completed' => (bool) $a->is_completed,
                    'user_name' => $u?->name ?? null,
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
            'stages' => $stages->map(fn($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'position' => (int) $s->position,
                'is_won' => (bool) $s->is_won,
            ])->values(),
            'activities' => $activities->values(),
        ]);
    });

    // Move deal stage (pipeline board drag/drop)
    Route::put('/api/sales/deals/{deal}/move-stage', function (Request $request, \App\Models\Deal $deal) {
        Gate::authorize('update', $deal);

        $request->validate([
            'stage_id' => ['required'],
        ]);

        $stage = \App\Models\PipelineStage::findOrFail($request->input('stage_id'));

        $deal->update([
            'stage_id' => $stage->id,
            'lost_reason' => null,
            'lost_at' => null,
            'won_at' => $stage->is_won ? now() : null,
        ]);

        return response()->json(['ok' => true]);
    });
});

// -------------------- Existing routes --------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::get('/pipeline-stages', [PipelineStageController::class, 'index'])->name('pipeline-stages.index');
    Route::get('/pipeline-stages/create', [PipelineStageController::class, 'create'])->name('pipeline-stages.create');
    Route::resource('pipeline-stages', PipelineStageController::class);
    Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
    Route::get('/deals/create', [DealController::class, 'create'])->name('deals.create');
    Route::get('/deals/{id}/edit', [DealController::class, 'edit'])->name('deals.edit');
    Route::resource('deals', DealController::class);
    Route::get('/pipeline-templates', [PipelineTemplateController::class, 'index'])->name('pipeline-templates.index');
//    Route::get('/pipelines-templates/create', [PipelineTemplateController::class, 'create'])->name('pipelines.create');
    Route::post('/pipeline-templates/select', [PipelineTemplateController::class, 'select'])->name('pipeline-templates.select');
    Route::resource('pipeline-templates', PipelineTemplateController::class);
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
//    Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
//    Route::get('/activities/{id}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
    Route::resource('activities', ActivityController::class);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth', 'role:manager,admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // Manager
    Route::middleware(['role:manager'])->group(function () {

        // Team Management
        Route::resource('teams', TeamController::class);

        // Custom Routes
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.add_member');
        Route::delete('/teams/members/{user}', [TeamController::class, 'removeMember'])->name('teams.remove_member');

        // Invitation Management
        Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

        // Dashboard Targets Update
        Route::post('/dashboard/targets', [DashboardController::class, 'updateTargets'])->name('targets.update');
    });

    // Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('organizations', OrganizationController::class);
        Route::get('/organization-users', [OrganizationController::class, 'usersIndex'])->name('organization-users.index');
    });

    // Invitation Acceptance Route
    Route::get('/register/invite/{token}', function ($token) {
        $invitation = \App\Models\Invitation::where('token', $token)
                        ->where('expires_at', '>', now())
                        ->firstOrFail(); 

        return view('auth.register-invite', compact('invitation'));
    })->name('register.invite');
    Route::post('/register/invite', [InvitationController::class, 'accept'])->name('register.invite.submit');

require __DIR__.'/auth.php';

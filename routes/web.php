<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\Sales\SalesCsrfController;
use App\Http\Controllers\Api\Sales\SalesCustomersController as SalesCustomersApiController;
use App\Http\Controllers\Api\Sales\SalesDealsController;
use App\Http\Controllers\Api\Sales\SalesPipelineBoardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\Integrations\N8nSetupController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\PipelineTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::middleware(['auth'])->get('/me', MeController::class)->name('me');

Route::middleware(['auth'])->group(function () {
    Route::get('/api/sales/csrf', [SalesCsrfController::class, 'show']);
    Route::get('/api/sales/pipeline-board', [SalesPipelineBoardController::class, 'index']);
    Route::get('/api/sales/deals/create-data', [SalesDealsController::class, 'createData']);
    Route::get('/api/sales/deals/{deal}/edit-data', [SalesDealsController::class, 'editData']);
    Route::put('/api/sales/deals/{deal}/move-stage', [SalesDealsController::class, 'moveStage']);

    Route::get('/api/sales/customers', [SalesCustomersApiController::class, 'index']);
    Route::post('/api/sales/customers', [SalesCustomersApiController::class, 'store']);
    Route::get('/api/sales/customers/{customer}', [SalesCustomersApiController::class, 'show']);
    Route::put('/api/sales/customers/{customer}', [SalesCustomersApiController::class, 'update']);

    require __DIR__.'/sales_dashboard.php';
    require __DIR__.'/sales_activities.php';
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

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth', 'role:manager,admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    Route::middleware(['role:manager'])->group(function () {
        Route::resource('teams', TeamController::class);
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.add_member');
        Route::delete('/teams/members/{user}', [TeamController::class, 'removeMember'])->name('teams.remove_member');
        Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::post('/dashboard/targets', [DashboardController::class, 'updateTargets'])->name('targets.update');

        Route::get('/integrations/n8n/setup', [N8nSetupController::class, 'show'])->name('integrations.n8n.setup');
        Route::post('/integrations/n8n/rotate-token', [N8nSetupController::class, 'rotateToken'])->name('integrations.n8n.rotate-token');
        Route::post('/integrations/n8n/line-token', [N8nSetupController::class, 'upsertLineAccessToken'])->name('integrations.n8n.line-token');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('organizations', OrganizationController::class);
        Route::get('/organization-users', [OrganizationController::class, 'usersIndex'])->name('organization-users.index');
    });
});

// Invitation Acceptance Route (guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/register/invite/{token}', function ($token) {
        $invitation = \App\Models\Invitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('auth.register-invite', compact('invitation'));
    })->name('register.invite');
    Route::post('/register/invite', [InvitationController::class, 'accept'])->name('register.invite.submit');
});

require __DIR__.'/auth.php';

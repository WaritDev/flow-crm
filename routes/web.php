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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::middleware(['auth', 'verified'])->group(function () {
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
    // sales -> Customer Management
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth', 'role:manager,admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Manager
    Route::middleware(['role:manager'])->group(function () {

        // Team Management
        Route::resource('teams', TeamController::class);

        // Custom Routes
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.add_member');
        Route::delete('/teams/members/{user}', [TeamController::class, 'removeMember'])->name('teams.remove_member');
    });

    // Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('organizations', OrganizationController::class);
    });

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return view('index');
    }
    return view('index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Sales -> Customer Management
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Manager
    Route::middleware(['role:manager'])->group(function () {

        // Team Management
        Route::resource('teams', TeamController::class);

        // Custom Routes
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.add_member');
        Route::delete('/teams/members/{user}', [TeamController::class, 'removeMember'])->name('teams.remove_member');

        // User Management (Sales Management)
        Route::resource('users', UserController::class)->except(['show']);
    });

});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

// mock
Route::get('/', function () {
    return view('teams.index');
});

Route::get('/teams', function () {
    return view('teams.index');
})->name('teams.index');

Route::get('/users', function () {
    return view('users.index');
})->name('users.index');

Route::get('/users/create', function () { return view('users.create'); })->name('users.create');
Route::get('/users/edit-mock', function () { return view('users.edit'); })->name('users.edit');
// end mock

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::resource('customers', CustomerController::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\Api\Sales\SalesActivitiesController;

// Note: this file is included from inside the existing auth middleware group
// in `routes/web.php`.

// -------------------- Action Stream / Activities API (Sales) --------------------
Route::get('/api/sales/activities', [SalesActivitiesController::class, 'index']);

Route::post('/api/sales/activities/{activity}/complete', [SalesActivitiesController::class, 'complete']);


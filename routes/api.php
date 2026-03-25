<?php

use App\Http\Controllers\Api\Sales\SalesN8nController;
use Illuminate\Support\Facades\Route;

// n8n automation endpoints (Bearer token via Sanctum personal access tokens)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/sales/deals/inactive', [SalesN8nController::class, 'inactiveDeals']);
    Route::post('/sales/automation/deals/{deal}/next-action', [SalesN8nController::class, 'upsertNextAction']);
});


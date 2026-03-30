<?php

use App\Http\Controllers\Api\LineInboundController;
use App\Http\Controllers\Api\Sales\SalesN8nController;
use Illuminate\Support\Facades\Route;

// n8n + automation (Bearer token or SPA session via Sanctum)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/sales/deals/inactive', [SalesN8nController::class, 'inactiveDeals']);
    Route::get('/sales/automation/deals/{deal}/pipeline-context', [SalesN8nController::class, 'dealPipelineContext']);
    Route::post('/sales/automation/deals/{deal}/next-action', [SalesN8nController::class, 'upsertNextAction']);

    // LINE → n8n → Laravel (Inbound Intake)
    Route::get('/integrations/line/context', [LineInboundController::class, 'customerContext']);
    Route::post('/integrations/line/customers', [LineInboundController::class, 'upsertCustomer']);
    Route::post('/integrations/line/activities', [LineInboundController::class, 'storeActivity']);
    Route::post('/integrations/line/conversation', [LineInboundController::class, 'appendConversation']);
    Route::get('/integrations/line/conversation', [LineInboundController::class, 'showConversation']);
});

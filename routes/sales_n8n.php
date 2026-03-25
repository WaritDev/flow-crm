<?php

use App\Http\Controllers\Api\Sales\SalesN8nController;

// GET /api/sales/deals/inactive?hours=48
// Used by n8n monitoring workflow to find "inactive" deals.
Route::get('/api/sales/deals/inactive', [SalesN8nController::class, 'inactiveDeals']);

// POST /api/sales/automation/deals/{deal}/next-action
// Used by n8n to create a follow-up task (Action-Driven) by updating deal.next_action + next_action_date.
Route::post('/api/sales/automation/deals/{deal}/next-action', [SalesN8nController::class, 'upsertNextAction']);


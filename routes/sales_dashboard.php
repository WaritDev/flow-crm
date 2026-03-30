<?php

use App\Http\Controllers\Api\Sales\SalesDashboardController;

Route::get('/api/sales/dashboard', [SalesDashboardController::class, 'show']);


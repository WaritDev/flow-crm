<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;

class SalesCsrfController extends Controller
{
    public function show()
    {
        return response()->json(['csrf_token' => csrf_token()]);
    }
}


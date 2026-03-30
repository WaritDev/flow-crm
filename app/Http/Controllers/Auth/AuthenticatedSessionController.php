<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $user = $request->user();

        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Account is inactive. Please contact support.',
            ]);
        }

        $request->session()->regenerate();
        $user->update(['last_login' => now()]);

        if ($user->role === 'sales') {
            $salesAppUrl = rtrim((string) env('SALES_APP_URL', 'http://localhost:3000'), '/');
            return redirect()->away($salesAppUrl . '/pipeline-stages');
        }

        if ($user->role === 'admin') {
            return redirect()->intended(route('organizations.index'));
        }

        if ($user->role === 'manager') {
            return redirect()->intended(route('dashboard.index'));
        }

        return redirect()->intended(route('dashboard.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
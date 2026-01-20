<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        DB::transaction(function () use ($request, &$organizationId, &$createdOrg) {

            if ($request->role === 'manager') {
                $createdOrg = Organization::create([
                    'name' => $request->org_name,
                    'slug' => Str::slug($request->org_name) . '-' . uniqid(),
                    'size' => $request->org_size,
                    'description' => $request->org_description,
                    'invite_code' => 'INV-' . strtoupper(Str::random(6)),
                ]);

                $organizationId = $createdOrg->id;
            } elseif ($request->role === 'sales') {
                $request->validate([
                    'invite_token' => ['required', 'string'],
                ]);

                $org = Organization::where('invite_code', $request->invite_token)->first();

                if (! $org) {
                    throw ValidationException::withMessages([
                        'invite_token' => ['Invalid invitation code. Please check with your manager.'],
                    ]);
                }

                $organizationId = $org->id;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'organization_id' => $organizationId,
                'last_login' => now(),
            ]);

            event(new Registered($user));
            Auth::login($user);
        });

        if ($request->role === 'manager' && isset($createdOrg)) {
            return view('auth.register-success', ['organization' => $createdOrg]);
        }

        return redirect(route('dashboard', absolute: false));
    }
}

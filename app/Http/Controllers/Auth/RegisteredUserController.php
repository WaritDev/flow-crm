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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}]+(\s+[\p{L}\p{M}]+)+$/u'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:'.User::class.',email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['manager', 'sales'])],
            'org_name' => ['required_if:role,manager', 'nullable', 'string', 'max:255'],
            'org_size' => ['required_if:role,manager', 'nullable', Rule::in(['1-10', '11-50', '50+'])],
            'org_description' => ['nullable', 'string', 'max:1000'],
            'invite_token' => ['required_if:role,sales', 'nullable', 'string', 'max:64'],
        ], [
            'email.unique' => 'This email is already registered. Please log in or use a different email.',
            'name.regex' => 'Please enter your first and last name.',
        ]);

        DB::transaction(function () use ($validated, &$organizationId, &$createdOrg) {

            if ($validated['role'] === 'manager') {
                $createdOrg = Organization::create([
                    'name' => $validated['org_name'],
                    'slug' => Str::slug($validated['org_name']).'-'.uniqid(),
                    'size' => $validated['org_size'],
                    'description' => $validated['org_description'] ?? null,
                    'invite_code' => 'INV-'.strtoupper(Str::random(6)),
                ]);

                $organizationId = $createdOrg->id;
            } elseif ($validated['role'] === 'sales') {
                $org = Organization::where('invite_code', $validated['invite_token'])->first();

                if (! $org) {
                    throw ValidationException::withMessages([
                        'invite_token' => ['Invalid invitation code. Please check with your manager.'],
                    ]);
                }

                $organizationId = $org->id;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'organization_id' => $organizationId,
                'last_login' => now(),
            ]);

            event(new Registered($user));
            Auth::login($user);
        });

        if ($validated['role'] === 'manager' && isset($createdOrg)) {
            // Next step: integrations setup (generate n8n token + show LINE webhook)
            $request->session()->put('flowcrm.n8n_generate_token', true);

            return redirect()->route('integrations.n8n.setup');
        }

        return redirect(route('dashboard.index', absolute: false));
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Sales self-registration (invite code). Used by the SvelteKit app only.
 * Manager/org setup uses {@see RegisteredUserController}.
 */
class SalesRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register-sales');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}]+(\s+[\p{L}\p{M}]+)+$/u'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'invite_token' => ['required', 'string', 'max:64'],
        ], [
            'email.unique' => 'This email is already registered. Please log in or use a different email.',
            'name.regex' => 'Please enter your first and last name.',
        ]);

        $org = Organization::where('invite_code', $validated['invite_token'])->first();

        if (! $org) {
            throw ValidationException::withMessages([
                'invite_token' => ['Invalid invitation code. Please check with your manager.'],
            ]);
        }

        $salesTeamId = Team::query()
            ->where('organization_id', $org->id)
            ->orderBy('id')
            ->value('id');

        if ($salesTeamId === null) {
            throw ValidationException::withMessages([
                'invite_token' => [
                    'This organization has no team yet. Ask a manager to create a team before inviting sales.',
                ],
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'sales',
            'organization_id' => $org->id,
            'team_id' => $salesTeamId,
            'last_login' => now(),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard.index');
    }
}

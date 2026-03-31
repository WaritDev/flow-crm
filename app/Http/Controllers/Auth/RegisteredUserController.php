<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\PipelineTemplate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Manager registration and organization bootstrap. Sales use {@see SalesRegisteredUserController}.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}]+(\s+[\p{L}\p{M}]+)+$/u'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'org_name' => ['required', 'string', 'max:255'],
            'org_size' => ['required', Rule::in(['1-10', '11-50', '50+'])],
            'org_description' => ['nullable', 'string', 'max:1000'],
        ], [
            'email.unique' => 'This email is already registered. Please log in or use a different email.',
            'name.regex' => 'Please enter your first and last name.',
        ]);

        $managerTeamId = null;
        $createdOrg = null;

        DB::transaction(function () use ($validated, &$createdOrg, &$managerTeamId) {
            $createdOrg = Organization::create([
                'name' => $validated['org_name'],
                'slug' => Str::slug($validated['org_name']).'-'.uniqid(),
                'size' => $validated['org_size'],
                'description' => $validated['org_description'] ?? null,
                'invite_code' => 'INV-'.strtoupper(Str::random(6)),
            ]);

            $defaultTemplateId = PipelineTemplate::query()
                ->whereNull('organization_id')
                ->where('name', 'Default Pipeline')
                ->value('id');

            $team = Team::create([
                'organization_id' => $createdOrg->id,
                'name' => 'Primary sales team',
                'template_id' => $defaultTemplateId,
            ]);
            $managerTeamId = $team->id;

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'manager',
                'organization_id' => $createdOrg->id,
                'team_id' => $managerTeamId,
                'last_login' => now(),
            ]);

            event(new Registered($user));
            Auth::login($user);
        });

        $request->session()->put('flowcrm.n8n_generate_token', true);

        return redirect()->route('integrations.n8n.setup');
    }
}

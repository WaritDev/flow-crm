<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Models\Organization;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        Gate::authorize('viewAny', User::class);

        $query = User::with(['team', 'organization'])
            ->where('id', '!=', $currentUser->id);

        if ($currentUser->role === 'admin') {
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }
        } 
        elseif ($currentUser->role === 'manager') {
            $query->where('organization_id', $currentUser->organization_id)
                ->where('role', 'sales');
        }

        $users = $query->latest()->paginate(10);
        $selectedOrg = $request->filled('organization_id') 
            ? Organization::find($request->organization_id) 
            : null;

        return view('users.index', compact('users', 'selectedOrg'));
    }

    public function create(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        Gate::authorize('create', User::class);
        $targetOrgId = $request->query('organization_id');
        $orgId = $currentUser->isAdmin() ? $targetOrgId : $currentUser->organization_id;
        $teams = Team::where('organization_id', $orgId)->get();
        return view('users.create', compact('teams', 'targetOrgId'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        Gate::authorize('create', User::class);
        $orgId = $currentUser->isAdmin() ? $request->organization_id : $currentUser->organization_id;
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}]+(\s+[\p{L}\p{M}]+)+$/u'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['manager', 'sales'])],
            'organization_id' => [
                Rule::requiredIf($currentUser->role === 'admin'), 
                'nullable', 
                'exists:organizations,id'
            ],
            'team_id' => [
                'nullable',
                Rule::exists('teams', 'id')->where(function ($query) use ($orgId) {
                    return $query->where('organization_id', $orgId);
                })
            ],
        ]);

        $role = $currentUser->isAdmin() ? $request->role : 'sales';
        $teamId = ($role === 'manager') ? null : $request->team_id;

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'team_id' => $teamId,
            'organization_id' => $orgId,
        ]);

        return redirect()->route('users.index', ['organization_id' => $orgId])
            ->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        Gate::authorize('update', $user);
        $teams = Team::where('organization_id', $user->organization_id)->get();
        $targetOrgId = $user->organization_id;
        return view('users.edit', compact('user', 'teams', 'targetOrgId'));
    }

    public function update(Request $request, User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        Gate::authorize('update', $user);
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                'regex:/^[\p{L}\p{M}]+(\s+[\p{L}\p{M}]+)+$/u'
            ],
            'email' => [
                'required', 'string', 'lowercase', 'email:rfc,dns', 'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => ['required', Rule::in(['manager', 'sales'])],
            'team_id' => [
                'nullable',
                Rule::exists('teams', 'id')->where(function ($query) use ($user) {
                    return $query->where('organization_id', $user->organization_id);
                })
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.regex' => 'The name must follow the "First Last" format.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($currentUser->role === 'admin') {
            $user->role = $request->role;
        }

        $user->team_id = ($user->role === 'manager') ? null : $request->team_id;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('users.index', ['organization_id' => $user->organization_id])
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        Gate::authorize('update', $user);
        $user->is_active = !$user->is_active;
        $user->save();
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User account has been {$status}!");
    }
}

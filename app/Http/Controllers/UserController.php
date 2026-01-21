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

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with('team')
            ->where('organization_id', Auth::user()->organization_id)
            ->where('role', 'sales')
            ->latest()
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        Gate::authorize('create', User::class);
        $teams = Team::where('organization_id', Auth::user()->organization_id)->get();
        return view('users.create', compact('teams'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                'regex:/^[\p{L}\p{M}]+(\s+[\p{L}\p{M}]+)+$/u'
            ],
            'email' => [
                'required', 'string', 'lowercase', 'email:rfc,dns', 'max:255',
                'unique:'.User::class
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'team_id' => [
                'nullable',
                Rule::exists('teams', 'id')->where(function ($query) {
                    return $query->where('organization_id', Auth::user()->organization_id);
                })
            ],
        ], [
            'name.regex' => 'The name must contain only letters and follow the "First Last" format.',
            'email.email' => 'The email address is invalid or the domain does not exist.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'sales',
            'team_id' => $request->team_id,
            'last_login' => null,
            'organization_id' => Auth::user()->organization_id,
        ]);

        return redirect()->route('users.index')->with('success', 'New sales Rep created successfully!');
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);
        $teams = Team::where('organization_id', Auth::user()->organization_id)->get();
        return view('users.edit', compact('user', 'teams'));
    }

    public function update(Request $request, User $user)
    {
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
            'team_id' => [
                'nullable',
                Rule::exists('teams', 'id')->where(function ($query) {
                    return $query->where('organization_id', Auth::user()->organization_id);
                })
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->team_id = $request->team_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}

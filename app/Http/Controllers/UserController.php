<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('team')
            ->where('role', 'sales')
            ->latest()
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $teams = Team::all();
        return view('users.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}]+(\s+[\p{L}]+)+$/u'
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:255',
                'unique:'.User::class
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'team_id' => ['nullable', 'exists:teams,id'],
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

        return redirect()->route('users.index')->with('success', 'New Sales Rep created successfully!');
    }

    public function edit(User $user)
    {
        $teams = Team::all();
        return view('users.edit', compact('user', 'teams'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}]+(\s+[\p{L}]+)+$/u'
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'team_id' => ['nullable', 'exists:teams,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.regex' => 'The name must contain only letters and follow the "First Last" format.',
            'email.email' => 'The email address is invalid or the domain does not exist.',
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
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with('members')->latest()->get();
        $availableUsers = User::whereNull('team_id')
            ->where('role', 'sales')
            ->get();

        return view('teams.index', compact('teams', 'availableUsers'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Team::create(['name' => $request->name]);
        return back()->with('success', 'Team created successfully!');
    }

    public function destroy(Team $team)
    {
        $team->members()->update(['team_id' => null]);

        $team->delete();
        return back()->with('success', 'Team deleted.');
    }

    public function addMember(Request $request, Team $team)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->team_id = $team->id;
        $user->save();

        return back()->with('success', 'Member added to team successfully.');
    }

    public function removeMember(User $user)
    {
        $user->team_id = null;
        $user->save();
        return back()->with('success', 'Member removed from team.');
    }
}

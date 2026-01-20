<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $organizations = Organization::all();
        $teams = Team::with(['members', 'organization'])->get();
        $availableUsers = User::whereNull('team_id')->where('role', 'sales')->get();

        return view('teams.index', compact('teams', 'availableUsers', 'organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        Team::create([
            'name' => $request->name,
            'organization_id' => $request->organization_id,
        ]);

        return back()->with('success', 'Team created successfully.');
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

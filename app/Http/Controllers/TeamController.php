<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Team::class);
        $user = Auth::user();
        $teams = Team::with(['members'])
        ->where('organization_id', $user->organization_id)
            ->get();

        $availableUsers = User::where('organization_id', $user->organization_id)
            ->where('role', 'sales')
            ->whereNull('team_id')
            ->get();

        return view('teams.index', compact('teams', 'availableUsers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Team::class);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Team::create([
            'name' => $request->name,
            'organization_id' => Auth::user()->organization_id,
        ]);

        return back()->with('success', 'Team created successfully.');
    }

    public function destroy(Team $team)
    {
        Gate::authorize('delete', $team);
        $team->members()->update(['team_id' => null]);

        $team->delete();
        return back()->with('success', 'Team deleted.');
    }

    public function addMember(Request $request, Team $team)
    {
        Gate::authorize('update', $team);

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::where('id', $request->user_id)
            ->where('organization_id', Auth::user()->organization_id)
            ->firstOrFail();

        $user->team_id = $team->id;
        $user->save();

        return back()->with('success', 'Member added to team successfully.');
    }

    public function removeMember(User $user)
    {
        Gate::authorize('update', $user);

        $user->team_id = null;
        $user->save();

        return back()->with('success', 'Member removed from team.');
    }

    public function update(Request $request, Team $team)
    {
        Gate::authorize('update', $team);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($team->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }

        $team->update([
            'name' => $request->name
        ]);

        return back()->with('success', 'Team name updated successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Mail\TeamInvitationMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $request->validate([
            'email' => [
                'required', 
                'email', 
                'unique:users,email'
            ],
            'team_id' => [
                'nullable', 
                Rule::exists('teams', 'id')->where(function ($query) use ($currentUser) {
                    return $query->where('organization_id', $currentUser->organization_id);
                })
            ]
        ]);

        $token = Str::random(32);
        $invitation = Invitation::updateOrCreate(
            ['email' => $request->email],
            [
                'token' => $token,
                'organization_id' => $currentUser->organization_id,
                'team_id' => $request->team_id,
                'role' => 'sales',
                'expires_at' => Carbon::now()->addDays(7)
            ]
        );

        Mail::to($request->email)->send(new TeamInvitationMail($invitation, $currentUser->name));
        return back()->with('success', 'Invitation sent successfully to ' . $request->email);
    }

    public function accept(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $invitation = Invitation::where('token', $request->token)
                        ->where('expires_at', '>', now())
                        ->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role' => $invitation->role ?? 'sales',
            'organization_id' => $invitation->organization_id,
            'team_id' => $invitation->team_id,
            'is_active' => true,
        ]);

        $invitation->delete();
        Auth::login($user);
        return redirect()->route('dashboard.index')->with('success', 'Account created! Welcome to the team.');
    }
}
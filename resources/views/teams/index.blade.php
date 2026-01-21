@extends('layouts.app')

@section('content')

    <div class="space-y-8">

        @if(session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">Success!</span> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Team Management</h2>
                <p class="text-sm text-slate-500">Create teams and assign sales members.</p>
            </div>

            <form action="{{ route('teams.store') }}" method="POST" class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">
                @csrf
                <input type="text" name="name" placeholder="New Team Name..." required class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 text-sm w-full md:w-64 placeholder-slate-400">
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium whitespace-nowrap shadow-sm transition-all focus:ring-4 focus:ring-emerald-500/20 w-full md:w-auto">
                    + Create Team
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($teams as $team)
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col h-full hover:shadow-md transition-shadow duration-200 group/card">

                    <div class="p-4 border-b border-slate-100 bg-slate-50 rounded-t-xl"
                         x-data="{
                             isEditing: false,
                             focusInput() { $nextTick(() => { $refs.nameInput.focus(); }); }
                         }">

                        <div class="flex justify-between items-start" x-show="!isEditing">
                            <div>
                                <div class="flex items-center gap-2 group/title">
                                    <h3 class="font-bold text-lg text-slate-800">{{ $team->name }}</h3>

                                    <button @click="isEditing = true; focusInput()" type="button" class="text-slate-400 hover:text-emerald-600 transition-colors opacity-0 group-hover/card:opacity-100" title="Rename Team">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                </div>
                                <span class="text-xs text-slate-500 font-medium">{{ $team->members->count() }} Members</span>
                            </div>

                            <form action="{{ route('teams.destroy', $team->id) }}" method="POST" onsubmit="return confirm('Delete this team? All members will be unassigned.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 p-1 rounded-md hover:bg-red-50 transition-colors" title="Delete Team">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>

                        <div x-show="isEditing" x-cloak class="w-full">
                            <form action="{{ route('teams.update', $team->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf @method('PUT')
                                <input x-ref="nameInput"
                                       type="text"
                                       name="name"
                                       value="{{ $team->name }}"
                                       class="flex-1 px-2 py-1 text-sm border border-emerald-500 rounded focus:ring-2 focus:ring-emerald-200 outline-none"
                                       required
                                       @keydown.escape="isEditing = false">

                                <button type="submit" class="text-white bg-emerald-600 hover:bg-emerald-700 p-1.5 rounded shadow-sm" title="Save">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>

                                <button type="button" @click="isEditing = false" class="text-slate-500 hover:text-slate-700 p-1.5 hover:bg-slate-200 rounded" title="Cancel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-4 flex-1 space-y-3">
                        @if($team->members->isEmpty())
                            <div class="flex flex-col items-center justify-center h-full py-6 text-slate-400">
                                <svg class="w-10 h-10 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-sm">No members yet</p>
                            </div>
                        @else
                            <ul class="space-y-2">
                                @foreach($team->members as $member)
                                    <li class="flex items-center justify-between text-sm group p-2 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-sm">
                                                {{ substr($member->name, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-slate-700 font-medium">{{ $member->name }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $member->email }}</span>
                                            </div>
                                        </div>

                                        <form action="{{ route('teams.remove_member', $member->id) }}" method="POST" onsubmit="return confirm('Remove {{ $member->name }} from {{ $team->name }}?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all p-1.5 hover:bg-red-50 rounded-md" title="Remove from team">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-xl">
                        <form action="{{ route('teams.add_member', $team->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            <select name="user_id" required class="flex-1 text-xs md:text-sm border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 bg-white text-slate-700 cursor-pointer">
                                <option value="">+ Add Member...</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                                @if($availableUsers->isEmpty())
                                    <option value="" disabled>No available users</option>
                                @endif
                            </select>
                            <button type="submit" class="bg-white border border-slate-300 hover:bg-emerald-600 hover:border-emerald-600 hover:text-white text-slate-600 rounded-lg px-3 py-2 transition-all shadow-sm text-xs font-bold uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed" {{ $availableUsers->isEmpty() ? 'disabled' : '' }}>
                                Add
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="col-span-full text-center py-12 text-slate-500 bg-slate-50 rounded-xl border-2 border-dashed border-slate-300">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <p class="text-lg font-medium text-slate-600">You haven't created any teams yet.</p>
                    <p class="text-sm text-slate-400">Start by entering a team name above.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

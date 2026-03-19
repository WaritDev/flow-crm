@extends('layouts.app')

@section('content')

    <div class="space-y-6">

        @if(session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">Success!</span> {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                @if(auth()->user()->isAdmin() && request('organization_id'))
                    <a href="{{ route('organization-users.index') }}" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 shadow-sm transition-all" title="Back to Organization List">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"></path></svg>
                    </a>
                @endif

                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        @if(isset($selectedOrg))
                            Members of {{ $selectedOrg->name }}
                        @elseif(auth()->user()->role === 'manager')
                            My Sales Team
                        @else
                            Organization Members
                        @endif
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        @if(isset($selectedOrg))
                            Managing managers and sales reps for <strong>{{ $selectedOrg->name }}</strong>.
                        @else
                            Manage all Managers and Sales Representatives.
                        @endif
                    </p>
                </div>
            </div>

            <a href="{{ route('users.create', ['organization_id' => request('organization_id')]) }}" class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New User
            </a>
        </div>

        <div class="overflow-hidden bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold tracking-wider">
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Team</th>
                        <th class="px-6 py-4">Last Login</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 text-white rounded-full font-bold shadow-sm ring-2 ring-white
                                        {{ $user->role === 'manager' ? 'bg-purple-500' : ($user->role === 'admin' ? 'bg-slate-700' : 'bg-emerald-500') }}">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @php
                                    $badgeClass = match($user->role) {
                                        'admin' => 'bg-slate-100 text-slate-700 border-slate-300',
                                        'manager' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'sales' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border {{ $badgeClass }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                @if($user->team)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $user->team->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">No Team</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->diffForHumans() : 'Never' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('users.edit', [$user->id, 'organization_id' => request('organization_id')]) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete {{ $user->name }}?');">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="organization_id" value="{{ request('organization_id') }}">
                                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                No users found for this organization.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $users->appends(['organization_id' => request('organization_id')])->links() }}
            </div>
        </div>
    </div>
@endsection
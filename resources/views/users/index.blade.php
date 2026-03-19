@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    openDeleteModal: false, 
    deleteUrl: '', 
    userName: '',
    orgId: '{{ request('organization_id') }}'
}">

    @if(session('success'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 2000)" 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-8"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed bottom-5 right-5 z-[100] max-w-sm w-full bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 flex items-center gap-4 ring-1 ring-black/5"
        >
            <div class="flex-shrink-0 bg-emerald-500 p-2 rounded-xl shadow-lg shadow-emerald-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-slate-900">Success!</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            @if(auth()->user()->isAdmin() && request('organization_id'))
                <a href="{{ route('organization-users.index') }}" 
                    class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 shadow-sm transition-all" 
                    title="Back to Organization Selection">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"></path>
                    </svg>
                </a>
            @endif

            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    @if(isset($selectedOrg))
                        Members of {{ $selectedOrg->name }}
                    @elseif(auth()->user()->isManager())
                        My Sales Team
                    @else
                        Organization Members
                    @endif
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    @if(isset($selectedOrg))
                        Managing staff for <strong>{{ $selectedOrg->name }}</strong>.
                    @else
                        Manage all Managers and Sales Representatives.
                    @endif
                </p>
            </div>
        </div>

        <a href="{{ route('users.create', ['organization_id' => request('organization_id')]) }}" 
            class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white transition-all bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm active:scale-95">
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
                    <th class="px-6 py-4 text-center">Status</th>
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

                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.toggle-status', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $user->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] uppercase font-bold text-slate-300 tracking-tight">System Account</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('users.edit', [$user->id, 'organization_id' => request('organization_id')]) }}" 
                                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                @if(auth()->id() !== $user->id)
                                    <button type="button" 
                                        @click="
                                            deleteUrl = '{{ route('users.destroy', $user->id) }}'; 
                                            userName = '{{ $user->name }}'; 
                                            openDeleteModal = true;
                                        "
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            No members found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $users->appends(['organization_id' => request('organization_id')])->links() }}
            </div>
        @endif
    </div>

    <template x-teleport="body">
        <div x-show="openDeleteModal" class="fixed inset-0 z-[110] overflow-y-auto" x-cloak>
            <div x-show="openDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="openDeleteModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative flex min-h-screen items-center justify-center p-4">
                <div x-show="openDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="w-full max-w-md bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-xl mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="text-center">
                            <h3 class="text-lg font-bold text-slate-900">Confirm Deletion</h3>
                            <p class="mt-2 text-sm text-slate-500">
                                Are you sure you want to delete <span class="font-bold text-slate-800" x-text="userName"></span>?
                            </p>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse sm:flex-row justify-end gap-3">
                        <button @click="openDeleteModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                        <form :action="deleteUrl" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="organization_id" :value="orgId">
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-sm transition-all active:scale-95">Delete Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
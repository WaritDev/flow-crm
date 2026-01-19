<aside :class="sidebarCollapsed ? 'w-20' : 'w-72'"
       class="fixed left-0 top-0 z-50 h-screen bg-slate-900 border-r border-slate-800 text-slate-300 transition-all duration-300 ease-in-out shadow-xl">

    <div class="flex h-16 items-center px-4 border-b border-slate-800/50 bg-slate-900/50 backdrop-blur-sm"
         :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">

        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap" x-show="!sidebarCollapsed">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/20">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <span class="text-lg font-bold text-white tracking-wide transition-opacity duration-200">
                FlowCRM
            </span>
        </div>

        <button @click="sidebarCollapsed = true" x-show="!sidebarCollapsed" class="rounded-lg p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <button @click="sidebarCollapsed = false" x-show="sidebarCollapsed" class="flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all shadow-md">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </button>
    </div>

    <div class="flex h-[calc(100vh-4rem)] flex-col justify-between">
        <nav class="flex-1 space-y-2 p-3 overflow-y-auto custom-scrollbar">

            <a href="{{ route('teams.index') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 {{ request()->routeIs('teams.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-all duration-200">
                <svg class="h-6 w-6 shrink-0 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap transition-opacity duration-200">
                    Manage Teams
                </span>
            </a>

            <a href="{{ route('users.index') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-all duration-200">
                <svg class="h-6 w-6 shrink-0 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap transition-opacity duration-200">
                    Manage Users
                </span>
            </a>

        </nav>

        <div class="border-t border-slate-800 p-3 bg-slate-900">
            <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-xl p-2 hover:bg-slate-800 transition-colors relative">
                <div class="h-10 w-10 shrink-0 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold overflow-hidden border border-slate-600 group-hover:border-emerald-500 transition-colors">
                    <span>{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                </div>
                <div x-show="!sidebarCollapsed" class="flex-1 overflow-hidden transition-opacity duration-200">
                    <p class="truncate text-sm font-medium text-white group-hover:text-emerald-400 transition-colors">
                        {{ Auth::user()->name ?? 'Manager' }}
                    </p>
                    <p class="truncate text-xs text-slate-500">Edit Profile</p>
                </div>
                <button @click.prevent="sidebarCollapsed = !sidebarCollapsed" x-show="sidebarCollapsed" class="absolute inset-0 w-full h-full cursor-pointer"></button>
            </a>
        </div>
    </div>
</aside>

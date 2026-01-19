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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap transition-opacity duration-200">
                    Manage Teams
                </span>
            </a>

            <a href="{{ route('users.index') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-all duration-200">
                <svg class="h-6 w-6 shrink-0 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap transition-opacity duration-200">
                    Manage Users
                </span>
            </a>

            <a href="{{ route('customers.index') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 {{ request()->routeIs('customers') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-all duration-200">
                <svg class="h-6 w-6 shrink-0 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap transition-opacity duration-200">
                    Customers
                </span>
            </a>

            <a href="{{ route('pipelines.index') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 {{ request()->routeIs('pipelines.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-all duration-200">
                <svg class="h-6 w-6 shrink-0 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap transition-opacity duration-200">
                    Pipeline
                </span>
            </a>

            <a href="{{ route('pipeline-templates.index') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 {{ request()->routeIs('pipeline-templates.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-all duration-200">
                <svg class="h-6 w-6 shrink-0 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="font-medium whitespace-nowrap transition-opacity duration-200">
                    Pipeline Template
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

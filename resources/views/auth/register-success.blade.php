<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-6">

        <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden text-center p-8">

            <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>

            <h2 class="text-2xl font-bold text-slate-800 mb-2">Registration Complete!</h2>
            <p class="text-slate-500 mb-8">
                Your organization <span class="font-semibold text-slate-800">"{{ $organization->name }}"</span> has been created.
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 mb-8">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Your Team Invite Code</p>

                <div class="flex items-center justify-center gap-3">
                    <span class="text-3xl font-mono font-bold text-emerald-600 tracking-wider select-all" id="invite-code">
                        {{ $organization->invite_code }}
                    </span>

                    <button onclick="navigator.clipboard.writeText('{{ $organization->invite_code }}');"
                            class="text-slate-400 hover:text-emerald-600 transition" title="Copy Code">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                </div>

                <p class="text-xs text-slate-400 mt-3">
                    Share this code with your Sales team so they can join your workspace.
                </p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('teams.index') }}" class="block w-full py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-lg shadow-slate-900/10 transition">
                    Go to Team Management &rarr;
                </a>
            </div>

        </div>
    </div>
</x-guest-layout>

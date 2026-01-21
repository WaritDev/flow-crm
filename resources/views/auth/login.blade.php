<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-6">

        <a href="{{ url('/') }}" class="absolute top-6 left-6 text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-2 font-medium text-sm group">
            <div class="p-2 bg-white rounded-lg shadow-sm border border-slate-200 group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </div>
            <span class="hidden sm:inline">Back to Home</span>
        </a>

        <div class="mb-8 text-center">
            <div class="flex justify-center mb-3">
                <div class="h-12 w-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">FlowCRM</h1>
            <p class="text-slate-500 text-sm mt-2">Welcome back! Please enter your details.</p>
        </div>

        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden p-8">

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" class="block mt-1 w-full px-4 py-2 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm"
                                  type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="john@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <x-input-label for="password" value="Password" />
                        @if (Route::has('password.request'))
                            <a class="text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:underline" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <x-text-input id="password" class="block w-full px-4 py-2 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm"
                                  type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-lg shadow-slate-900/10 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex justify-center items-center gap-2">
                        Sign in <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-slate-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-bold text-slate-800 hover:text-emerald-600 transition">
                    Create account
                </a>
            </div>

        </div>
    </div>
</x-guest-layout>

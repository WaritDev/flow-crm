<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-slate-50">
        <div class="max-w-md text-center space-y-4">
            <h1 class="text-xl font-bold text-slate-800">Sales registration</h1>
            <p class="text-sm text-slate-600">
                Sales sign-up is only available in the <strong>FlowCRM SvelteKit app</strong>.
            </p>
            <p class="text-xs text-slate-500">
                This page exists to obtain a CSRF token. If you are a <strong>manager</strong>, use Register on this site instead.
            </p>
            @if(config('app.frontend_url'))
                <a href="{{ config('app.frontend_url') }}/register"
                   class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                    Open sales registration
                </a>
            @endif
        </div>
    </div>
</x-guest-layout>

<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Integrations Setup (n8n + LINE OA)</h1>
                <p class="text-slate-600 mt-2">
                    Connect n8n (Bearer token) and configure the Webhook URL for LINE OA (Messaging API).
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-8">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Organization</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $organization->name }}</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Invite Code</p>
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-mono font-bold text-emerald-700 select-all">{{ $organization->invite_code }}</span>
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $organization->invite_code }}');"
                            class="text-slate-500 hover:text-emerald-700 transition text-sm font-semibold"
                        >
                            Copy
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">n8n API Token (Bearer)</p>

                    @if(session('n8n_plain_text_token'))
                        <div class="mb-3 text-sm text-emerald-700 font-semibold">
                            New token created (shown once) — copy it into n8n Credentials
                        </div>
                        <div class="flex items-start gap-3">
                            <textarea readonly class="w-full min-h-[80px] rounded-xl border border-slate-200 bg-slate-50 p-3 font-mono text-xs">{{ session('n8n_plain_text_token') }}</textarea>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText(@json(session('n8n_plain_text_token')));"
                                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                                Copy
                            </button>
                        </div>
                    @elseif($plainTextToken)
                        <div class="mb-3 text-sm text-emerald-700 font-semibold">
                            n8n token (shown once) — copy it into n8n Credentials
                        </div>
                        <div class="flex items-start gap-3">
                            <textarea readonly class="w-full min-h-[80px] rounded-xl border border-slate-200 bg-slate-50 p-3 font-mono text-xs">{{ $plainTextToken }}</textarea>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText(@json($plainTextToken));"
                                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                                Copy
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-slate-600">
                            A token already exists (we do not store plain text). Use “Rotate Token” to issue a new one.
                        </p>
                    @endif

                    <div class="mt-4 flex items-center gap-3">
                        <form method="POST" action="{{ route('integrations.n8n.rotate-token') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600 transition">
                                Rotate Token
                            </button>
                        </form>
                        <p class="text-xs text-slate-500">
                            Rotate periodically and revoke immediately if you see anything suspicious.
                        </p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">LINE OA webhook URL (paste in LINE Developers)</p>
                    <p class="text-sm text-slate-600 mb-3">
                        This URL targets the n8n Webhook (managed n8n) using this organization’s path.
                    </p>
                    @if(!$n8nBaseUrl)
                        <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            <span class="font-mono font-semibold">N8N_URL</span> is not set in <span class="font-mono">.env</span>,
                            so the full webhook URL cannot be built.
                            <div class="mt-2 text-xs text-amber-800">
                                Example: <span class="font-mono">N8N_URL=https://xxxx.ngrok-free.app</span>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <textarea readonly class="w-full min-h-[70px] rounded-xl border border-slate-200 bg-slate-50 p-3 font-mono text-xs">{{ $webhookUrl ?: ('(Set N8N_URL first) Path: ' . ($integration->line_webhook_path ?? '')) }}</textarea>
                        <button type="button"
                                onclick="navigator.clipboard.writeText(@json($webhookUrl ?: ($integration->line_webhook_path ?? '')));"
                                class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                            Copy
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">
                        In n8n, create a workflow with a Webhook node whose path matches the end of this URL.
                    </p>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">LINE OA Channel Access Token (Optional)</p>
                    <p class="text-sm text-slate-600 mb-4">
                        Not required if FlowCRM does not send LINE messages (let n8n handle send/reply).
                        If you later want FlowCRM to send via Messaging API, save the token here.
                    </p>

                    <form method="POST" action="{{ route('integrations.n8n.line-token') }}" class="space-y-3">
                        @csrf
                        <textarea name="line_channel_access_token"
                                  class="w-full min-h-[90px] rounded-xl border border-slate-200 p-3 font-mono text-xs"
                                  placeholder="Paste Channel Access Token (long) or leave blank"></textarea>
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-xs text-slate-500">
                                Status: {{ $lineTokenPresent ? 'Configured' : 'Not set' }}
                            </p>
                            <button type="submit"
                                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                                Save
                            </button>
                        </div>
                    </form>
                </div>

                <div class="pt-2">
                    <a href="{{ route('teams.index') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 transition">
                        Manage teams &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


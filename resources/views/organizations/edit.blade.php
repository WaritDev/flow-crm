@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Edit Organization</h2>
                <p class="text-sm text-slate-500">Update details for {{ $organization->name }}.</p>
            </div>
            <a href="{{ route('organizations.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <form action="{{ route('organizations.update', $organization->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Organization Name *</label>
                    <input type="text" name="name" value="{{ old('name', $organization->name) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug', $organization->slug) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Size</label>
                    <select name="size" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">Select size</option>
                        <option value="small" {{ old('size', $organization->size) == 'small' ? 'selected' : '' }}>Small (1-10)</option>
                        <option value="medium" {{ old('size', $organization->size) == 'medium' ? 'selected' : '' }}>Medium (11-50)</option>
                        <option value="large" {{ old('size', $organization->size) == 'large' ? 'selected' : '' }}>Large (51-200)</option>
                        <option value="enterprise" {{ old('size', $organization->size) == 'enterprise' ? 'selected' : '' }}>Enterprise (200+)</option>
                    </select>
                    @error('size') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors" placeholder="Enter organization description">{{ old('description', $organization->description) }}</textarea>
                    @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invite Code</label>
                    <input type="text" name="invite_code" value="{{ old('invite_code', $organization->invite_code) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('invite_code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 font-medium shadow-sm transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Update Organization
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800">Integrations (Admin)</h3>
            <p class="text-sm text-slate-500 mt-1">
                Configure the n8n API token and LINE OA webhook for this organization.
            </p>

            @if(session('n8n_plain_text_token'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-semibold text-emerald-800">New n8n token (shown once)</p>
                    <div class="mt-2 flex items-start gap-3">
                        <textarea readonly class="w-full min-h-[80px] rounded-xl border border-emerald-200 bg-white p-3 font-mono text-xs">{{ session('n8n_plain_text_token') }}</textarea>
                        <button type="button"
                                onclick="navigator.clipboard.writeText(@json(session('n8n_plain_text_token')));"
                                class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                            Copy
                        </button>
                    </div>
                </div>
            @endif

            <form action="{{ route('organizations.update', $organization->id) }}" method="POST" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                <input type="hidden" name="name" value="{{ old('name', $organization->name) }}">
                <input type="hidden" name="slug" value="{{ old('slug', $organization->slug) }}">
                <input type="hidden" name="size" value="{{ old('size', $organization->size) }}">
                <input type="hidden" name="description" value="{{ old('description', $organization->description) }}">
                <input type="hidden" name="invite_code" value="{{ old('invite_code', $organization->invite_code) }}">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">n8n Token Name</label>
                    <input type="text" name="integration_n8n_token_name" value="{{ old('integration_n8n_token_name', $integration->n8n_token_name ?? '') }}"
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="n8n-default">
                    @error('integration_n8n_token_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">LINE OA Webhook Path</label>
                    <input type="text" name="integration_line_webhook_path" value="{{ old('integration_line_webhook_path', $integration->line_webhook_path ?? '') }}"
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                           placeholder="flowcrm-line-<org>-<secret>">
                    @error('integration_line_webhook_path') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    <p class="text-xs text-slate-500 mt-2">
                        @if($n8nBaseUrl)
                            Webhook URL: <span class="font-mono select-all">{{ $webhookUrl }}</span>
                        @else
                            Set <span class="font-mono">N8N_URL</span> in <span class="font-mono">.env</span> so the app can build the full webhook URL (https/ngrok supported).
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input id="integration_regenerate_line_webhook" type="checkbox" name="integration_regenerate_line_webhook" value="1"
                           class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                    <label for="integration_regenerate_line_webhook" class="text-sm text-slate-700">
                        Regenerate LINE webhook secret/path
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">LINE OA Channel Access Token (Optional)</label>
                    <textarea name="integration_line_channel_access_token" rows="3"
                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                              placeholder="Optional if n8n sends LINE messages on its own"></textarea>
                    @error('integration_line_channel_access_token') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    <p class="text-xs text-slate-500 mt-2">Status: {{ ($integration->line_channel_access_token_encrypted ?? null) ? 'Configured' : 'Not set' }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <input id="integration_rotate_n8n_token" type="checkbox" name="integration_rotate_n8n_token" value="1"
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="integration_rotate_n8n_token" class="text-sm text-slate-700">
                        Rotate n8n token (issue new, revoke old)
                    </label>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-lg hover:bg-slate-800 font-medium shadow-sm transition-all">
                        Save Integrations
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

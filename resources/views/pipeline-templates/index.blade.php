@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-10">

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Pipeline templates</h1>
                <p class="text-slate-500 mt-2 text-lg max-w-2xl">
                    Pick a system template or create your own, then <strong>assign it to a team</strong> — teams with existing deals cannot switch templates.
                </p>
            </div>
            <a href="{{ route('pipeline-templates.create') }}"
               class="inline-flex justify-center items-center gap-2 rounded-xl bg-slate-900 text-white px-5 py-2.5 text-sm font-bold hover:bg-slate-800 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New template
            </a>
        </div>

        <section aria-labelledby="teams-heading">
            <h2 id="teams-heading" class="text-lg font-bold text-slate-800 mb-4">Team template assignment</h2>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="bg-slate-50 text-left text-slate-600 border-b border-slate-200">
                        <th class="px-4 py-3 font-semibold">Team</th>
                        <th class="px-4 py-3 font-semibold">Current template</th>
                        <th class="px-4 py-3 font-semibold">Deals</th>
                        <th class="px-4 py-3 font-semibold">Change template</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($teams as $team)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $team->name }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $team->pipelineTemplate?->name ?? '— Not set —' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $team->deals_count }}</td>
                            <td class="px-4 py-3">
                                @if($team->mayChangePipelineTemplate())
                                    <form action="{{ route('pipeline-templates.select') }}" method="POST" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                                        <select name="template_id" required
                                                class="rounded-lg border border-slate-300 text-sm py-1.5 px-2 min-w-[200px] focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">— Select template —</option>
                                            @foreach($templates as $tpl)
                                                <option value="{{ $tpl->id }}" @selected($team->template_id == $tpl->id)>
                                                    {{ $tpl->name }}@if($tpl->organization_id === null) (system)@else (ours)@endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-lg bg-emerald-600 text-white text-xs font-bold px-3 py-2 hover:bg-emerald-700">
                                            Save
                                        </button>
                                    </form>
                                @else
                                    <span class="text-amber-700 text-xs font-medium inline-flex items-center gap-1">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6V9a4 4 0 10-8 0v4h8z"/></svg>
                                        Has deals — template is locked
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No teams yet — create one under Team management first.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="catalog-heading">
            <h2 id="catalog-heading" class="text-lg font-bold text-slate-800 mb-4">Available templates</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $template)
                    <x-pipeline-template.db-card :template="$template" />
                @endforeach
            </div>
        </section>
    </div>
@endsection

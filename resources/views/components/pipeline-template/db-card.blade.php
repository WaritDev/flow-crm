@props(['template'])

@php
    $ind = strtolower((string) ($template->industry ?? ''));
    $color = 'blue';
    if (str_contains($ind, 'beauty') || str_contains($ind, 'clinic')) $color = 'pink';
    elseif (str_contains($ind, 'construction')) $color = 'orange';
    elseif (str_contains($ind, 'pre')) $color = 'purple';
    elseif (str_contains($ind, 'b2b')) $color = 'indigo';
    elseif (str_contains($ind, 'health')) $color = 'rose';

    $colors = [
        'pink' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-600', 'border' => 'border-pink-200'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'border' => 'border-orange-200'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'border' => 'border-purple-200'],
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200'],
        'rose' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'border' => 'border-rose-200'],
    ];
    $theme = $colors[$color] ?? $colors['blue'];
    $isSystem = $template->organization_id === null;
@endphp

<div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col h-full hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between gap-2 mb-3">
        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $theme['bg'] }} {{ $theme['text'] }}">
            {{ $isSystem ? 'System' : 'Organization' }}
        </span>
        @if($template->is_default)
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Default</span>
        @endif
    </div>

    <h3 class="text-lg font-bold text-slate-900">{{ $template->name }}</h3>
    @if($template->industry)
        <p class="text-sm text-slate-500 mt-0.5">{{ $template->industry }}</p>
    @endif

    @if($template->description)
        <p class="text-slate-600 text-sm mt-3 flex-grow leading-relaxed">{{ Str::limit($template->description, 160) }}</p>
    @else
        <p class="text-slate-400 text-sm mt-3 flex-grow italic">No description</p>
    @endif

    <div class="border-t border-slate-100 my-4"></div>

    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Stages</p>
    <div class="flex flex-wrap gap-1.5 mb-4">
        @foreach($template->stages as $stage)
            <span class="text-[11px] px-2 py-0.5 rounded border {{ $stage->is_won ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-600' }}">
                {{ $stage->name }}
            </span>
        @endforeach
    </div>

    <div class="mt-auto flex flex-col gap-2">
        <a href="{{ route('pipeline-templates.show', $template) }}" class="text-center text-sm text-slate-600 hover:text-emerald-600 font-medium py-2">View details</a>
        @if(!$isSystem)
            <a href="{{ route('pipeline-templates.edit', $template) }}" class="text-center text-sm text-emerald-700 hover:underline font-medium py-1">Edit template</a>
        @endif
    </div>
</div>

@props(['title', 'value', 'icon', 'color' => 'slate', 'trend' => null])

@php
    $colors = [
        'rose' => 'bg-rose-50 text-rose-500',
        'amber' => 'bg-amber-50 text-amber-500',
        'emerald' => 'bg-emerald-50 text-emerald-500',
        'slate' => 'bg-slate-100 text-slate-500',
    ];
    $theme = $colors[$color] ?? $colors['slate'];
@endphp

<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-start justify-between h-full">
    <div>
        <p class="text-sm text-slate-500 font-medium mb-1">{{ $title }}</p>
        <h3 class="text-3xl font-bold text-slate-800">{{ $value }}</h3>
        @if($trend)
            <p class="text-xs font-bold text-emerald-500 mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                {{ $trend }} จากเดือนที่แล้ว
            </p>
        @endif
    </div>
    <div class="w-12 h-12 rounded-xl {{ $theme }} flex items-center justify-center">
        {{ $slot }}
    </div>
</div>

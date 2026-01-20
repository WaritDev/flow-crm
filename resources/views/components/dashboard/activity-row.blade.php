@props(['activity'])

@php
    $borderColors = [
        'urgent' => 'border-l-rose-500',
        'medium' => 'border-l-amber-400',
        'normal' => 'border-l-emerald-400',
    ];

    $badges = [
        'urgent' => 'bg-rose-50 text-rose-500',
        'medium' => 'bg-amber-50 text-amber-600',
        'normal' => 'bg-emerald-50 text-emerald-600',
    ];

    $labels = ['urgent' => 'ด่วน', 'medium' => 'ปานกลาง', 'normal' => 'ปกติ'];
@endphp

<div class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border-b border-slate-50 hover:bg-slate-50/50 transition-colors border-l-4 {{ $borderColors[$activity['priority']] }} last:border-b-0">
    <div class="flex items-start gap-4 mb-3 sm:mb-0">
        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            @if(str_contains($activity['action_type'], 'LINE'))
                <i class="fab fa-line text-lg"></i>
            @else
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
            @endif
        </div>
        <div>
            <h4 class="font-bold text-slate-800 text-sm">
                <span class="text-emerald-600">[{{ $activity['action_type'] }}]</span>
                {{ $activity['customer_name'] }}
            </h4>
            <p class="text-sm text-slate-500">{{ $activity['description'] }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3 self-end sm:self-center">
        <span class="text-xs px-2 py-0.5 rounded {{ $badges[$activity['priority']] }} font-bold">
            {{ $labels[$activity['priority']] }}
        </span>
        <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ $activity['time'] }}
        </span>

        <div class="hidden group-hover:flex items-center gap-2 ml-2 transition-all">
            <button class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-white flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                Copy
            </button>
            <button class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold hover:bg-slate-700 flex items-center gap-1 shadow-sm">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                LINE
            </button>
        </div>
    </div>
</div>

@props(['activity'])

<div
    @click="selectedId = {{ $activity['id'] }}"
    class="p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 relative group hover:shadow-md bg-white"
    :class="selectedId === {{ $activity['id'] }} ? 'border-emerald-500 ring-1 ring-emerald-500/20 shadow-emerald-500/10' : 'border-transparent shadow-sm hover:border-slate-200'"
>
    <div class="flex items-start justify-between mb-1">
        <div class="flex items-center gap-2">
            <x-activity.priority-badge :priority="$activity['priority']" />
            <h3 class="font-bold text-slate-800">
                <span class="text-emerald-600">[{{ $activity['action_type'] }}]</span>
                {{ $activity['customer_nickname'] }}
            </h3>
        </div>
        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </div>

    <p class="text-sm text-slate-600 mb-2 line-clamp-1">{{ $activity['title'] }}</p>

    @if($activity['warning'])
        <div class="flex items-center gap-1.5 text-xs text-amber-500 font-medium mb-3">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            {{ $activity['warning'] }}
        </div>
    @else
        <div class="mb-3 h-4"></div> @endif

    <div class="flex justify-between items-end border-t border-slate-50 pt-2">
        <div class="flex items-center gap-1 text-xs text-slate-400">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ $activity['time'] }}
        </div>
        <div class="font-bold text-slate-800 text-sm">
            ฿{{ number_format($activity['amount']) }}
        </div>
    </div>
</div>

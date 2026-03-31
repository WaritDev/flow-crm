@props(['deal'])

<div draggable="true"
     @dragstart="startDrag($event, {{ json_encode($deal) }})"
     class="p-4 rounded-xl border border-gray-200 shadow-sm bg-white cursor-move hover:shadow-md hover:border-emerald-300 transition-all relative group"
>
    <div class="mb-3">
        <h3 class="font-bold text-slate-800 text-lg">{{ $deal->customer->name ?? 'Unknown Customer' }}</h3>
        @if($deal->customer && $deal->customer->organization)
            <p class="text-sm text-slate-500">({{ $deal->customer->organization->name }})</p>
        @endif
        <p class="text-emerald-500 font-bold mt-1">THB {{ number_format($deal->value) }}</p>
    </div>

    @php
        $nextActionLabel = trim((string) ($deal->next_action ?? ''));
        $hasPlannedNextAction = $nextActionLabel !== '' || (bool) $deal->next_action_date;
    @endphp
    @if($deal->isStale() && !$hasPlannedNextAction)
        <div class="bg-red-50 p-3 rounded-lg border border-red-100 mb-3">
            <div class="flex items-center gap-2 text-red-500 font-bold text-xs mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                No next action
            </div>
            <p class="text-slate-800 font-medium text-sm">Add a next step</p>
        </div>
    @elseif($deal->isStale() && $hasPlannedNextAction)
        <div class="bg-amber-50 p-3 rounded-lg border border-amber-200 mb-3">
            <div class="flex items-center gap-2 text-amber-800 font-bold text-xs mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Next action overdue
            </div>
            <p class="text-xs text-amber-900/70 mb-1">Next step:</p>
            <p class="text-slate-800 font-medium text-sm">{{ $nextActionLabel !== '' ? $nextActionLabel : 'Pending' }}</p>
            <div class="flex items-center gap-1 mt-2 text-xs text-amber-900/70">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Due: {{ $deal->next_action_date ? $deal->next_action_date->format('d M Y') : '-' }}
            </div>
        </div>
    @else
        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 mb-3">
            <p class="text-xs text-slate-400 mb-1">Next Step:</p>
            <p class="text-slate-800 font-medium text-sm">{{ $deal->next_action ?? 'Pending' }}</p>
            <div class="flex items-center gap-1 mt-2 text-xs text-slate-400">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Next action due: {{ $deal->next_action_date ? $deal->next_action_date->format('d M Y') : '-' }}
            </div>
            @if($deal->expected_close_date)
                <p class="text-xs text-slate-400 mt-2">Expected close: {{ $deal->expected_close_date->format('d M Y') }}</p>
            @endif
        </div>
    @endif

    <div class="flex justify-between items-end">
        <span class="text-xs text-slate-400">{{ $deal->updated_at->diffInDays(now()) }} days in this stage</span>

        <a href="#"
           class="flex items-center gap-1 text-emerald-500 hover:text-emerald-600 font-medium text-sm px-2 py-1 rounded hover:bg-emerald-50 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12 .5C5.4.5 0 4.8 0 10.2c0 2.9 1.6 5.5 4.3 7.3-.2.8-.7 2.3-.9 2.8-.1.4 0 .6.4.4.2 0 2.1-1.3 2.9-1.9 1.7.5 3.5.7 5.3.7 6.6 0 12-4.3 12-9.7S16.6.5 12 .5z" />
            </svg>
            LINE
        </a>
    </div>
</div>

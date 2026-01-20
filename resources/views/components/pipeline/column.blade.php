@props(['stage', 'deals'])

@php
    $stageDeals = $deals->where('stage_id', $stage->id);
    $totalAmount = $stageDeals->sum('value'); // Deal model has 'value', not 'amount'
    $count = $stageDeals->count();

    // Color Coding for Header Dots
    $colors = [
        'Prospect' => 'bg-gray-400',
        'Contacted' => 'bg-emerald-300',
        'Quoted' => 'bg-yellow-300',
        'Negotiation' => 'bg-slate-400',
        'Won' => 'bg-green-500',
        'Lost' => 'bg-red-500',
    ];
    $dotColor = $colors[$stage->name] ?? 'bg-slate-300';
@endphp

<div class="flex-shrink-0 w-80 flex flex-col h-full rounded-xl bg-slate-50/50 border border-slate-200/60"
    @dragover.prevent="dragOver($event, {{ $stage->position }})"
    @drop="drop($event, '{{ $stage->id }}', {{ $stage->position }})"
    :class="{ 'bg-red-50/50 cursor-not-allowed opacity-60': isInvalidDrop({{ $stage->position }}) }">
    <div
        class="p-4 flex items-center justify-between border-b border-slate-100 bg-white/50 rounded-t-xl backdrop-blur-sm sticky top-0 z-10">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full {{ $dotColor }}"></div>
            <h3 class="font-bold text-slate-700">{{ $stage->name }}</h3>
            <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full text-xs font-semibold">{{ $count }}</span>
        </div>
        <div class="text-sm font-semibold text-slate-400">
            ฿{{ number_format($totalAmount) > 0 ? number_format($totalAmount / 1000) . 'k' : '0' }}
        </div>
    </div>

    <div class="p-3 space-y-3 flex-1 overflow-y-auto min-h-[500px]">
        @foreach($stageDeals as $deal)
            <x-pipeline.card :deal="$deal" />
        @endforeach

        <button
            class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-slate-400 hover:border-emerald-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all flex items-center justify-center gap-2 group">
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            เพิ่มดีล
        </button>
    </div>
</div>
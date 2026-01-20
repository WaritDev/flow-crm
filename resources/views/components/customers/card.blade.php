@props(['active' => false, 'name', 'nickname', 'handle', 'amount', 'status'])

<div @class([
    'p-4 rounded-xl border transition-all cursor-pointer flex justify-between items-center group hover:shadow-md',
    'border-emerald-500 bg-emerald-50/10 ring-1 ring-emerald-500/20' => $active,
    'border-gray-200 bg-white hover:border-gray-300' => !$active
])>
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold
            {{ $active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
            {{ mb_substr($nickname, 0, 2) }}
        </div>

        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-slate-800">{{ $nickname }}</h3>
                <x-ui.badge :status="$status" />
            </div>
            <p class="text-sm text-slate-500 mt-0.5">{{ $name }}</p>

            @if(!empty($tags))
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach(array_slice($tags, 0, 2) as $tag) {{-- โชว์แค่ 2 อันแรกป้องกันล้น --}}
                    <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">#{{ $tag }}</span>
                    @endforeach
                    @if(count($tags) > 2)
                        <span class="text-[10px] text-slate-400">+{{ count($tags) - 2 }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="flex flex-col justify-between items-end h-full self-stretch py-1">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-emerald-600 font-semibold text-sm">{{ $amount }}</span>
    </div>
</div>

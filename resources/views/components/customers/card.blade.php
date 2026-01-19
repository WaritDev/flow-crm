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
            <p class="text-sm text-slate-400 mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                {{ $handle }}
            </p>
        </div>
    </div>

    <div class="flex flex-col justify-between items-end h-full self-stretch py-1">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-emerald-600 font-semibold text-sm">{{ $amount }}</span>
    </div>
</div>

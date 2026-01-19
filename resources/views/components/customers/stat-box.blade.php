@props(['label', 'value', 'subtext' => null, 'isMoney' => false])

<div class="border border-gray-200 rounded-xl p-6 text-center bg-white flex flex-col items-center justify-center h-full">
    <div @class(['text-3xl font-bold mb-1', 'text-emerald-500' => $isMoney, 'text-slate-800' => !$isMoney])>
        {{ $value }}
    </div>
    <div class="text-sm text-slate-500">{{ $label }}</div>
    @if($subtext)
        <div class="text-xs text-slate-400 mt-1">{{ $subtext }}</div>
    @endif
</div>

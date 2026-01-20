@props(['label', 'value', 'icon'])

<div class="bg-slate-50 rounded-xl p-4 flex items-start gap-3">
    <div class="text-slate-400 mt-0.5">
        {{ $icon }} </div>
    <div>
        <p class="text-xs text-slate-500 font-medium mb-0.5">{{ $label }}</p>
        <p class="text-slate-800 font-medium">{{ $value }}</p>
    </div>
</div>

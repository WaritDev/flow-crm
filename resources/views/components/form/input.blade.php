@props(['label', 'name', 'type' => 'text', 'placeholder' => '', 'required' => false, 'helper' => null])

<div class="flex flex-col gap-1.5 w-full">
    <label for="{{ $name }}" class="text-sm font-semibold text-slate-700">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors text-slate-800 placeholder-gray-400"
    >
    @if($helper)
        <span class="text-xs text-slate-400">{{ $helper }}</span>
    @endif
</div>

@props(['label', 'name', 'checked' => true])

<div class="flex flex-col gap-1.5">
    <label class="text-sm font-semibold text-slate-700">{{ $label }}</label>
    <label class="relative inline-flex items-center cursor-pointer mt-1">
        <input type="checkbox" name="{{ $name }}" class="sr-only peer" {{ $checked ? 'checked' : '' }}>
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
        <span class="ml-3 text-sm font-medium text-slate-600 peer-checked:text-emerald-600">
            Active
        </span>
    </label>
</div>

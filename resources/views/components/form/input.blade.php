@props([
    'label',
    'name',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'value' => '',
    'helper' => null
])

<div class="flex flex-col gap-1.5 w-full">
    <label for="{{ $name }}" class="text-sm font-semibold text-slate-700">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}" {{-- ลำดับความสำคัญ: ข้อมูลใหม่ที่พิมพ์ผิด > ข้อมูลจาก DB > ค่าว่าง --}}
        placeholder="{{ $placeholder }}"
        @class([
            'w-full px-4 py-2.5 rounded-lg border transition-all text-slate-800 placeholder-gray-400 focus:outline-none focus:ring-2',
            'border-red-300 focus:ring-red-200 focus:border-red-500 bg-red-50' => $errors->has($name),
            'border-gray-300 focus:border-emerald-500 focus:ring-emerald-200 bg-white' => !$errors->has($name)
        ])
    >

    @error($name)
    <span class="text-xs text-red-500 font-medium">{{ $message }}</span>
    @enderror

    @if($helper && !$errors->has($name))
        <span class="text-xs text-slate-400">{{ $helper }}</span>
    @endif
</div>

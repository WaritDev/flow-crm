@props(['status' => 'active'])

@php
    $styles = [
        'active' => 'bg-emerald-100 text-emerald-700',
        'inactive' => 'bg-gray-100 text-gray-600',
        'pending' => 'bg-yellow-100 text-yellow-700',
    ];
    $label = ucfirst($status);
    $style = $styles[strtolower($status)] ?? $styles['inactive'];
@endphp

<span class="{{ $style }} px-2.5 py-0.5 rounded-full text-xs font-semibold">
    {{ $label }}
</span>

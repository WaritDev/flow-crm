@props(['priority'])

@php
    $styles = [
        'urgent' => 'bg-red-50 text-red-500 ring-red-500/20',
        'medium' => 'bg-orange-50 text-orange-500 ring-orange-500/20',
        'normal' => 'bg-emerald-50 text-emerald-500 ring-emerald-500/20',
    ];

    $labels = [
        'urgent' => 'ด่วน',
        'medium' => 'ปานกลาง',
        'normal' => 'ปกติ',
    ];

    $style = $styles[$priority] ?? $styles['normal'];
    $label = $labels[$priority] ?? 'ปกติ';
@endphp

<span class="{{ $style }} ring-1 px-2.5 py-0.5 rounded-full text-xs font-bold inline-block text-center min-w-[50px]">
    {{ $label }}
</span>

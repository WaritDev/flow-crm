@props(['template'])

@php
    // Map สีตามที่ส่งมา (Tailwind class map)
    $colors = [
        'pink' =>   ['bg' => 'bg-pink-100',   'text' => 'text-pink-500', 'hover' => 'group-hover:bg-pink-200'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-500', 'hover' => 'group-hover:bg-orange-200'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-500', 'hover' => 'group-hover:bg-purple-200'],
        'blue' =>   ['bg' => 'bg-blue-100',   'text' => 'text-blue-500', 'hover' => 'group-hover:bg-blue-200'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-500', 'hover' => 'group-hover:bg-indigo-200'],
        'rose' =>   ['bg' => 'bg-rose-100',   'text' => 'text-rose-500', 'hover' => 'group-hover:bg-rose-200'],
    ];

    $theme = $colors[$template['color']] ?? $colors['blue'];
@endphp

<div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col h-full hover:shadow-lg hover:border-gray-300 transition-all duration-200 group relative">

    @if($template['is_popular'])
        <span class="absolute top-4 right-4 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full">
            ยอดนิยม
        </span>
    @endif

    <div class="mb-4">
        <div class="w-12 h-12 rounded-xl {{ $theme['bg'] }} {{ $theme['text'] }} {{ $theme['hover'] }} flex items-center justify-center mb-4 transition-colors">
            @if($template['icon'] == 'sparkles')
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 3.214L13 21l-2.286-6.857L5 12l5.714-3.214L13 3z" /></svg>
            @elseif($template['icon'] == 'wrench')
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
            @elseif($template['icon'] == 'shopping-bag')
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            @elseif($template['icon'] == 'office-building')
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            @elseif($template['icon'] == 'briefcase')
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            @elseif($template['icon'] == 'heart')
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            @endif
        </div>

        <h3 class="text-lg font-bold text-slate-800">{{ $template['title_th'] }}</h3>
        <p class="text-sm text-slate-400 font-medium">{{ $template['title_en'] }}</p>
    </div>

    <p class="text-slate-500 text-sm leading-relaxed mb-6 flex-grow">
        {{ $template['description'] }}
    </p>

    <div class="border-t border-gray-100 my-4"></div>

    <div class="mb-4">
        <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Pipeline Stages:</p>
        <div class="flex flex-wrap gap-2">
            @foreach($template['stages'] as $stage)
                <span class="bg-slate-50 border border-slate-200 text-slate-600 text-[11px] px-2 py-1 rounded font-medium">
                    {{ $stage }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="mb-6">
        <p class="text-xs text-slate-400">
            รวม {{ $template['script_count'] }} Script ภาษาไทย
        </p>
    </div>

    <form action="{{ route('pipeline-templates.select') }}" method="POST" class="mt-auto">
        @csrf
        <input type="hidden" name="template_id" value="{{ $template['id'] }}">
        <button type="submit" class="w-full py-2.5 px-4 rounded-lg border border-gray-200 text-slate-700 font-semibold text-sm hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all flex items-center justify-center gap-2">
            เลือก Template นี้
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>
    </form>
</div>

@props(['title', 'description' => null])

<div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
    <div class="mb-5 border-b border-gray-100 pb-4">
        <h3 class="text-lg font-bold text-slate-800">{{ $title }}</h3>
        @if($description)
            <p class="text-sm text-slate-400 mt-1">{{ $description }}</p>
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
        {{ $slot }}
    </div>
</div>

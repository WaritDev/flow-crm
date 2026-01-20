@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $deal->name }}</h1>
                    @php
                        $daysInStage = now()->diffInDays($deal->updated_at);
                        $healthColor = $daysInStage > 7 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700';
                        $healthText = $daysInStage > 7 ? 'Stagnant (นิ่งนานเกิน)' : 'Healthy (สดใหม่)';
                    @endphp
                    <span class="{{ $healthColor }} px-2.5 py-0.5 rounded-full text-xs font-bold">
                        {{ $healthText }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">
                    ลูกค้า: <strong>{{ $deal->customer->name }} ({{ $deal->customer->nickname }})</strong> • สร้างเมื่อ
                    {{ $deal->created_at->format('d M Y') }}
                </p>
            </div>

            <div class="flex gap-3">
                <button type="submit" form="editDealForm"
                    class="px-6 py-2.5 bg-slate-900 text-white rounded-lg font-medium shadow-lg hover:bg-slate-800 transition-all">
                    บันทึกการเปลี่ยนแปลง
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <div class="lg:col-span-2">
                <form id="editDealForm" action="{{ route('deals.update', $deal->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('deals.partials.form-fields', ['customers' => $customers, 'deal' => $deal, 'stages' => $stages])
                </form>
            </div>

            <div class="space-y-6">

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <span class="text-emerald-500 text-xl"><i class="fab fa-line"></i></span>
                            LINE Script
                        </h3>
                        <span class="text-xs text-slate-400">Stage:
                            {{ $deal->lost_at ? 'Lost' : ($deal->stage->name ?? 'Unknown') }}</span>
                    </div>
                    <div
                        class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-sm text-slate-600 italic mb-3 relative group">
                        "สวัสดีครับ คุณ{{ $deal->customer->nickname }} ผมส่งใบเสนอราคาให้พิจารณา..."
                    </div>
                    <button onclick="navigator.clipboard.writeText('ข้อความสคริปต์...')"
                        class="w-full py-2 border border-emerald-200 text-emerald-600 rounded-lg hover:bg-emerald-50 text-sm font-bold transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        Copy Script
                    </button>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4">Timeline กิจกรรม</h3>
                    <div class="relative border-l-2 border-slate-200 ml-3 space-y-6">
                        @foreach($activities as $activity)
                            <div class="relative pl-6">
                                <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-2 border-white
                                    {{ $activity->is_completed ? 'bg-slate-300' : 'bg-emerald-500' }}"></div>

                                <p class="text-sm font-bold text-slate-800">{{ $activity->title }}</p>
                                <p class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }} โดย
                                    {{ $activity->user->name }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
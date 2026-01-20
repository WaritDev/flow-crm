@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manager Dashboard</h1>
                <p class="text-slate-500">ภาพรวมประสิทธิภาพทีมขายประจำเดือนนี้</p>
            </div>

            <div class="flex bg-slate-100 p-1 rounded-lg">
                <a href="{{ route('dashboard.index', ['view' => 'sales']) }}" class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700">Sales View</a>
                <span class="px-4 py-2 text-sm font-medium bg-white text-slate-800 shadow-sm rounded-md">Manager View</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-900 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <p class="text-slate-400 text-sm mb-1">Total Revenue</p>
                <h3 class="text-3xl font-bold">฿{{ number_format($stats['total_revenue']/1000000, 2) }}M</h3>
                <p class="text-emerald-400 text-xs mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                    +15% จากเดือนก่อน
                </p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-sm mb-1">Target Achievement</p>
                <div class="flex items-end gap-2">
                    <h3 class="text-3xl font-bold text-slate-800">{{ $stats['target_achievement'] }}%</h3>
                    <span class="text-slate-400 text-sm mb-1">ของเป้าหมาย</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $stats['target_achievement'] }}%"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-sm mb-1">Active Deals (ทั้งทีม)</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $stats['active_deals'] }}</h3>
                <p class="text-xs text-slate-400 mt-2">มูลค่ารอปิด ฿3.5M</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-sm mb-1">Win Rate</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $stats['avg_conversion'] }}%</h3>
                <p class="text-xs text-emerald-500 mt-2">↑ ดีขึ้น 2%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-bold text-slate-800">Team Performance</h2>
                    <select class="text-sm border-gray-200 rounded-lg text-slate-600">
                        <option>เดือนนี้</option>
                        <option>ไตรมาสนี้</option>
                    </select>
                </div>
                <div class="relative h-80 w-full">
                    <canvas id="teamChart"></canvas>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="text-xl">🏆</span> Top Sales
                    </h2>
                    <div class="space-y-4">
                        @foreach($topPerformers as $index => $sale)
                            <div class="flex items-center justify-between p-3 rounded-xl {{ $index == 0 ? 'bg-amber-50 border border-amber-100' : 'bg-slate-50' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold {{ $index == 0 ? 'bg-amber-100 text-amber-600' : 'bg-white text-slate-600' }}">
                                        {{ $sale['avatar'] }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm">{{ $sale['name'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $index + 1 }}st Place</p>
                                    </div>
                                </div>
                                <span class="font-bold {{ $index == 0 ? 'text-amber-600' : 'text-slate-700' }}">
                            ฿{{ number_format($sale['amount']/1000) }}K
                        </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="font-bold text-slate-800 mb-4">Pipeline Health</h2>
                    <div class="space-y-3">
                        @foreach($pipelineSummary as $stage)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-600 font-medium">{{ $stage['stage'] }}</span>
                                    <span class="text-slate-400">{{ $stage['count'] }} ดีล (฿{{ number_format($stage['value']/1000) }}k)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($stage['value'] / 3000000) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const ctx = document.getElementById('teamChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($teamPerformance['labels']),
                datasets: [
                    {
                        label: 'ยอดขายจริง',
                        data: @json($teamPerformance['data']),
                        backgroundColor: '#10b981', // Emerald
                        borderRadius: 6,
                        barPercentage: 0.6
                    },
                    {
                        label: 'เป้าหมาย',
                        data: @json($teamPerformance['targets']),
                        backgroundColor: '#e2e8f0', // Slate-200
                        borderRadius: 6,
                        barPercentage: 0.6,
                        hidden: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ฿' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 2], color: '#f1f5f9' },
                        ticks: { callback: value => '฿' + (value/1000) + 'k' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
@endsection

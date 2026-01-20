@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">

        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                Sales Dashboard <span class="text-3xl">👋</span>
            </h1>
            <p class="text-slate-500 mt-1">นี่คือภาพรวมกิจกรรมของคุณวันนี้</p>
            <div class="flex bg-slate-100 p-1 rounded-lg">
                <span class="px-4 py-2 text-sm font-medium bg-white text-slate-800 shadow-sm rounded-md">Sales View</span>
                <a href="{{ route('dashboard.index', ['view' => 'manager']) }}" class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700">Manager View</a>
            </div>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-dashboard.stat-card title="ต้องทำวันนี้" value="{{ $stats['todo_today'] }}" color="rose">
                <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </x-dashboard.stat-card>

            <x-dashboard.stat-card title="ดีลค้างเกิน 3 วัน" value="{{ $stats['overdue_deals'] }}" color="amber">
                <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </x-dashboard.stat-card>

            <x-dashboard.stat-card title="ลูกค้ายืนยันใบเสนอราคา" value="{{ $stats['confirmed_quotes'] }}" color="emerald">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </x-dashboard.stat-card>

            <x-dashboard.stat-card title="Revenue เดือนนี้" value="฿{{ number_format($stats['revenue_month']/1000) }}K" color="slate" trend="{{ $stats['revenue_growth'] }}%">
                <svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
            </x-dashboard.stat-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-800">กิจกรรมที่ต้องทำวันนี้</h2>
                        <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded font-bold">{{ count($activities) }} รายการ</span>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @foreach($activities as $activity)
                            <x-dashboard.activity-row :activity="$activity" />
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-bold text-slate-800">Revenue Snapshot</h2>
                            <p class="text-xs text-slate-400">ยอดขายจริง vs คาดการณ์</p>
                        </div>
                        <div class="flex gap-3 text-xs">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> ยอดจริง</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-300"></span> คาดการณ์</span>
                        </div>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

            </div>

            <div class="space-y-6">

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">เป้าหมายวันนี้</h3>
                            <p class="text-xs text-slate-500">ทำไปแล้ว 8 จาก 18 กิจกรรม</p>
                        </div>
                    </div>

                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-bold text-slate-700">44% สำเร็จ</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 mb-6">
                        <div class="bg-emerald-500 h-2.5 rounded-full" style="width: 44%"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <h4 class="text-lg font-bold text-slate-800">5<span class="text-xs text-slate-400 font-normal">/10</span></h4>
                            <p class="text-[10px] text-slate-500">ทัก LINE</p>
                            <div class="w-full bg-slate-200 h-1 mt-2 rounded-full"><div class="bg-emerald-500 h-1 rounded-full" style="width: 50%"></div></div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <h4 class="text-lg font-bold text-slate-800">2<span class="text-xs text-slate-400 font-normal">/5</span></h4>
                            <p class="text-[10px] text-slate-500">โทร</p>
                            <div class="w-full bg-slate-200 h-1 mt-2 rounded-full"><div class="bg-emerald-500 h-1 rounded-full" style="width: 40%"></div></div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <h4 class="text-lg font-bold text-slate-800">1<span class="text-xs text-slate-400 font-normal">/3</span></h4>
                            <p class="text-[10px] text-slate-500">ปิดดีล</p>
                            <div class="w-full bg-slate-200 h-1 mt-2 rounded-full"><div class="bg-emerald-500 h-1 rounded-full" style="width: 33%"></div></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800">156</h3>
                        <p class="text-xs text-slate-500">ลูกค้าทั้งหมด</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800">89</h3>
                        <p class="text-xs text-slate-500">ข้อความส่งเดือนนี้</p>
                    </div>
                </div>

                <div class="bg-amber-50 rounded-2xl border border-amber-100 p-5">
                    <div class="flex items-center gap-2 mb-2 text-amber-600 font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        สัญญาณเตือน
                    </div>
                    <p class="text-sm text-slate-600 mb-3">ลูกค้า 3 รายเงียบไป 48 ชม.</p>
                    <a href="#" class="text-emerald-600 text-sm font-bold flex items-center gap-1 hover:underline">
                        ดูคำแนะนำ <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Config Chart.js
        const ctx = document.getElementById('revenueChart').getContext('2d');

        // Gradient Background for Real Data
        const gradientReal = ctx.createLinearGradient(0, 0, 0, 300);
        gradientReal.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); // Emerald-500 transparent
        gradientReal.addColorStop(1, 'rgba(16, 185, 129, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'คาดการณ์',
                        data: @json($chartData['projected']),
                        borderColor: '#cbd5e1', // Slate-300
                        borderDash: [5, 5],
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'ยอดจริง',
                        data: @json($chartData['data']),
                        borderColor: '#10b981', // Emerald-500
                        backgroundColor: gradientReal,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '฿' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 2],
                            color: '#f1f5f9'
                        },
                        ticks: {
                            callback: function(value) {
                                return value >= 1000 ? (value/1000) + 'k' : value;
                            },
                            font: { size: 10 },
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    </script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="max-w-screen-2xl mx-auto p-4 md:p-6"
         x-data="{
        selectedId: {{ $activities[0]['id'] }},
        activities: {{ json_encode($activities) }},
        get activeActivity() {
            return this.activities.find(a => a.id === this.selectedId);
        },
        copyScript() {
            navigator.clipboard.writeText(this.activeActivity.script);
            alert('คัดลอกข้อความแล้ว!');
        }
     }">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Action Stream</h1>
                <p class="text-slate-500 mt-1">กิจกรรมที่ต้องทำ เรียงตามความสำคัญ</p>
            </div>

            <div class="flex bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
                <button class="px-4 py-1.5 rounded-md text-sm font-medium bg-slate-800 text-white shadow-sm">ทั้งหมด</button>
                <button class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-50">ด่วน</button>
                <button class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-50">ปานกลาง</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <div class="lg:col-span-4 space-y-3 max-h-[calc(100vh-12rem)] overflow-y-auto pr-1 custom-scrollbar">
                @foreach($activities as $activity)
                    <x-activity.list-item :activity="$activity" />
                @endforeach
            </div>

            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 min-h-[600px] relative">

                <div x-show="activeActivity" x-transition.opacity>

                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <x-activity.priority-badge priority="activeActivity.priority" class="text-lg px-3 py-1" />
                                <span class="text-slate-500 text-sm flex items-center gap-1">
                                กำหนด <span x-text="activeActivity.time"></span>
                            </span>
                            </div>
                            <h2 class="text-2xl font-bold text-slate-800">
                                <span class="text-emerald-500" x-text="'[' + activeActivity.action_type + ' LINE]'"></span>
                                <span x-text="activeActivity.customer_name"></span>
                            </h2>
                            <p class="text-slate-500 mt-1" x-text="activeActivity.title"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 mb-1">มูลค่าดีล</p>
                            <p class="text-3xl font-bold text-emerald-500" x-text="'฿' + new Intl.NumberFormat().format(activeActivity.amount)"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-slate-50 p-4 rounded-xl">
                            <p class="text-xs text-slate-400 mb-1">ชื่อเล่น</p>
                            <p class="font-bold text-slate-800 text-lg" x-text="activeActivity.customer_nickname"></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl">
                            <p class="text-xs text-slate-400 mb-1">LINE ID</p>
                            <p class="font-bold text-slate-800 text-lg" x-text="activeActivity.line_id"></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl">
                            <p class="text-xs text-slate-400 mb-1">ติดต่อล่าสุด</p>
                            <p class="font-bold text-slate-800 text-lg" x-text="activeActivity.last_contact"></p>
                        </div>
                    </div>

                    <div x-show="activeActivity.warning" class="bg-amber-50 border border-amber-100 rounded-xl p-4 mb-6 flex items-start gap-3">
                        <div class="p-2 bg-amber-100 rounded-full text-amber-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-amber-600">สัญญาณ</h4>
                            <p class="text-slate-600 text-sm" x-text="activeActivity.warning"></p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="text-sm font-semibold text-slate-500 mb-2">Script สำหรับส่ง</p>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 text-slate-700 leading-relaxed text-lg shadow-inner">
                            <p x-text="activeActivity.script"></p>
                        </div>
                    </div>

                    <div class="flex gap-4 mb-6">
                        <button @click="copyScript()" class="flex-1 py-3 px-4 border border-slate-200 rounded-xl text-slate-700 font-bold hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                            Copy Message
                        </button>
                        <a href="#" class="flex-1 py-3 px-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            Open in LINE
                        </a>
                    </div>

                    <div class="text-center">
                        <button class="text-emerald-500 hover:text-emerald-600 font-medium text-sm flex items-center justify-center gap-1 mx-auto transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            ทำเสร็จแล้ว
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    </style>
@endsection

@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6 font-sans">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900">Customers</h2>
                <p class="text-slate-500 mt-1">ลูกค้าทั้งหมด 4 ราย</p>
            </div>


            <button onclick="window.location='{{ route('customers.create') }}'" class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-lg shadow-slate-900/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                เพิ่มลูกค้าใหม่
            </button>


        </div>

        <div class="flex gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text"
                       class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm shadow-sm"
                       placeholder="ค้นหาชื่อ, ชื่อเล่น, LINE ID...">
            </div>
            <button class="px-5 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-slate-700 rounded-lg flex items-center gap-2 font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <div class="lg:col-span-4 space-y-3">
                <x-customers.card
                    :active="true"
                    nickname="เจ"
                    name="คุณสมชาย วงศ์ดี"
                    handle="@somchai_j"
                    amount="฿156,000"
                    status="active"
                />

                <x-customers.card
                    nickname="นก"
                    name="คุณนก ศรีสุข"
                    handle="@nok_sri"
                    amount="฿45,000"
                    status="active"
                />

                <x-customers.card
                    nickname="พี่หนุ่ม"
                    name="คุณมานพ ธุรกิจดี"
                    handle="@manop_biz"
                    amount="฿520,000"
                    status="inactive"
                />
                <x-customers.card
                    nickname="วิ"
                    name="คุณวิภา สวยงาม"
                    handle="@wipa_suay"
                    amount="฿12,500"
                    status="active"
                />
            </div>

            <div class="lg:col-span-8 bg-white rounded-2xl border border-gray-200 p-6 md:p-8 shadow-sm">

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                            เจ
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-xl font-bold text-slate-900">คุณสมชาย วงศ์ดี</h1>
                                <x-ui.badge status="active" />
                            </div>
                            <p class="text-slate-500 mt-1">ชื่อเล่น: เจ</p>
                        </div>
                    </div>
                    <a href="#" class="inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        Open in LINE
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <x-customers.info-item label="LINE ID" value="@somchai_j">
                        <x-slot:icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </x-slot:icon>
                    </x-customers.info-item>

                    <x-customers.info-item label="เบอร์โทร" value="081-234-5678">
                        <x-slot:icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </x-slot:icon>
                    </x-customers.info-item>

                    <x-customers.info-item label="จังหวัด" value="กรุงเทพฯ">
                        <x-slot:icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </x-slot:icon>
                    </x-customers.info-item>

                    <x-customers.info-item label="ประเภทธุรกิจ" value="คลินิกความงาม">
                        <x-slot:icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </x-slot:icon>
                    </x-customers.info-item>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
                    <x-customers.stat-box label="ยอดขายรวม" value="฿156,000" :isMoney="true" />
                    <x-customers.stat-box label="จำนวนดีล" value="5" />
                    <x-customers.stat-box label="ติดต่อล่าสุด" value="2 วันที่แล้ว" subtext="17 ม.ค. 2567" />
                </div>

                <div>
                    <h3 class="font-bold text-slate-800 text-lg mb-4">ประวัติกิจกรรม</h3>

                    <div class="space-y-1">
                        <div class="flex gap-4 p-3 hover:bg-slate-50 rounded-lg transition-colors">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex-shrink-0 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            </div>
                            <div>
                                <p class="text-slate-800 font-medium">ส่งใบเสนอราคา Spa Package</p>
                                <p class="text-sm text-slate-400">14 ม.ค. 2567</p>
                            </div>
                        </div>

                        <div class="flex gap-4 p-3 hover:bg-slate-50 rounded-lg transition-colors">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex-shrink-0 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <p class="text-slate-800 font-medium">โทรนัดคุยเรื่องโปรโมชั่น</p>
                                <p class="text-sm text-slate-400">12 ม.ค. 2567</p>
                            </div>
                        </div>

                        <div class="flex gap-4 p-3 hover:bg-slate-50 rounded-lg transition-colors">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex-shrink-0 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-slate-800 font-medium">ปิดดีล Treatment Package</p>
                                <p class="text-sm text-slate-400">10 ม.ค. 2567</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

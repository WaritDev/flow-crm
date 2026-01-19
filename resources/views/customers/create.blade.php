@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-6 space-y-6">

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.index') }}" class="p-2 rounded-lg hover:bg-white hover:shadow-sm text-slate-500 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">เพิ่มลูกค้าใหม่</h1>
                    <p class="text-sm text-slate-500">กรอกข้อมูลลูกค้าเพื่อเริ่มติดตามและสร้างยอดขาย</p>
                </div>
            </div>

            <div class="hidden sm:flex gap-3">
                <button type="button" class="px-5 py-2.5 rounded-lg border border-gray-200 bg-white text-slate-600 font-medium hover:bg-gray-50 transition-colors">ยกเลิก</button>
                <button type="submit" form="createCustomerForm" class="px-6 py-2.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 shadow-lg shadow-slate-900/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    บันทึกข้อมูล
                </button>
            </div>
        </div>

        <form id="createCustomerForm" action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-form.section title="ข้อมูลพื้นฐาน" description="ข้อมูลส่วนตัวที่จำเป็นสำหรับการระบุตัวตน">

                <div class="md:col-span-2 flex items-center gap-6 mb-2">
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-full bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden">
                            <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>

                            <img id="preview" class="hidden w-full h-full object-cover" />
                        </div>
                        <label class="absolute bottom-0 right-0 bg-emerald-500 text-white p-1.5 rounded-full cursor-pointer shadow-sm hover:bg-emerald-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <input type="file" name="avatar" class="hidden" onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('preview').classList.remove('hidden');">
                        </label>
                    </div>
                    <div>
                        <h4 class="font-medium text-slate-700">รูปโปรไฟล์</h4>
                        <p class="text-xs text-slate-400 mt-1">แนะนำขนาด 500x500px <br>รองรับไฟล์ JPG, PNG</p>
                    </div>
                </div>

                <x-form.input label="ชื่อ-นามสกุล" name="fullname" placeholder="เช่น นายสมชาย ใจดี" required="true" />

                <x-form.input label="ชื่อเล่น (Nickname)" name="nickname" placeholder="เช่น คุณเจ" required="true" helper="สำคัญ: ใช้เรียกเพื่อสร้างความสนิทสนม" />

                <x-form.toggle label="สถานะ (Status)" name="is_active" />
            </x-form.section>


            <x-form.section title="ช่องทางการติดต่อ" description="ใช้สำหรับติดต่อสื่อสารและส่งโปรโมชั่น">

                <div class="relative">
                    <x-form.input label="LINE ID" name="line_id" placeholder="@lineid" required="true" />
                    <div class="absolute right-3 top-9 text-emerald-500">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .5C5.4.5 0 4.8 0 10.2c0 2.9 1.6 5.5 4.3 7.3-.2.8-.7 2.3-.9 2.8-.1.4 0 .6.4.4.2 0 2.1-1.3 2.9-1.9 1.7.5 3.5.7 5.3.7 6.6 0 12-4.3 12-9.7S16.6.5 12 .5z"/></svg>
                    </div>
                </div>

                <div class="relative">
                    <x-form.input label="เบอร์โทรศัพท์" name="phone" placeholder="08x-xxx-xxxx" type="tel" required="true" />
                    <div class="absolute right-3 top-9 text-slate-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <x-form.input label="อีเมล (Email)" name="email" placeholder="customer@example.com" type="email" />
                </div>

            </x-form.section>


            <x-form.section title="ข้อมูลเชิงลึก & Segmentation" description="เพื่อการวิเคราะห์และแนะนำโปรโมชั่นที่แม่นยำ">

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="text-sm font-semibold text-slate-700">จังหวัด</label>
                    <select name="province" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-slate-800 bg-white">
                        <option value="" disabled selected>เลือกจังหวัด...</option>
                        <option value="Bangkok">กรุงเทพมหานคร</option>
                        <option value="Chiang Mai">เชียงใหม่</option>
                        <option value="Phuket">ภูเก็ต</option>
                        <option value="Chonburi">ชลบุรี</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="text-sm font-semibold text-slate-700">ประเภทธุรกิจ</label>
                    <select name="business_type" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-slate-800 bg-white">
                        <option value="" disabled selected>เลือกประเภทธุรกิจ...</option>
                        <option value="beauty_clinic">คลินิกความงาม / สปา</option>
                        <option value="restaurant">ร้านอาหาร / คาเฟ่</option>
                        <option value="retail">ค้าปลีก / Online Shop</option>
                        <option value="real_estate">อสังหาริมทรัพย์</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700">Tags (ป้ายกำกับ)</label>
                    <div class="p-2 rounded-lg border border-gray-300 bg-white focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200 transition-all flex flex-wrap gap-2">
                    <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-sm flex items-center gap-1">
                        #VIP
                        <button type="button" class="hover:text-emerald-900">&times;</button>
                    </span>
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm flex items-center gap-1">
                        #FacebookLead
                        <button type="button" class="hover:text-blue-900">&times;</button>
                    </span>

                        <input type="text" placeholder="+ พิมพ์แล้วกด Enter" class="flex-1 outline-none min-w-[120px] text-sm py-1">
                    </div>
                    <span class="text-xs text-slate-400">ใช้สำหรับแบ่งกลุ่มลูกค้าเพื่อส่งโปรโมชั่น (เช่น #VIP, #ลูกค้าเก่า)</span>
                </div>

                <div class="md:col-span-2 flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-slate-700">ที่อยู่ (สำหรับจัดส่ง)</label>
                    <textarea name="address" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors text-slate-800 placeholder-gray-400" placeholder="บ้านเลขที่, ถนน, ซอย..."></textarea>
                </div>

            </x-form.section>

            <div class="sm:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 z-10">
                <button type="submit" class="w-full py-3 rounded-lg bg-slate-900 text-white font-bold text-lg shadow-lg">
                    บันทึกข้อมูลลูกค้า
                </button>
            </div>

        </form>
    </div>
@endsection

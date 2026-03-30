<x-guest-layout>
    @php
        $registerStep = ($errors->has('org_name') || $errors->has('org_size') || $errors->has('org_description')) ? 2 : 1;
    @endphp
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-6 relative"
         x-data="{
             step: {{ $registerStep }},
             passwordError: false,
             passwordLengthError: false,
             nameError: false,
             next() {
                 const inputs = document.querySelectorAll('#step1 input');
                 let valid = true;
                 inputs.forEach(i => {
                     if (!i.checkValidity()) { i.reportValidity(); valid = false; return; }
                 });
                 if (!valid) return;
                 const nameVal = document.getElementById('name').value.trim();
                 if (!nameVal.includes(' ')) {
                     this.nameError = true;
                     document.getElementById('name').focus();
                     return;
                 }
                 this.nameError = false;
                 const pass = document.getElementById('password').value;
                 const confirm = document.getElementById('password_confirmation').value;
                 if (pass.length < 8) {
                     this.passwordLengthError = true;
                     document.getElementById('password').focus();
                     return;
                 }
                 this.passwordLengthError = false;
                 if (pass !== confirm) {
                     this.passwordError = true;
                     document.getElementById('password_confirmation').focus();
                     return;
                 }
                 this.passwordError = false;
                 this.step = 2;
             }
         }">

        <a href="{{ url('/') }}" class="absolute top-6 left-6 text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-2 font-medium text-sm group">
            <div class="p-2 bg-white rounded-lg shadow-sm border border-slate-200 group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </div>
            <span class="hidden sm:inline">Back to Home</span>
        </a>

        <div class="mb-8 text-center">
            <div class="flex justify-center mb-3">
                <div class="h-12 w-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">FlowCRM</h1>
            <p class="text-slate-500 text-sm mt-2">สมัครผู้จัดการ — สร้างองค์กรและรหัสเชิญทีมขาย (Sales สมัครผ่านแอปหน้าอื่น)</p>
        </div>

        <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="h-1.5 w-full bg-slate-100">
                <div class="h-full bg-emerald-500 transition-all duration-500 ease-out" :style="'width: ' + (step * 50) + '%'"></div>
            </div>

            <form method="POST" action="{{ route('register') }}" class="p-8" id="register-form">
                @csrf

                <div x-show="step === 1" x-transition.opacity.duration.300ms id="step1">
                    <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600">1</span>
                        บัญชีผู้จัดการ
                    </h2>
                    <div class="space-y-5">
                        <div>
                            <x-input-label for="name" value="ชื่อ–นามสกุล" />
                            <x-text-input id="name" class="block mt-1 w-full px-4 py-2" type="text" name="name" :value="old('name')" required autofocus
                                placeholder="Somchai Yingrak"
                                x-bind:class="nameError ? 'border-red-500' : ''" @input="nameError = false" />
                            <p x-show="nameError" style="display: none;" class="text-sm text-red-600 mt-1">กรุณากรอกชื่อและนามสกุล</p>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="อีเมล" />
                            <x-text-input id="email" class="block mt-1 w-full px-4 py-2" type="email" name="email" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="password" value="รหัสผ่าน" />
                                <x-text-input id="password" class="block mt-1 w-full px-4 py-2" type="password" name="password" required
                                    x-bind:class="passwordLengthError ? 'border-red-500' : ''" @input="passwordLengthError = false" />
                            </div>
                            <div>
                                <x-input-label for="password_confirmation" value="ยืนยัน" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full px-4 py-2" type="password" name="password_confirmation" required
                                    x-bind:class="passwordError ? 'border-red-500' : ''" @input="passwordError = false" />
                            </div>
                        </div>
                        <p x-show="passwordLengthError" style="display: none;" class="text-sm text-red-600">รหัสผ่านอย่างน้อย 8 ตัวอักษร</p>
                        <p x-show="passwordError" style="display: none;" class="text-sm text-red-600">รหัสผ่านไม่ตรงกัน</p>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-50 flex justify-between items-center">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-600">มีบัญชีแล้ว</a>
                        <button type="button" @click="next()" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold">ถัดไป</button>
                    </div>
                </div>

                <div x-show="step === 2" x-cloak x-transition.opacity.duration.300ms>
                    <div class="mb-6">
                        <button type="button" @click="step = 1" class="text-xs font-bold text-slate-400 hover:text-emerald-600 mb-4 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg> กลับ
                        </button>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600">2</span>
                            ตั้งค่าองค์กร
                        </h2>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="org_name" value="ชื่อองค์กร" />
                            <x-text-input id="org_name" class="block mt-1 w-full px-4 py-2" type="text" name="org_name" :value="old('org_name')" required placeholder="Acme Corp" />
                            <x-input-error :messages="$errors->get('org_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="org_size" value="ขนาดบริษัท" />
                            <select name="org_size" required class="block mt-1 w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm text-sm py-2.5">
                                <option value="" disabled {{ old('org_size') ? '' : 'selected' }}>เลือก...</option>
                                <option value="1-10" @selected(old('org_size') === '1-10')>1–10</option>
                                <option value="11-50" @selected(old('org_size') === '11-50')>11–50</option>
                                <option value="50+" @selected(old('org_size') === '50+')>50+</option>
                            </select>
                            <x-input-error :messages="$errors->get('org_size')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="org_description" value="คำอธิบาย (ไม่บังคับ)" />
                            <textarea id="org_description" name="org_description" rows="2" class="block mt-1 w-full border-gray-300 rounded-xl shadow-sm text-sm">{{ old('org_description') }}</textarea>
                            <x-input-error :messages="$errors->get('org_description')" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-50">
                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold">สร้างองค์กรและเข้าสู่ระบบ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>

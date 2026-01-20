<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-6"
         x-data="{
             step: 1,
             role: null,
             passwordError: false,
             passwordLengthError: false,
             nameError: false,

             next() {
                 const inputs = document.querySelectorAll('#step1 input');
                 let valid = true;

                 inputs.forEach(i => {
                     if(!i.checkValidity()) {
                         i.reportValidity();
                         valid = false;
                         return;
                     }
                 });

                 if(valid) {
                     const nameVal = document.getElementById('name').value.trim();
                     if (!nameVal.includes(' ')) {
                         this.nameError = true;
                         valid = false;
                         document.getElementById('name').focus();
                     } else {
                         this.nameError = false;
                     }

                     if (valid) {
                         const pass = document.getElementById('password').value;
                         const confirm = document.getElementById('password_confirmation').value;

                         if (pass.length < 8) {
                             this.passwordLengthError = true;
                             valid = false;
                             document.getElementById('password').focus();
                         } else {
                             this.passwordLengthError = false;
                         }

                         if (valid && pass !== confirm) {
                             this.passwordError = true;
                             valid = false;
                             document.getElementById('password_confirmation').focus();
                         } else {
                             this.passwordError = false;
                         }
                     }
                 }

                 if(valid) this.step = 2;
             }
         }">

        <div class="mb-8 text-center">
            <div class="flex justify-center mb-3">
                <div class="h-12 w-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">FlowCRM</h1>
            <p class="text-slate-500 text-sm mt-2">Create your workspace account</p>
        </div>

        <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">

            <div class="h-1.5 w-full bg-slate-100">
                <div class="h-full bg-emerald-500 transition-all duration-500 ease-out"
                     :style="'width: ' + (step * 33.33) + '%'"></div>
            </div>

            <form method="POST" action="{{ route('register') }}" class="p-8" id="register-form">
                @csrf
                <input type="hidden" name="role" x-model="role">

                <div x-show="step === 1" x-transition.opacity.duration.300ms id="step1">
                    <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600">1</span>
                        Account Details
                    </h2>

                    <div class="space-y-5">
                        <div>
                            <x-input-label for="name" value="Full Name" />
                            <x-text-input
                                id="name"
                                class="block mt-1 w-full px-4 py-2"
                                type="text"
                                name="name"
                                :value="old('name')"
                                required autofocus
                                placeholder="Firstname Lastname"
                                x-bind:class="nameError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                @input="nameError = false"
                            />
                            <p x-show="nameError" style="display: none;" class="text-sm text-red-600 font-medium animate-pulse mt-1">
                                Please enter your First Name and Last Name.
                            </p>
                        </div>

                        <div>
                            <x-input-label for="email" value="Email Address" />
                            <x-text-input id="email" class="block mt-1 w-full px-4 py-2" type="email" name="email" :value="old('email')" required placeholder="example@gmail.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="password" value="Password" />
                                <x-text-input
                                    id="password"
                                    class="block mt-1 w-full px-4 py-2"
                                    type="password"
                                    name="password"
                                    required placeholder="••••••••"
                                    x-bind:class="passwordLengthError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                    @input="passwordLengthError = false"
                                />
                            </div>
                            <div>
                                <x-input-label for="password_confirmation" value="Confirm" />
                                <x-text-input
                                    id="password_confirmation"
                                    class="block mt-1 w-full px-4 py-2"
                                    type="password"
                                    name="password_confirmation"
                                    required placeholder="••••••••"
                                    x-bind:class="passwordError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                    @input="passwordError = false"
                                />
                            </div>
                        </div>

                        <p x-show="passwordLengthError" style="display: none;" class="text-sm text-red-600 font-medium animate-pulse mt-1">
                            Password must be at least 8 characters.
                        </p>

                        <p x-show="passwordError" style="display: none;" class="text-sm text-red-600 font-medium animate-pulse mt-1">
                            Passwords do not match.
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-slate-50 flex justify-between items-center">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-emerald-600 transition">Log in instead</a>
                        <button type="button" @click="next()" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold transition shadow-lg shadow-slate-900/10 flex items-center gap-2">
                            Next Step <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>
                    </div>
                </div>

                <div x-show="step === 2" x-cloak x-transition.opacity.duration.300ms>
                    <div class="mb-6">
                        <button type="button" @click="step = 1" class="text-xs font-bold text-slate-400 hover:text-emerald-600 mb-4 flex items-center gap-1 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg> Back
                        </button>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600">2</span>
                            Choose your role
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div @click="role = 'manager'; step = 3"
                             class="cursor-pointer relative border-2 border-slate-100 rounded-2xl p-5 text-center hover:border-emerald-500 hover:bg-emerald-50/50 transition-all duration-200 group">
                            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform shadow-sm">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <h3 class="font-bold text-slate-800">Create Org</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">I want to set up my company & invite team.</p>
                        </div>

                        <div @click="role = 'sales'; step = 3"
                             class="cursor-pointer relative border-2 border-slate-100 rounded-2xl p-5 text-center hover:border-blue-500 hover:bg-blue-50/50 transition-all duration-200 group">
                            <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 group-hover:-rotate-3 transition-transform shadow-sm">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="font-bold text-slate-800">Join Team</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">I have an invite token from my manager.</p>
                        </div>
                    </div>
                </div>

                <div x-show="step === 3" x-cloak x-transition.opacity.duration.300ms>
                    <div class="mb-6">
                        <button type="button" @click="step = 2" class="text-xs font-bold text-slate-400 hover:text-emerald-600 mb-4 flex items-center gap-1 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg> Change Role
                        </button>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600">3</span>
                            <span x-text="role === 'manager' ? 'Organization Setup' : 'Invitation Code'"></span>
                        </h2>
                    </div>

                    <div x-show="role === 'manager'" class="space-y-4" id="manager-form">
                        <div>
                            <x-input-label for="org_name" value="Organization Name" />
                            <x-text-input id="org_name" class="block mt-1 w-full px-4 py-2" type="text" name="org_name" placeholder="Acme Corp" />
                        </div>
                        <div>
                            <x-input-label for="org_size" value="Company Size" />
                            <select name="org_size" class="block mt-1 w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm text-sm py-2.5 cursor-pointer">
                                <option value="" disabled selected>Select size...</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="50+">50+ employees</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="org_desc" value="Description (Optional)" />
                            <textarea name="org_description" rows="2" class="block mt-1 w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm text-sm" placeholder="Tell us briefly about your business..."></textarea>
                        </div>
                    </div>

                    <div x-show="role === 'sales'" class="text-center py-4" id="sales-form">
                        <x-input-label for="token" value="Enter Invitation Token" class="mb-2" />
                        <x-text-input id="token" class="block w-full text-center text-2xl font-mono tracking-[0.5em] uppercase px-4 py-3 border-2 border-slate-200 focus:border-emerald-500 rounded-xl" type="text" name="invite_token" placeholder="INV-XXXXXX" maxlength="12" />

                        <x-input-error :messages="$errors->get('invite_token')" class="mt-2" />

                        <p class="text-xs text-slate-400 mt-3 bg-slate-50 py-2 rounded-lg inline-block px-4">
                            Check your email inbox for the code
                        </p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-slate-50">
                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                            Complete Registration
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-guest-layout>

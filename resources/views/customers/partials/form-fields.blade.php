<x-form.section title="ข้อมูลพื้นฐาน" description="ข้อมูลส่วนตัวที่จำเป็นสำหรับการระบุตัวตน">
    <div class="md:col-span-2 flex items-center gap-6 mb-2">
        <div class="relative group">
            <div class="w-24 h-24 rounded-full bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden">
                @php
                    $previewUrl = isset($customer) && $customer->img_profile ? asset('storage/' . $customer->img_profile) : '';
                @endphp
                <img id="preview" src="{{ $previewUrl }}" class="{{ $previewUrl ? '' : 'hidden' }} w-full h-full object-cover" />
                <svg id="placeholder-icon" class="{{ $previewUrl ? 'hidden' : '' }} w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <label class="absolute bottom-0 right-0 bg-emerald-500 text-white p-1.5 rounded-full cursor-pointer shadow-sm hover:bg-emerald-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <input type="file" name="avatar" class="hidden" onchange="previewAvatar(this)">
            </label>
        </div>
        <div>
            <h4 class="font-medium text-slate-700">รูปโปรไฟล์</h4>
            <p class="text-xs text-slate-400 mt-1">รองรับ JPG, PNG (Max 2MB)</p>
            @error('avatar') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
    </div>

    <x-form.input label="ชื่อ-นามสกุล" name="fullname" :value="$customer->name ?? ''" required="true" placeholder="เช่น นายสมชาย ใจดี" />
    <x-form.input label="ชื่อเล่น" name="nickname" :value="$customer->nickname ?? ''" placeholder="เช่น เจ" />
    <x-form.toggle label="สถานะ (Status)" name="is_active" :checked="isset($customer) ? ($customer->status === 'active') : true" />
</x-form.section>

<x-form.section title="ช่องทางการติดต่อ" description="ใช้สำหรับติดต่อสื่อสารและส่งโปรโมชั่น">
    <x-form.input label="LINE ID" name="line_id" :value="$customer->line_id ?? ''" required="true" placeholder="@lineid" />
    <x-form.input label="เบอร์โทรศัพท์" name="phone" :value="$customer->phone_num ?? ''" placeholder="08x-xxx-xxxx" />
    <div class="md:col-span-2">
        <x-form.input label="อีเมล (Email)" name="email" :value="$customer->email ?? ''" type="email" placeholder="customer@example.com" />
    </div>
</x-form.section>

<x-form.section title="ข้อมูลเชิงลึก & Segmentation" description="เพื่อการวิเคราะห์และแบ่งกลุ่มลูกค้า">
    <div class="flex flex-col gap-1.5 w-full">
        <label class="text-sm font-semibold text-slate-700">จังหวัด</label>
        <select name="province" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-slate-800 bg-white">
            <option value="" selected>เลือกจังหวัด...</option>
            @foreach(['กรุงเทพมหานคร', 'เชียงใหม่', 'ภูเก็ต', 'ชลบุรี', 'ขอนแก่น'] as $pv)
                <option value="{{ $pv }}" @selected(old('province', $customer->province ?? '') == $pv)>{{ $pv }}</option>
            @endforeach
        </select>
        @error('province') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
    </div>

    <div class="flex flex-col gap-1.5 w-full">
        <label class="text-sm font-semibold text-slate-700">ประเภทธุรกิจ</label>
        <select name="business_type" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-slate-800 bg-white">
            <option value="" selected>เลือกประเภทธุรกิจ...</option>
            @foreach(['beauty_clinic' => 'คลินิกความงาม', 'restaurant' => 'ร้านอาหาร', 'retail' => 'ค้าปลีก'] as $val => $lab)
                <option value="{{ $val }}" @selected(old('business_type', $customer->business_type ?? '') == $val)>{{ $lab }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2 flex flex-col gap-1.5" x-data="tagManager({{ json_encode(old('tags', $customer->tags ?? [])) }})">
        <label class="text-sm font-semibold text-slate-700">Tags (ป้ายกำกับ)</label>
        <div class="p-2 rounded-lg border border-gray-300 bg-white focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200 flex flex-wrap gap-2">
            <template x-for="(tag, index) in tags" :key="index">
                <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-sm flex items-center gap-1">
                    <span x-text="'#' + tag"></span>
                    <button type="button" @click="removeTag(index)" class="hover:text-emerald-900">&times;</button>
                    <input type="hidden" name="tags[]" :value="tag">
                </span>
            </template>
            <input type="text" x-model="input" @keydown.enter.prevent="addTag()" placeholder="+ เพิ่ม Tag" class="flex-1 outline-none text-sm py-1">
        </div>
    </div>

    <div class="md:col-span-2 flex flex-col gap-1.5">
        <label class="text-sm font-semibold text-slate-700">ที่อยู่</label>
        <textarea name="address" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-slate-800">{{ old('address', $customer->address ?? '') }}</textarea>
    </div>
</x-form.section>

<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.getElementById('preview');
                img.src = e.target.result;
                img.classList.remove('hidden');
                document.getElementById('placeholder-icon').classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function tagManager(initialTags) {
        return {
            tags: initialTags || [],
            input: '',
            addTag() {
                const val = this.input.trim().replace('#', '');
                if (val && !this.tags.includes(val)) this.tags.push(val);
                this.input = '';
            },
            removeTag(index) { this.tags.splice(index, 1); }
        }
    }
</script>

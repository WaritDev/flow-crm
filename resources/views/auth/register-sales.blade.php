<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-slate-50">
        <div class="max-w-md text-center space-y-4">
            <h1 class="text-xl font-bold text-slate-800">สมัครสมาชิกฝั่ง Sales</h1>
            <p class="text-sm text-slate-600">
                การสมัครสำหรับพนักงานขายทำผ่าน <strong>แอป FlowCRM (SvelteKit)</strong> เท่านั้น
            </p>
            <p class="text-xs text-slate-500">
                หน้านี้ใช้สำหรับดึง CSRF token — ถ้าคุณเป็น <strong>ผู้จัดการ</strong> ให้สมัครที่เมนู Register บนเว็บนี้แทน
            </p>
            @if(config('app.frontend_url'))
                <a href="{{ config('app.frontend_url') }}/register"
                   class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                    ไปหน้าสมัคร Sales
                </a>
            @endif
        </div>
    </div>
</x-guest-layout>

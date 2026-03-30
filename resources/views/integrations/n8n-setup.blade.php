<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Integrations Setup (n8n + LINE OA)</h1>
                <p class="text-slate-600 mt-2">
                    ใช้หน้านี้เพื่อเชื่อม n8n (Bearer token) และตั้งค่า Webhook URL สำหรับ LINE OA (Messaging API)
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-8">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Organization</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $organization->name }}</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Invite Code</p>
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-mono font-bold text-emerald-700 select-all">{{ $organization->invite_code }}</span>
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $organization->invite_code }}');"
                            class="text-slate-500 hover:text-emerald-700 transition text-sm font-semibold"
                        >
                            Copy
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">n8n API Token (Bearer)</p>

                    @if(session('n8n_plain_text_token'))
                        <div class="mb-3 text-sm text-emerald-700 font-semibold">
                            สร้าง token ใหม่แล้ว (แสดงครั้งเดียว) — กรุณาคัดลอกไปเก็บใน n8n Credentials
                        </div>
                        <div class="flex items-start gap-3">
                            <textarea readonly class="w-full min-h-[80px] rounded-xl border border-slate-200 bg-slate-50 p-3 font-mono text-xs">{{ session('n8n_plain_text_token') }}</textarea>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText(@json(session('n8n_plain_text_token')));"
                                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                                Copy
                            </button>
                        </div>
                    @elseif($plainTextToken)
                        <div class="mb-3 text-sm text-emerald-700 font-semibold">
                            token สำหรับ n8n (แสดงครั้งเดียว) — กรุณาคัดลอกไปเก็บใน n8n Credentials
                        </div>
                        <div class="flex items-start gap-3">
                            <textarea readonly class="w-full min-h-[80px] rounded-xl border border-slate-200 bg-slate-50 p-3 font-mono text-xs">{{ $plainTextToken }}</textarea>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText(@json($plainTextToken));"
                                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                                Copy
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-slate-600">
                            token ถูกสร้างไว้แล้ว (เราไม่เก็บค่า token แบบ plain text) ถ้าต้องการใหม่ให้กด “Rotate Token”
                        </p>
                    @endif

                    <div class="mt-4 flex items-center gap-3">
                        <form method="POST" action="{{ route('integrations.n8n.rotate-token') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600 transition">
                                Rotate Token
                            </button>
                        </form>
                        <p class="text-xs text-slate-500">
                            แนะนำให้ rotate เป็นระยะ และ revoke ทันทีถ้าพบความผิดปกติ
                        </p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">LINE OA Webhook URL (ใส่ใน LINE Developers)</p>
                    <p class="text-sm text-slate-600 mb-3">
                        URL นี้ชี้ไปที่ n8n Webhook (บน managed n8n ของเรา) โดยใช้ path เฉพาะขององค์กรนี้
                    </p>
                    @if(!$n8nBaseUrl)
                        <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            ยังไม่ได้ตั้งค่า <span class="font-mono font-semibold">N8N_URL</span> ใน <span class="font-mono">.env</span>
                            เลยไม่สามารถสร้างลิงก์ Webhook URL แบบเต็มได้
                            <div class="mt-2 text-xs text-amber-800">
                                ตัวอย่าง: <span class="font-mono">N8N_URL=https://xxxx.ngrok-free.app</span>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <textarea readonly class="w-full min-h-[70px] rounded-xl border border-slate-200 bg-slate-50 p-3 font-mono text-xs">{{ $webhookUrl ?: ('(ตั้งค่า N8N_URL ก่อน) Path: ' . ($integration->line_webhook_path ?? '')) }}</textarea>
                        <button type="button"
                                onclick="navigator.clipboard.writeText(@json($webhookUrl ?: ($integration->line_webhook_path ?? '')));"
                                class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                            Copy
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">
                        ใน n8n ให้สร้าง Workflow ที่มี Webhook node และตั้ง Path ให้ตรงกับท้าย URL นี้
                    </p>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">LINE OA Channel Access Token (Optional)</p>
                    <p class="text-sm text-slate-600 mb-4">
                        ไม่จำเป็น ถ้าเรา “ไม่ได้” ส่งข้อความออกจาก FlowCRM เอง (กรณีนี้ให้ n8n จัดการส่ง/ตอบกลับได้)
                        แต่ถ้าอนาคตต้องการให้ FlowCRM ส่งข้อความผ่าน Messaging API ให้กรอกไว้ได้
                    </p>

                    <form method="POST" action="{{ route('integrations.n8n.line-token') }}" class="space-y-3">
                        @csrf
                        <textarea name="line_channel_access_token"
                                  class="w-full min-h-[90px] rounded-xl border border-slate-200 p-3 font-mono text-xs"
                                  placeholder="ใส่ Channel Access Token (ยาวมาก) หรือเว้นว่างไว้"></textarea>
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-xs text-slate-500">
                                สถานะ: {{ $lineTokenPresent ? 'ตั้งค่าแล้ว' : 'ยังไม่ตั้งค่า' }}
                            </p>
                            <button type="submit"
                                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                                Save
                            </button>
                        </div>
                    </form>
                </div>

                <div class="pt-2">
                    <a href="{{ route('teams.index') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 transition">
                        ไปจัดการทีม &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


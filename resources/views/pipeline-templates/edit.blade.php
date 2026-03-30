@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold text-slate-900">แก้ไข: {{ $template->name }}</h1>

        @if($teamsAssigned)
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3 text-sm">
                มีทีมผูก template นี้อยู่ — คุณแก้<strong>ชื่อ / อุตสาหกรรม / คำอธิบาย</strong>ได้เท่านั้น
                หากต้องการเปลี่ยนลำดับ stage ให้ยกเลิกการผูกทีมก่อน (ทีมที่ยังไม่มีดีลเท่านั้น)
            </div>
        @endif

        <form method="POST" action="{{ route('pipeline-templates.update', $template) }}" class="mt-8 space-y-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-slate-700">ชื่อ template</label>
                <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">อุตสาหกรรม</label>
                <input type="text" name="industry" value="{{ old('industry', $template->industry) }}"
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"/>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">คำอธิบาย</label>
                <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $template->description) }}</textarea>
            </div>

            @if(!$teamsAssigned)
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Stages</label>
                    <div id="stages" class="space-y-2">
                        @php
                            $st = old('stages', $template->stages->map(fn($s) => ['name' => $s->name, 'is_won' => $s->is_won])->all());
                        @endphp
                        @foreach($st as $i => $row)
                            <div class="flex gap-2 items-center">
                                <span class="text-xs text-slate-400 w-6">{{ $i + 1 }}</span>
                                <input type="text" name="stages[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}"
                                       required class="flex-1 rounded-lg border-slate-300 text-sm"/>
                                <label class="text-xs text-slate-600 flex items-center gap-1">
                                    <input type="hidden" name="stages[{{ $i }}][is_won]" value="0"/>
                                    <input type="checkbox" name="stages[{{ $i }}][is_won]" value="1" @checked(!empty($row['is_won']))/>
                                    Won
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('stages') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-emerald-600 text-white font-bold px-6 py-2.5 hover:bg-emerald-700">บันทึก</button>
                <a href="{{ route('pipeline-templates.index') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-slate-700 font-medium">กลับ</a>
            </div>
        </form>

        <form method="POST" action="{{ route('pipeline-templates.destroy', $template) }}" class="mt-8"
              onsubmit="return confirm('ลบ template นี้? ต้องไม่มีทีมผูกอยู่');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 font-semibold hover:underline">ลบ template นี้</button>
        </form>
    </div>
@endsection

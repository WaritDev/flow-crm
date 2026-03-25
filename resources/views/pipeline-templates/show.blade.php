@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4 space-y-6">
        <a href="{{ route('pipeline-templates.index') }}" class="text-sm text-slate-500 hover:text-emerald-600">← กลับ</a>

        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $template->name }}</h1>
            @if($template->organization_id === null)
                <span class="inline-block mt-2 text-xs font-bold bg-slate-100 text-slate-700 px-2 py-1 rounded">เทมเพลตระบบ</span>
            @else
                <span class="inline-block mt-2 text-xs font-bold bg-emerald-50 text-emerald-800 px-2 py-1 rounded">ขององค์กร</span>
            @endif
            @if($template->industry)
                <p class="text-slate-600 mt-2">{{ $template->industry }}</p>
            @endif
            @if($template->description)
                <p class="text-slate-600 mt-2">{{ $template->description }}</p>
            @endif
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="font-bold text-slate-800 mb-3">Stages</h2>
            <ol class="list-decimal list-inside space-y-1 text-slate-700 text-sm">
                @foreach($template->stages as $s)
                    <li>{{ $s->name }} @if($s->is_won)<span class="text-emerald-600 text-xs">(Won)</span>@endif</li>
                @endforeach
            </ol>
        </div>

        @if($teamsUsing->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h2 class="font-bold text-slate-800 mb-3">ทีมที่ใช้ template นี้</h2>
                <ul class="text-sm text-slate-700 space-y-2">
                    @foreach($teamsUsing as $t)
                        <li>{{ $t->name }} — {{ $t->deals_count }} ดีล</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($template->organization_id !== null && auth()->user()->isManager())
            <a href="{{ route('pipeline-templates.edit', $template) }}" class="inline-flex rounded-xl bg-slate-900 text-white font-bold px-5 py-2 text-sm hover:bg-slate-800">แก้ไข</a>
        @endif
    </div>
@endsection

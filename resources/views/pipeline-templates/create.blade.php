@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold text-slate-900">Create organization pipeline template</h1>
        <p class="text-slate-500 mt-2 text-sm">Define at least one stage name. Only teams with no deals can be assigned to this template.</p>

        <form method="POST" action="{{ route('pipeline-templates.store') }}" class="mt-8 space-y-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700">Template name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"/>
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Industry / tag (optional)</label>
                <input type="text" name="industry" value="{{ old('industry') }}"
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"/>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Description</label>
                <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Stages</label>
                <div id="stages" class="space-y-2">
                    @php $oldStages = old('stages', [['name' => '', 'is_won' => false], ['name' => '', 'is_won' => false], ['name' => '', 'is_won' => false]]); @endphp
                    @foreach($oldStages as $i => $st)
                        <div class="flex gap-2 items-center stage-row">
                            <span class="text-xs text-slate-400 w-6">{{ $i + 1 }}</span>
                            <input type="text" name="stages[{{ $i }}][name]" value="{{ $st['name'] ?? '' }}"
                                   placeholder="Stage name"
                                   class="flex-1 rounded-lg border-slate-300 text-sm"/>
                            <label class="text-xs text-slate-600 flex items-center gap-1 whitespace-nowrap">
                                <input type="hidden" name="stages[{{ $i }}][is_won]" value="0"/>
                                <input type="checkbox" name="stages[{{ $i }}][is_won]" value="1" @checked(!empty($st['is_won']))/>
                                Won
                            </label>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addStageRow()" class="mt-2 text-sm text-emerald-700 font-medium hover:underline">+ Add stage</button>
                @error('stages') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-emerald-600 text-white font-bold px-6 py-2.5 hover:bg-emerald-700">Save</button>
                <a href="{{ route('pipeline-templates.index') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-slate-700 font-medium hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        let stageIndex = {{ count($oldStages) }};
        function addStageRow() {
            const wrap = document.getElementById('stages');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center stage-row';
            div.innerHTML = `
                <span class="text-xs text-slate-400 w-6">${stageIndex + 1}</span>
                <input type="text" name="stages[${stageIndex}][name]" placeholder="Stage name" class="flex-1 rounded-lg border-slate-300 text-sm"/>
                <label class="text-xs text-slate-600 flex items-center gap-1 whitespace-nowrap">
                    <input type="hidden" name="stages[${stageIndex}][is_won]" value="0"/>
                    <input type="checkbox" name="stages[${stageIndex}][is_won]" value="1"/> Won
                </label>`;
            wrap.appendChild(div);
            stageIndex++;
        }
    </script>
@endsection

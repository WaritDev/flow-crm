@props(['customers', 'stages', 'deal' => null])

@php
    $currentStageId = old('stage', $deal->stage_id ?? ($stages->first()->id ?? ''));
    if (isset($deal) && $deal->lost_at) {
        $currentStageId = 'lost';
    }
    $wonStageId = $stages->where('is_won', true)->first()->id ?? 'won_mock';
@endphp

<div x-data="{
    currentStage: '{{ $currentStageId }}',
    wonStageId: '{{ $wonStageId }}',
    formatCurrency(el) {
        // Optional currency formatting hook
    }
}" class="space-y-6">

    <x-form.section title="Deal details" description="Basic fields for the opportunity">

        <div class="md:col-span-2">
            <x-form.input
                label="Deal name"
                name="name"
                :value="old('name', $deal?->name ?? '')"
                placeholder="e.g. Condo sale – Mr. Ton"
                required="true"
            />
        </div>

        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Customer</label>
            <select name="customer_id"
                    class="w-full mt-1.5 px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white text-slate-800">
                <option value="" disabled {{ !isset($deal) ? 'selected' : '' }}>Select customer…</option>
                @foreach($customers as $c)
                    <option value="{{ $c['id'] }}" @selected(old('customer_id', $deal->customer_id ?? '') == $c['id'])>
                        {{ $c['label'] }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">Search by name, nickname, or LINE ID where supported</p>
        </div>

        <div>
            <x-form.input
                type="number"
                step="0.01"
                label="Amount (THB)"
                name="value"
                :value="old('value', $deal?->value ?? '')"
                placeholder="0.00"
                required="true"
            />
        </div>

        <div>
            <x-form.input
                type="date"
                label="Expected close date"
                name="expected_close_date"
                :value="old('expected_close_date', (isset($deal) && $deal->expected_close_date) ? \Carbon\Carbon::parse($deal->expected_close_date)->format('Y-m-d') : '')"
            />
        </div>

    </x-form.section>


    <x-form.section title="Progress" description="Update stage and plan the next step">

        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Stage</label>
            <select name="stage" x-model="currentStage"
                    class="w-full mt-1.5 px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 bg-white font-medium">
                @foreach($stages as $stage)
                    <option value="{{ $stage->id }}" class="{{ $stage->is_won ? 'text-emerald-600 font-bold' : '' }}">
                        {{ $stage->name }}
                        {{ $stage->is_won ? '(Won)' : '' }}
                    </option>
                @endforeach
                <option value="lost" class="text-red-600 font-bold">Closed lost</option>
            </select>
        </div>

        <div class="md:col-span-2 bg-red-50 p-4 rounded-lg border border-red-200" x-show="currentStage == 'lost'"
             x-transition style="display: none;">
            <label class="text-sm font-bold text-red-700">Lost reason *</label>
            <select name="lost_reason"
                    class="w-full mt-1.5 px-3 py-2 rounded border border-red-300 text-red-900 bg-white">
                <option value="">Choose a reason…</option>
                <option value="price">Lost on price</option>
                <option value="competitor">Competitor won</option>
                <option value="not_interested">Not interested / churned</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="contents" x-show="currentStage != wonStageId && currentStage != 'lost'">
            <div class="md:col-span-2 my-2 border-t border-slate-100"></div>

            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-1.5">
                    <label class="text-sm font-bold text-emerald-700">Next action</label>
                    <span
                        class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-bold">MANDATORY</span>
                </div>
                <input type="text" name="next_action" placeholder="e.g. Confirm appointment, send revised quote"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-emerald-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all text-slate-800"
                       value="{{ old('next_action') }}">
                <p class="text-xs text-slate-400 mt-1">Creates a task on your calendar automatically</p>
            </div>

            <div>
                <label class="text-sm font-bold text-emerald-700">Due date</label>
                <input type="date" name="next_action_date"
                       class="w-full mt-1.5 px-4 py-2.5 rounded-lg border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                       value="{{ old('next_action_date') }}">
            </div>

            <div class="flex flex-col justify-end pb-1">
                <div class="text-xs text-slate-500 bg-slate-100 p-2 rounded">
                    Owner: <strong>{{ Auth::user()->name }}</strong> (default)
                </div>
            </div>
        </div>

    </x-form.section>

    <x-form.section title="Notes">
        <div class="md:col-span-2">
            <x-form.input
                type="textarea"
                label="Description"
                name="description"
                :value="old('description', $deal?->description ?? '')"
                rows="3"
            />
        </div>
    </x-form.section>

</div>

@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-6 space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.index') }}" class="p-2 text-slate-500 hover:bg-white rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">แก้ไขข้อมูล: {{ $customer->name }}</h1>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" form="editCustomerForm" class="px-6 py-2.5 bg-slate-900 text-white rounded-lg font-medium shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    บันทึกการแก้ไข
                </button>
            </div>
        </div>

        <form id="editCustomerForm" action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT') {{-- สำคัญมากสำหรับหน้า Edit --}}

            @include('customers.partials.form-fields')

        </form>
    </div>
@endsection

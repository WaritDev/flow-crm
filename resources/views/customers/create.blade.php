@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-6 space-y-6">

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.index') }}" class="p-2 rounded-lg hover:bg-white hover:shadow-sm text-slate-500 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Add customer</h1>
                    <p class="text-sm text-slate-500">Enter customer details to start tracking and closing deals.</p>
                </div>
            </div>

            <div class="hidden sm:flex gap-3">
                <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-200 bg-white text-slate-600 font-medium hover:bg-gray-50 transition-colors inline-flex items-center justify-center">Cancel</a>
                <button type="submit" form="createCustomerForm" class="px-6 py-2.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 shadow-lg shadow-slate-900/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Save
                </button>
            </div>
        </div>

        <form id="createCustomerForm" action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @include('customers.partials.form-fields')

            <div class="sm:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 z-10">
                <button type="submit" class="w-full py-3 rounded-lg bg-slate-900 text-white font-bold text-lg shadow-lg">
                    Save customer
                </button>
            </div>

        </form>
    </div>
@endsection

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Select Organization</h2>
        <p class="text-sm text-slate-500 mt-1">Choose an organization to manage its managers and sales representatives.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($organizations as $org)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="h-12 w-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m1-8h1"></path>
                            </svg>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            {{ $org->users_count }} Members
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 mb-1">{{ $org->name }}</h3>
                    <p class="text-sm text-slate-500 line-clamp-2 mb-6">Manage all access and roles for this organization's staff.</p>
                    <a href="{{ route('users.index', ['organization_id' => $org->id]) }}" 
                        class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-900 text-white rounded-xl font-medium hover:bg-slate-800 transition-all group-hover:scale-[1.02]">
                        Manage Users
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 bg-white rounded-2xl border border-dashed border-slate-300 text-center">
                <p class="text-slate-500">No organizations found. Create one first.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $organizations->links() }}
    </div>
</div>
@endsection
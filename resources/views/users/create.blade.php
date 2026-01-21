@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Add New Sales Rep</h2>
                <p class="text-sm text-slate-500">Create a new user account for your sales team.</p>
            </div>
            <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors" placeholder="Firstname Lastname">
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors" placeholder="example@gmail.com">
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Assign Team</label>
                    <div class="relative">
                        <select name="team_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white appearance-none cursor-pointer">
                            <option value="">- Select a Team -</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Can be assigned later if needed.</p>
                    @error('team_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <hr class="border-slate-100">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <input type="hidden" name="role" value="sales">

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-lg hover:bg-emerald-700 font-medium shadow-sm transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        Create Sales Rep
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

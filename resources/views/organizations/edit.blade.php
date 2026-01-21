@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Edit Organization</h2>
                <p class="text-sm text-slate-500">Update details for {{ $organization->name }}.</p>
            </div>
            <a href="{{ route('organizations.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <form action="{{ route('organizations.update', $organization->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Organization Name *</label>
                    <input type="text" name="name" value="{{ old('name', $organization->name) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug', $organization->slug) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Size</label>
                    <select name="size" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">Select size</option>
                        <option value="small" {{ old('size', $organization->size) == 'small' ? 'selected' : '' }}>Small (1-10)</option>
                        <option value="medium" {{ old('size', $organization->size) == 'medium' ? 'selected' : '' }}>Medium (11-50)</option>
                        <option value="large" {{ old('size', $organization->size) == 'large' ? 'selected' : '' }}>Large (51-200)</option>
                        <option value="enterprise" {{ old('size', $organization->size) == 'enterprise' ? 'selected' : '' }}>Enterprise (200+)</option>
                    </select>
                    @error('size') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors" placeholder="Enter organization description">{{ old('description', $organization->description) }}</textarea>
                    @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invite Code</label>
                    <input type="text" name="invite_code" value="{{ old('invite_code', $organization->invite_code) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    @error('invite_code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 font-medium shadow-sm transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Update Organization
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

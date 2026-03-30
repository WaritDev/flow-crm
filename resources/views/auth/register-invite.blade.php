<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-2xl">
            <h2 class="text-2xl font-bold text-slate-800 text-center mb-6">Join FlowCRM</h2>
            
            <p class="text-sm text-slate-600 mb-6 text-center">
                You have been invited to join the sales team. Please complete your registration.
            </p>
    
            <form method="POST" action="{{ route('register.invite.submit') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $invitation->token }}">
    
                <div class="mb-4">
                    <label class="block font-medium text-sm text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ $invitation->email }}" readonly 
                        class="mt-1 block w-full bg-slate-100 border-slate-300 rounded-md shadow-sm text-slate-500 cursor-not-allowed">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
    
                <div class="mb-4">
                    <label class="block font-medium text-sm text-slate-700">Full Name</label>
                    <input type="text" name="name" required autofocus 
                        class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
    
                <div class="mb-6">
                    <label class="block font-medium text-sm text-slate-700">Password</label>
                    <input type="password" name="password" required 
                        class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
    
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-slate-900 hover:bg-slate-800">
                    Create Account
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
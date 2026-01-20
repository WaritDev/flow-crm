<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FlowCRM - Streamline Your Sales</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white text-slate-800">
<nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/20">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-600">
                    FlowCRM
                </span>
            </div>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        @if(auth()->user()->isManager())
                            <a href="{{ route('teams.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors">
                                Manage Team
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition-all">
                                Get Started
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>

<div class="relative isolate pt-14">
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
        <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-emerald-200 to-teal-200 opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
    </div>

    <div class="py-24 sm:py-32 lg:pb-40">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl">
                    Manage your sales team <br>
                    <span class="text-emerald-600">with ease & flow.</span>
                </h1>
                <p class="mt-6 text-lg leading-8 text-slate-600">
                    FlowCRM helps service-based businesses streamline their operations. Manage teams, track sales performance, and organize customer data in one simple platform.
                </p>

                <div class="mt-10 flex items-center justify-center gap-x-6">
                    @auth
                        @if(auth()->user()->isManager())
                            <a href="{{ route('users.index') }}" class="rounded-md bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition-all">
                                Go to Team Management
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition-all">
                                Go to Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="rounded-md bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition-all">
                            Start Free Trial
                        </a>
                        <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-slate-900 hover:text-emerald-600 transition-colors">
                            Log in <span aria-hidden="true">→</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-slate-50 py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:text-center">
            <h2 class="text-base font-semibold leading-7 text-emerald-600">Everything you need</h2>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Built for Managers & Sales Teams
            </p>
            <p class="mt-6 text-lg leading-8 text-slate-600">
                Stop using spreadsheets. FlowCRM gives you the tools to manage your team and close deals faster.
            </p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
            <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-3 lg:gap-y-16">

                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-slate-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 5.472m0 0a9.09 9.09 0 00-3.242 3.22m3.242-3.22a3 3 0 014.033 4.007" />
                            </svg>
                        </div>
                        Team Management
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-slate-600">
                        Create teams, assign sales representatives, and manage permissions easily.
                    </dd>
                </div>

                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-slate-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                            </svg>
                        </div>
                        Sales Tracking
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-slate-600">
                        Monitor performance, view customer lists, and track sales activities in real-time.
                    </dd>
                </div>

                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-slate-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        Simple & Clean
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-slate-600">
                        A user-friendly interface designed to keep your focus on closing deals, not fighting software.
                    </dd>
                </div>

            </dl>
        </div>
    </div>
</div>

<footer class="bg-white py-10">
    <div class="text-center text-slate-500 text-sm">
        &copy; {{ date('Y') }} FlowCRM. All rights reserved.
    </div>
</footer>

</body>
</html>

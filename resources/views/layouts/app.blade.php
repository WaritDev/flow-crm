<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FlowCRM') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        const isCollapsed = localStorage.getItem('sidebarState') === 'true';
        document.documentElement.style.setProperty('--sidebar-width', isCollapsed ? '5rem' : '18rem');
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-900" 
        x-data="{ sidebarCollapsed: localStorage.getItem('sidebarState') === 'true' }"
        x-init="$watch('sidebarCollapsed', val => {
            localStorage.setItem('sidebarState', val);
            document.documentElement.style.setProperty('--sidebar-width', val ? '5rem' : '18rem');
        })">

@include('layouts.sidebar')

<main class="min-h-screen transition-all duration-300 ease-in-out bg-slate-50"
        style="margin-left: var(--sidebar-width);">

    <div class="p-6 md:p-8">
        @yield('content')
    </div>

</main>

</body>
</html>
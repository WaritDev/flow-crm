<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FlowCRM') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900" x-data="{ sidebarCollapsed: false }">

@include('layouts.sidebar')

<main class="min-h-screen transition-all duration-300 ease-in-out bg-slate-50"
      :class="sidebarCollapsed ? 'ml-20' : 'ml-72'">

    <div class="p-6 md:p-8">
        @yield('content')
    </div>

</main>

</body>
</html>

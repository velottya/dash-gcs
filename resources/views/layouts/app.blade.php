<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-body text-dark antialiased">
    <div class="flex h-screen overflow-hidden bg-gray-50">
        @include('layouts.partials.sidebar')

        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="flex items-center justify-between border-b border-dark/10 bg-white px-6 py-4">
                <div>
                    <h1 class="font-heading text-lg font-extrabold text-dark">@yield('title', 'Dashboard')</h1>
                    @hasSection('subtitle')
                        <p class="text-sm text-dark/50">@yield('subtitle')</p>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-dark">{{ session('nama') ?? session('username') }}</p>
                        <p class="text-xs text-dark/50">{{ session('jabatan') }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-dark/5 text-dark/60 transition hover:bg-red-50 hover:text-red-600"
                            title="Keluar">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

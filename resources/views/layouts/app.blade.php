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

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-dark">{{ session('nama') ?? session('username') }}</p>
                        <p class="text-xs text-dark/50">{{ \App\Support\Role::label((int) session('level')) }}</p>
                    </div>

                    <div class="relative">
                        <button id="profile-toggle" type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-gold text-sm font-black text-dark transition hover:ring-2 hover:ring-gold hover:ring-offset-1"
                            title="Profil">
                            {{ strtoupper(substr(session('nama') ?? session('username') ?? 'U', 0, 1)) }}
                        </button>

                        <div id="profile-menu"
                            class="absolute right-0 top-full z-50 mt-2 hidden w-48 origin-top-right rounded-xl border border-dark/10 bg-white py-1 shadow-lg">
                            <a href="{{ route('profile.user') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">
                                <i class="fas fa-user w-4 text-center text-dark/40"></i> Profil Saya
                            </a>
                            <a href="{{ route('profile.company') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">
                                <i class="fas fa-building w-4 text-center text-dark/40"></i> Profil Perusahaan
                            </a>
                            <div class="my-1 border-t border-dark/10"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 transition hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt w-4 text-center"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        const toggle = document.getElementById('profile-toggle');
                        const menu = document.getElementById('profile-menu');
                        toggle.addEventListener('click', function (e) {
                            e.stopPropagation();
                            menu.classList.toggle('hidden');
                        });
                        document.addEventListener('click', function () {
                            menu.classList.add('hidden');
                        });
                    })();
                </script>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-dark font-body antialiased relative overflow-hidden">

    <div class="pointer-events-none fixed inset-0 z-0 flex h-full w-full justify-between">
        <x-supergrafis position="top-left" size="w-48 h-48 md:w-64 md:h-64" class="absolute top-0 left-0" />
        <x-supergrafis position="bottom-right" size="w-72 h-72 md:w-[600px] md:h-[600px]" class="absolute bottom-0 right-0" />
    </div>

    <div class="relative z-10 flex min-h-screen items-center justify-center overflow-y-auto px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex flex-col items-center text-center">
                <img src="{{ asset('logo.png') }}" alt="PT Gresik Cipta Sejahtera" class="mb-4 h-24 w-24 drop-shadow">
                <h1 class="font-heading text-3xl font-black tracking-tight text-white">DASH GCS</h1>
                <p class="mt-1 text-sm font-medium text-white/70">PT Gresik Cipta Sejahtera</p>
            </div>

            <div class="rounded-2xl bg-cream p-8 shadow-2xl">
                <h2 class="font-heading text-xl font-extrabold text-dark">Masuk ke Dashboard</h2>
                <p class="mb-6 mt-1 text-sm text-dark/60">Silakan masuk menggunakan akun Anda.</p>

                @if (session('message'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('message') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="username" class="mb-1 block text-sm font-semibold text-dark">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            class="w-full rounded-lg border border-dark/15 px-4 py-2.5 text-dark focus:border-green focus:outline focus:outline-2 focus:outline-green/30"
                            placeholder="Masukkan username">
                    </div>

<div>
    <label for="password" class="mb-1 block text-sm font-semibold text-dark">Password</label>

    <div class="relative">
        <input
            type="password"
            id="password"
            name="password"
            required
            class="w-full rounded-lg border border-dark/15 px-4 py-2.5 pr-10 text-dark focus:border-green focus:outline focus:outline-2 focus:outline-green/30"
            placeholder="Masukkan password">

        <button
            type="button"
            id="togglePassword"
            class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-dark focus:outline-none">

            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function (e) {
        // Toggle tipe input antara password dan text
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);

        // Opsional: Ubah bentuk ikon jika ingin menambahkan efek mata dicoret (eye-slash)
        if (type === 'text') {
            // Ikon Mata Dicoret
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
            `;
        } else {
            // Ikon Mata Normal
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            `;
        }
    });
</script>

                    <button
                        type="submit"
                        class="font-heading w-full rounded-lg bg-gold px-4 py-3 font-bold text-dark transition hover:bg-gold-light">
                        Masuk
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-white/50">
                &copy; {{ now()->year }} PT Gresik Cipta Sejahtera. Seluruh hak cipta dilindungi.
            </p>
        </div>
    </div>
</body>
</html>

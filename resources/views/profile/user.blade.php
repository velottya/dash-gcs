@extends('layouts.app')

@section('title', 'Profil Saya')

@php
    use App\Support\Role;
    $nama    = $pegawai->nama ?? $fallbackNama ?? null;
    $bagian  = $pegawai->BAGIAN ?? $fallbackJabatan ?? null;
    $jabatan = $pegawai->jabatan ?? session('jabatan') ?? null;
    $alamat  = collect([$pegawai->WILAYAH ?? null, $pegawai->UNIT_KERJA ?? null])
        ->filter(fn ($p) => trim((string) $p) !== '')
        ->map(fn ($p) => trim($p))
        ->implode(', ');
    $lvl       = (int) session('level');
    $roleLabel = Role::label($lvl);
    if (in_array($lvl, [Role::GM, Role::MANAGER]) && !empty($bagian)) {
        $roleLabel = Role::label($lvl) . ' ' . ucwords(strtolower($bagian));
    }
@endphp

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green/10 px-4 py-3 text-sm text-green">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="max-w-lg space-y-4">
        {{-- Kartu profil --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 pb-2 pt-5">
                <p class="text-xs uppercase tracking-wide text-dark/40">{{ $bagian ?? '-' }}</p>
                <span class="rounded-full bg-green/10 px-2 py-0.5 text-xs font-semibold text-green">{{ $roleLabel }}</span>
            </div>
            <div class="flex items-center gap-4 px-6 pb-6">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gold text-2xl font-black text-dark">
                    {{ $nama ? strtoupper(substr($nama, 0, 1)) : '?' }}
                </div>
                <div>
                    <h2 class="font-heading text-lg font-extrabold text-dark">
                        {{ $nama ? ucwords(strtolower($nama)) : '-' }}
                    </h2>
                    @if ($jabatan)
                        <p class="mt-0.5 text-sm text-dark/60">{{ $jabatan }}</p>
                    @endif
                    @if ($alamat !== '')
                        <p class="mt-1 text-sm text-dark/60">
                            <i class="fas fa-building mr-2 text-dark/30"></i>{{ $alamat }}
                        </p>
                    @endif
                    @if (!empty($extra['phone']))
                        <p class="mt-1 text-sm text-dark/60">
                            <i class="fas fa-phone mr-2 text-dark/30"></i>{{ $extra['phone'] }}
                        </p>
                    @endif
                    @if (!empty($extra['email']))
                        <p class="mt-1 text-sm text-dark/60">
                            <i class="fas fa-envelope mr-2 text-dark/30"></i>{{ $extra['email'] }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-dark/10 px-6 py-4">
                <button type="button"
                    onclick="document.getElementById('modal-edit-user').showModal()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm font-semibold text-dark transition hover:bg-dark/5">
                    <i class="fas fa-pencil-alt mr-1"></i> Ubah Profil
                </button>
                <button type="button"
                    onclick="document.getElementById('modal-change-password').showModal()"
                    class="rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">
                    <i class="fas fa-key mr-1"></i> Ganti Password
                </button>
            </div>
        </div>

        {{-- Info NIK & Username --}}
        <div class="rounded-xl bg-white shadow-sm px-6 py-4">
            <p class="text-xs uppercase tracking-wide text-dark/40 mb-3">Informasi Akun</p>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-dark/40">NIK</p>
                    <p class="font-medium text-dark">{{ session('nik') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-dark/40">Username</p>
                    <p class="font-medium text-dark">{{ session('username') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-dark/40">Role</p>
                    <p class="font-medium text-dark">{{ $roleLabel }}</p>
                </div>
                <div>
                    <p class="text-xs text-dark/40">Login Terakhir</p>
                    <p class="font-medium text-dark">{{ $lastLogin ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         MODAL: Ubah Profil
    ================================================================ --}}
    <dialog id="modal-edit-user" class="w-full max-w-md rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Ubah Profil</h3>
            <button type="button" onclick="document.getElementById('modal-edit-user').close()"
                class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <form method="POST" action="{{ route('profile.user.update') }}" class="space-y-4 p-5">
            @csrf
            <div class="space-y-3 p-4 rounded-lg bg-dark/3">
                <div>
                    <label class="mb-1 block text-xs text-dark/50">Nama (dari data SDM)</label>
                    <input type="text" disabled value="{{ $nama ? ucwords(strtolower($nama)) : '-' }}"
                        class="w-full rounded-lg border border-dark/10 bg-dark/5 px-3 py-2 text-sm text-dark/60 cursor-not-allowed">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-dark/50">Jabatan / Bagian</label>
                    <input type="text" disabled value="{{ $bagian ?? '-' }}"
                        class="w-full rounded-lg border border-dark/10 bg-dark/5 px-3 py-2 text-sm text-dark/60 cursor-not-allowed">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">No. Telepon</label>
                <input type="text" name="phone" value="{{ $extra['phone'] ?? '' }}"
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Email</label>
                <input type="email" name="email" value="{{ $extra['email'] ?? '' }}"
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-user').close()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">Simpan</button>
            </div>
        </form>
    </dialog>

    {{-- ================================================================
         MODAL: Ganti Password
    ================================================================ --}}
    <dialog id="modal-change-password" class="w-full max-w-md rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Ganti Password</h3>
            <button type="button" onclick="document.getElementById('modal-change-password').close()"
                class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <form method="POST" action="{{ route('profile.user.change-password') }}" class="space-y-4 p-5">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Password Lama</label>
                <input type="password" name="current_password" required
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Password Baru</label>
                <input type="password" name="new_password" required minlength="6"
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" required
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-change-password').close()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">Simpan</button>
            </div>
        </form>
    </dialog>
@endsection

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

    <div class="mx-auto max-w-xl space-y-4">
        {{-- Kartu profil --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="flex flex-col items-center gap-4 px-8 pb-8 pt-10">
                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-gold text-4xl font-black text-dark shadow-lg ring-4 ring-gold/20">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Foto profil" class="h-full w-full object-cover">
                    @else
                        {{ $nama ? strtoupper(substr($nama, 0, 1)) : '?' }}
                    @endif
                </div>
                <div class="text-center">
                    <h2 class="font-heading text-lg font-extrabold text-dark">
                        {{ $nama ? ucwords(strtolower($nama)) : '-' }}
                    </h2>
                    <div class="mt-1 flex items-center justify-center gap-2">
                        <span class="rounded-full bg-green/10 px-2 py-0.5 text-xs font-semibold text-green">{{ $roleLabel }}</span>
                        @if ($bagian)
                            <span class="text-xs uppercase tracking-wide text-dark/40">{{ $bagian }}</span>
                        @endif
                    </div>
                </div>
                <div class="space-y-1 text-center">
                    @if ($jabatan)
                        <p class="text-sm text-dark/60">{{ $jabatan }}</p>
                    @endif
                    @if ($alamat !== '')
                        <p class="text-sm text-dark/60">
                            <i class="fas fa-building mr-2 text-dark/30"></i>{{ $alamat }}
                        </p>
                    @endif
                    @if (!empty($extra['phone']))
                        <p class="text-sm text-dark/60">
                            <i class="fas fa-phone mr-2 text-dark/30"></i>{{ $extra['phone'] }}
                        </p>
                    @endif
                    @if (!empty($extra['email']))
                        <p class="text-sm text-dark/60">
                            <i class="fas fa-envelope mr-2 text-dark/30"></i>{{ $extra['email'] }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex justify-center gap-2 border-t border-dark/10 px-8 py-4">
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
        <div class="rounded-xl bg-white shadow-sm px-8 py-6">
            <p class="text-center text-xs uppercase tracking-wide text-dark/40 mb-3">Informasi Akun</p>
            <div class="grid grid-cols-2 gap-3 text-sm text-center">
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
        <form method="POST" action="{{ route('profile.user.update') }}" enctype="multipart/form-data" class="space-y-4 p-5">
            @csrf
            <div class="flex flex-col items-center gap-3 pb-2 pt-1">
                <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-gold text-2xl font-black text-dark shadow-md ring-4 ring-gold/20" id="foto-preview-wrap">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Foto profil" class="h-full w-full object-cover" id="foto-preview-img">
                    @else
                        <span id="foto-preview-initial">{{ $nama ? strtoupper(substr($nama, 0, 1)) : '?' }}</span>
                    @endif
                </div>
                <label for="foto-input" class="cursor-pointer text-xs font-semibold text-green hover:underline">
                    <i class="fas fa-camera mr-1"></i> Ganti Foto
                </label>
                <input type="file" name="foto" id="foto-input" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="hidden">
                <p class="text-xs text-dark/40">JPG, JPEG, atau PNG — maks. 2MB</p>
                <p id="foto-error" class="hidden text-xs font-medium text-red-600"></p>
            </div>
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
                <div class="relative">
                    <input type="password" name="current_password" id="pwd-current" required
                        class="w-full rounded-lg border border-dark/20 px-3 py-2 pr-10 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <button type="button" onclick="togglePwd('pwd-current', 'eye-current')"
                        class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center text-dark/30 transition hover:text-dark/60">
                        <i class="fas fa-eye text-sm leading-none" id="eye-current"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password" id="pwd-new" required minlength="6"
                        class="w-full rounded-lg border border-dark/20 px-3 py-2 pr-10 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <button type="button" onclick="togglePwd('pwd-new', 'eye-new')"
                        class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center text-dark/30 transition hover:text-dark/60">
                        <i class="fas fa-eye text-sm leading-none" id="eye-new"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password_confirmation" id="pwd-confirm" required
                        class="w-full rounded-lg border border-dark/20 px-3 py-2 pr-10 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <button type="button" onclick="togglePwd('pwd-confirm', 'eye-confirm')"
                        class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center text-dark/30 transition hover:text-dark/60">
                        <i class="fas fa-eye text-sm leading-none" id="eye-confirm"></i>
                    </button>
                </div>
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

@push('head')
<script>
function togglePwd(inputId, iconId) {
    const inp = document.getElementById(inputId);
    const ico = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inp.type = 'password';
        ico.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('foto-input').addEventListener('change', function () {
        const file = this.files[0];
        const errorEl = document.getElementById('foto-error');
        errorEl.classList.add('hidden');
        errorEl.textContent = '';

        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png'];
        const maxBytes = 2 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            errorEl.textContent = 'Format tidak didukung. Gunakan JPG, JPEG, atau PNG.';
            errorEl.classList.remove('hidden');
            this.value = '';
            return;
        }
        if (file.size > maxBytes) {
            errorEl.textContent = 'Ukuran file melebihi 2MB.';
            errorEl.classList.remove('hidden');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const wrap = document.getElementById('foto-preview-wrap');
            wrap.innerHTML = '';
            const img = document.createElement('img');
            img.id = 'foto-preview-img';
            img.alt = 'Foto profil';
            img.className = 'h-full w-full object-cover';
            img.src = e.target.result;
            wrap.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush

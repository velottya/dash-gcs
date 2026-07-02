@extends('layouts.app')

@section('title', 'Manajemen User')

@php
    $roleColors = [
        2 => 'bg-blue-100 text-blue-700',
        3 => 'bg-amber-100 text-amber-700',
        4 => 'bg-purple-100 text-purple-700',
    ];
    $roleLabels = [2 => 'General Manager', 3 => 'Manager', 4 => 'Direksi'];
@endphp

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
            <i class="fas fa-check-circle mr-1.5"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600">
            <i class="fas fa-exclamation-circle mr-1.5"></i> {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        @if ($isGmView)
            <p class="text-sm text-dark/50">Daftar Manager di bawah Anda (hanya lihat)</p>
        @else
            <p class="text-sm text-dark/50"></p>
            <button type="button" onclick="openTambah()"
                class="flex items-center gap-2 rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">
                <i class="fas fa-plus"></i> Tambah User
            </button>
        @endif
    </div>

    {{-- ── Tabel User ──────────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="border-b border-dark/10 bg-dark/5">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Nama / NIK</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Username</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Jabatan / Bagian</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Role</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Dibuat</th>
                    <th class="px-4 py-3 text-center font-semibold text-dark/70 w-44">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark/5">
                @forelse ($users as $u)
                    @php $lvl = (int) $u->ID_LEVEL; @endphp
                    <tr class="hover:bg-dark/2">
                        <td class="px-4 py-3">
                            <p class="font-medium text-dark">{{ ucwords(strtolower($u->nama ?? '-')) }}</p>
                            <p class="text-xs text-dark/40">{{ $u->NIK }}</p>
                        </td>
                        <td class="px-4 py-3 text-dark/70">{{ $u->USERNAME }}</td>
                        <td class="px-4 py-3">
                            <p class="text-dark/70">{{ $u->jabatan ?? '-' }}</p>
                            <p class="text-xs text-dark/40">{{ $u->BAGIAN ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $roleColors[$lvl] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $roleLabels[$lvl] ?? '-' }}
                            </span>
                            @if (in_array($lvl, [2, 3]) && !empty($u->BAGIAN))
                                <p class="mt-0.5 text-xs text-dark/50">{{ ucwords(strtolower($u->BAGIAN)) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($u->data_aktif === 'Aktif')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Non-Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-dark/50">
                            {{ $u->DATE_CREATE ? \Carbon\Carbon::parse($u->DATE_CREATE)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    onclick="openDetail('{{ $u->NIK }}')"
                                    title="Detail"
                                    class="rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs text-blue-600 transition hover:bg-blue-100">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if (!$isGmView)
                                    <button type="button"
                                        onclick="openEdit({{ json_encode($u) }}, {{ json_encode($gms) }})"
                                        title="Edit"
                                        class="flex items-center gap-1 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-600 transition hover:bg-amber-100">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button type="button"
                                        onclick="openReset('{{ $u->NIK }}', '{{ addslashes(ucwords(strtolower($u->nama ?? $u->USERNAME))) }}')"
                                        title="Reset Password"
                                        class="flex items-center gap-1 rounded-lg bg-green/10 px-3 py-1.5 text-xs font-medium text-green transition hover:bg-green/20">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button type="button"
                                        onclick="openDelete('{{ $u->NIK }}', '{{ addslashes(ucwords(strtolower($u->nama ?? $u->USERNAME))) }}')"
                                        title="Hapus"
                                        class="flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <i class="fas fa-users mb-3 text-3xl block text-dark/20"></i>
                            <p class="text-sm text-dark/40">Belum ada user terdaftar.</p>
                            <p class="text-xs text-dark/30 mt-1">Klik "Tambah User" untuk menambahkan user pertama.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================================================================
         MODAL: Tambah User Baru
    ================================================================ --}}
    <dialog id="modal-tambah" class="w-full max-w-lg rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Tambah User Baru</h3>
            <button type="button" onclick="closeTambah()" class="rounded p-1 text-dark/40 hover:text-dark">&times;</button>
        </div>
        <form id="form-tambah" method="POST" action="{{ route('users.store') }}" class="space-y-4 p-5" autocomplete="off">
            @csrf
            <input type="hidden" name="nik" id="tambah-nik">

            {{-- Honeypot: absorbs Chrome's "is this a login form?" heuristic so the
                 browser doesn't attach its saved-credentials autofill dropdown to the
                 pegawai search field below (that native popup renders above everything
                 and visually clashes with our own results dropdown / eye icon). --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" name="fake-username" tabindex="-1" autocomplete="username">
                <input type="password" name="fake-password" tabindex="-1" autocomplete="new-password">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-dark">
                    Cari Pegawai <span class="text-red-500">*</span>
                </label>
                <div class="relative" id="pegawai-combobox">
                    <div class="flex items-center overflow-hidden rounded-lg border border-dark/20 focus-within:border-green focus-within:ring-1 focus-within:ring-green bg-white">
                        <span class="pl-3 flex items-center text-dark/30"><i class="fas fa-search text-xs leading-none"></i></span>
                        <input type="text" id="search-pegawai" name="search-pegawai-noautofill" autocomplete="off" data-lpignore="true" data-1p-ignore
                            placeholder="Ketik NIK atau nama untuk mencari..."
                            class="flex-1 min-w-0 px-3 py-2 text-sm outline-none bg-transparent">
                        <button type="button" id="pegawai-clear-btn" onclick="clearPegawai()" tabindex="-1"
                            title="Ganti pilihan"
                            class="hidden shrink-0 px-3 py-2 text-dark/30 hover:text-red-500 transition">
                            <i class="fas fa-times-circle text-sm leading-none"></i>
                        </button>
                    </div>
                    <div id="pegawai-results"
                        class="absolute left-0 right-0 z-30 mt-1 hidden max-h-56 overflow-y-auto rounded-lg border border-dark/10 bg-white shadow-xl"></div>
                </div>
                <p class="mt-1 text-xs text-dark/40">Ketik NIK atau nama untuk menampilkan pilihan</p>
            </div>

            <div id="pegawai-preview" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-emerald-600">Pegawai Dipilih</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
                    <div><span class="text-xs text-dark/40 block">NIK</span><span id="prev-nik" class="font-semibold text-dark"></span></div>
                    <div><span class="text-xs text-dark/40 block">Nama</span><span id="prev-nama" class="font-semibold text-dark"></span></div>
                    <div><span class="text-xs text-dark/40 block">Jabatan</span><span id="prev-jabatan" class="text-dark/70"></span></div>
                    <div><span class="text-xs text-dark/40 block">Bagian</span><span id="prev-bagian" class="text-dark/70"></span></div>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" id="tambah-username" required
                    placeholder="Username untuk login"
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" name="password" id="tambah-password" required minlength="6"
                        autocomplete="new-password" data-lpignore="true" data-1p-ignore
                        placeholder="Minimal 6 karakter"
                        class="w-full rounded-lg border border-dark/20 px-3 py-2 pr-10 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <button type="button" onclick="togglePwd('tambah-password', 'eye-tambah')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-dark/30 hover:text-dark/60 transition">
                        <i class="fas fa-eye text-sm leading-none" id="eye-tambah"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Role <span class="text-red-500">*</span></label>
                <select name="level" id="tambah-level" required
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <option value="">-- Pilih Role --</option>
                    <option value="2">General Manager</option>
                    <option value="3">Manager</option>
                    <option value="4">Direksi</option>
                </select>
            </div>

            <div id="tambah-gm-wrapper" class="hidden">
                <label class="mb-1 block text-sm font-medium text-dark">Di bawah GM</label>
                <select name="nik_gm"
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <option value="">-- Pilih GM --</option>
                    @foreach ($gms as $gm)
                        <option value="{{ $gm->NIK }}">{{ ucwords(strtolower($gm->nama ?? $gm->NIK)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="closeTambah()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">
                    <i class="fas fa-user-plus mr-1.5"></i> Simpan
                </button>
            </div>
        </form>
    </dialog>

    {{-- ================================================================
         MODAL: Edit User
    ================================================================ --}}
    <dialog id="modal-edit" class="w-full max-w-lg rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Edit User</h3>
            <button type="button" onclick="document.getElementById('modal-edit').close()" class="rounded p-1 text-dark/40 hover:text-dark">&times;</button>
        </div>
        <form id="form-edit" method="POST" class="space-y-4 p-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3 rounded-lg bg-dark/5 p-3 border border-dark/10">
                <div>
                    <p class="text-xs text-dark/40 mb-0.5">NIK</p>
                    <p id="edit-nik-display" class="font-semibold text-sm text-dark"></p>
                </div>
                <div>
                    <p class="text-xs text-dark/40 mb-0.5">Nama</p>
                    <p id="edit-nama-display" class="font-semibold text-sm text-dark"></p>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Username</label>
                <input type="text" name="username" id="edit-username" required
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Role</label>
                <select name="level" id="edit-level" required
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <option value="2">General Manager</option>
                    <option value="3">Manager</option>
                    <option value="4">Direksi</option>
                </select>
            </div>
            <div id="edit-gm-wrapper" class="hidden">
                <label class="mb-1 block text-sm font-medium text-dark">Di bawah GM</label>
                <select name="nik_gm" id="edit-nik-gm"
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <option value="">-- Tidak ada --</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Status Akun</label>
                <select name="status" id="edit-status" required
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-edit').close()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">
                    <i class="fas fa-save mr-1.5"></i> Simpan
                </button>
            </div>
        </form>
    </dialog>

    {{-- ================================================================
         MODAL: Reset Password
    ================================================================ --}}
    <dialog id="modal-reset" class="w-full max-w-md rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Reset Password</h3>
            <button type="button" onclick="document.getElementById('modal-reset').close()" class="rounded p-1 text-dark/40 hover:text-dark">&times;</button>
        </div>
        <form id="form-reset" method="POST" class="space-y-4 p-5">
            @csrf
            @method('PATCH')
            <div id="reset-label" class="flex items-start gap-2.5 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2.5 text-sm text-amber-700">
                <i class="fas fa-key mt-0.5 shrink-0"></i>
                <span id="reset-label-text"></span>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password" id="reset-password" required minlength="6"
                        placeholder="Minimal 6 karakter"
                        class="w-full rounded-lg border border-dark/20 px-3 py-2 pr-10 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                    <button type="button" onclick="togglePwd('reset-password', 'eye-reset')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-dark/30 hover:text-dark/60 transition">
                        <i class="fas fa-eye text-sm" id="eye-reset"></i>
                    </button>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-reset').close()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600">
                    <i class="fas fa-key mr-1.5"></i> Reset
                </button>
            </div>
        </form>
    </dialog>

    {{-- ================================================================
         MODAL: Detail User
    ================================================================ --}}
    <dialog id="modal-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail User</h3>
            <button type="button" onclick="document.getElementById('modal-detail').close()" class="rounded p-1 text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="detail-body" class="max-h-[78vh] overflow-y-auto p-5">
            <div class="flex items-center justify-center py-10 text-dark/30">
                <i class="fas fa-spinner fa-spin mr-2"></i> Memuat...
            </div>
        </div>
    </dialog>

    {{-- ================================================================
         MODAL: Hapus Konfirmasi
    ================================================================ --}}
    <dialog id="modal-hapus" class="w-full max-w-sm rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="p-6 text-center space-y-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 mx-auto">
                <i class="fas fa-trash-alt text-red-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-dark text-base">Hapus User?</p>
                <p id="hapus-label" class="mt-1.5 text-sm text-dark/60 leading-relaxed"></p>
            </div>
            <form id="form-hapus" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-3 mt-2">
                    <button type="button" onclick="document.getElementById('modal-hapus').close()"
                        class="rounded-lg border border-dark/20 px-5 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                        <i class="fas fa-trash mr-1.5"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
<script>
const SEARCH_PEGAWAI_URL = "{{ route('users.search-pegawai') }}";
const DETAIL_URL         = "{{ url('users') }}";
const ROLE_LABELS        = {1:'Superadmin', 2:'General Manager', 3:'Manager', 4:'Direksi'};
const RL_COLOR           = {
    1: 'bg-gray-100 text-gray-700',
    2: 'bg-blue-100 text-blue-700',
    3: 'bg-amber-100 text-amber-700',
    4: 'bg-purple-100 text-purple-700',
};

// ── Toggle Password Visibility ──────────────────────────────────────────────
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

// ── Modal Tambah ────────────────────────────────────────────────────────────
let dropdownOpen = false;

function openTambah() {
    document.getElementById('modal-tambah').showModal();
}

function closeTambah() {
    document.getElementById('modal-tambah').close();
    resetCombobox();
    document.getElementById('tambah-nik').value = '';
    document.getElementById('tambah-username').value = '';
    document.getElementById('tambah-level').value = '';
    document.getElementById('tambah-password').value = '';
    document.getElementById('tambah-password').type = 'password';
    document.getElementById('eye-tambah').className = 'fas fa-eye text-sm';
    document.getElementById('pegawai-preview').classList.add('hidden');
    document.getElementById('tambah-gm-wrapper').classList.add('hidden');
}

// ── Combobox helpers ────────────────────────────────────────────────────────
function resetCombobox() {
    const inp = document.getElementById('search-pegawai');
    inp.value = '';
    inp.readOnly = false;
    inp.classList.remove('text-dark', 'font-medium');
    document.getElementById('pegawai-clear-btn').classList.add('hidden');
    closeDropdown();
}

function openDropdown() {
    document.getElementById('pegawai-results').classList.remove('hidden');
    dropdownOpen = true;
}

function closeDropdown() {
    document.getElementById('pegawai-results').classList.add('hidden');
    dropdownOpen = false;
}

function renderPegawaiList(data) {
    const box = document.getElementById('pegawai-results');
    box.innerHTML = '';
    if (!data.length) {
        box.innerHTML = '<p class="px-4 py-3 text-xs text-dark/40 italic">Tidak ditemukan atau sudah memiliki akun</p>';
        openDropdown();
        return;
    }
    data.forEach(p => {
        const el = document.createElement('button');
        el.type = 'button';
        el.className = 'block w-full text-left px-3 py-2.5 text-sm hover:bg-emerald-50 border-b border-dark/5 last:border-0 transition';
        el.innerHTML = `<span class="font-semibold text-dark">${p.Nik}</span>`
            + `<span class="mx-1.5 text-dark/30">—</span>`
            + `<span class="text-dark/80">${p.nama}</span>`
            + (p.jabatan ? `<br><span class="text-xs text-dark/40">${p.jabatan}${p.BAGIAN ? ' · ' + p.BAGIAN : ''}</span>` : '');
        el.addEventListener('click', () => selectPegawai(p));
        box.appendChild(el);
    });
    openDropdown();
}

let pegawaiTimer;
document.getElementById('search-pegawai').addEventListener('input', function () {
    if (this.readOnly) return;
    clearTimeout(pegawaiTimer);
    const q = this.value.trim();
    if (!q) { closeDropdown(); return; }
    pegawaiTimer = setTimeout(() => {
        fetch(SEARCH_PEGAWAI_URL + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(renderPegawaiList);
    }, 250);
});

document.addEventListener('click', function (e) {
    const cb = document.getElementById('pegawai-combobox');
    if (cb && !cb.contains(e.target)) closeDropdown();
});

function selectPegawai(p) {
    document.getElementById('tambah-nik').value = p.Nik;
    const inp = document.getElementById('search-pegawai');
    inp.value    = p.Nik + '  —  ' + p.nama;
    inp.readOnly = true;
    inp.classList.add('text-dark', 'font-medium');
    document.getElementById('pegawai-clear-btn').classList.remove('hidden');
    document.getElementById('prev-nik').textContent     = p.Nik;
    document.getElementById('prev-nama').textContent    = p.nama;
    document.getElementById('prev-jabatan').textContent = p.jabatan || '-';
    document.getElementById('prev-bagian').textContent  = p.BAGIAN  || '-';
    document.getElementById('pegawai-preview').classList.remove('hidden');
    if (!document.getElementById('tambah-username').value) {
        document.getElementById('tambah-username').value = p.Nik.replace(/\D/g, '').toLowerCase();
    }
    closeDropdown();
}

function clearPegawai() {
    document.getElementById('tambah-nik').value = '';
    document.getElementById('tambah-username').value = '';
    document.getElementById('pegawai-preview').classList.add('hidden');
    resetCombobox();
    document.getElementById('search-pegawai').focus();
}

document.getElementById('tambah-level').addEventListener('change', function () {
    document.getElementById('tambah-gm-wrapper').classList.toggle('hidden', this.value !== '3');
});

// ── Edit ───────────────────────────────────────────────────────────────────
function openEdit(user, gms) {
    const nameTitle = n => n ? n.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : '-';
    document.getElementById('edit-nik-display').textContent  = user.NIK;
    document.getElementById('edit-nama-display').textContent = nameTitle(user.nama);
    document.getElementById('edit-username').value = user.USERNAME;
    document.getElementById('edit-level').value    = user.ID_LEVEL;
    document.getElementById('edit-status').value   = user.data_aktif;

    const gmSel = document.getElementById('edit-nik-gm');
    gmSel.innerHTML = '<option value="">-- Tidak ada --</option>';
    gms.forEach(g => {
        const opt = document.createElement('option');
        opt.value = g.NIK;
        opt.textContent = nameTitle(g.nama) || g.NIK;
        gmSel.appendChild(opt);
    });

    const isManager = parseInt(user.ID_LEVEL) === 3;
    document.getElementById('edit-gm-wrapper').classList.toggle('hidden', !isManager);
    if (isManager) {
        fetch(DETAIL_URL + '/' + user.NIK + '/detail').then(r => r.json()).then(d => {
            if (d.gm) gmSel.value = d.gm.NIK_GM;
        });
    }

    document.getElementById('form-edit').action = DETAIL_URL + '/' + user.NIK;
    document.getElementById('modal-edit').showModal();
}

document.getElementById('edit-level').addEventListener('change', function () {
    document.getElementById('edit-gm-wrapper').classList.toggle('hidden', this.value !== '3');
});

// ── Reset Password ─────────────────────────────────────────────────────────
function openReset(nik, nama) {
    document.getElementById('reset-label-text').textContent = 'Reset password untuk: ' + nama;
    document.getElementById('reset-password').value = '';
    document.getElementById('reset-password').type  = 'password';
    document.getElementById('eye-reset').className  = 'fas fa-eye text-sm';
    document.getElementById('form-reset').action    = DETAIL_URL + '/' + nik + '/reset-password';
    document.getElementById('modal-reset').showModal();
}

// ── Detail ─────────────────────────────────────────────────────────────────
function openDetail(nik) {
    document.getElementById('detail-body').innerHTML = `
        <div class="flex items-center justify-center py-10 text-dark/30">
            <i class="fas fa-spinner fa-spin mr-2"></i> Memuat...
        </div>`;
    document.getElementById('modal-detail').showModal();

    fetch(DETAIL_URL + '/' + nik + '/detail')
        .then(r => r.json())
        .then(d => {
            const u = d.user;
            if (!u) {
                document.getElementById('detail-body').innerHTML =
                    '<p class="py-6 text-center text-red-500 text-sm">Data tidak ditemukan.</p>';
                return;
            }
            const rl        = ROLE_LABELS[u.ID_LEVEL] || '-';
            const rlCls     = RL_COLOR[u.ID_LEVEL]    || 'bg-gray-100 text-gray-600';
            const nameTitle = n => n ? n.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : '-';
            const fmtDate   = s => s ? new Date(s).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}) : '-';
            const fmtDT     = s => s ? new Date(s).toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-';

            const avatar = (u.nama || u.USERNAME || '?').charAt(0).toUpperCase();
            const statusBadge = u.data_aktif === 'Aktif'
                ? `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif</span>`
                : `<span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Non-Aktif</span>`;

            let html = `
            <div class="flex items-start gap-4 rounded-xl bg-dark/5 p-4 mb-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-green text-2xl font-black text-white shadow">
                    ${avatar}
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-dark leading-tight">${nameTitle(u.nama)}</p>
                    <p class="text-sm text-dark/50 mt-0.5">${u.jabatan || ''}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ${rlCls}">${rl}</span>
                        ${statusBadge}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5">

                <div>
                    <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-dark/40">
                        <span class="h-px flex-1 bg-dark/10"></span>
                        <i class="fas fa-user-shield"></i> Informasi Akun
                        <span class="h-px flex-1 bg-dark/10"></span>
                    </p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        ${dtRow('NIK', u.NIK)}
                        ${dtRow('Username', u.USERNAME)}
                        ${dtRow('Role', `<span class="rounded-full px-2 py-0.5 text-xs font-semibold ${rlCls}">${rl}</span>`)}
                        ${dtRow('Status Akun', statusBadge)}
                        ${dtRow('Tgl. Dibuat', fmtDT(u.DATE_CREATE))}
                        ${dtRow('Login Terakhir', fmtDT(u.LAST_LOGIN))}
                    </div>
                </div>

                <div>
                    <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-dark/40">
                        <span class="h-px flex-1 bg-dark/10"></span>
                        <i class="fas fa-id-card"></i> Data Pegawai
                        <span class="h-px flex-1 bg-dark/10"></span>
                    </p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        ${dtRow('Nama Lengkap', nameTitle(u.nama))}
                        ${dtRow('Jabatan', nameTitle(u.jabatan))}
                        ${dtRow('Bagian', u.BAGIAN || '-')}
                        ${dtRow('Divisi', u.DIVISI || '-')}
                        ${dtRow('Unit Kerja', u.UNIT_KERJA || '-')}
                        ${dtRow('Wilayah', u.WILAYAH || '-')}
                        ${dtRow('Golongan', u.GOL || '-')}
                        ${dtRow('Jenis Kelamin', u.jenis_kelamin || '-')}
                        ${dtRow('Jenis Pegawai', u.jenis_pegawai || '-')}
                        ${dtRow('Status Nikah', u.STATUS_NIKAH || '-')}
                        ${dtRow('Jumlah Anak', u.JUMLAH_ANAK != null ? u.JUMLAH_ANAK : '-')}
                        ${dtRow('Pendidikan', u.NM_PENDIDIKAN || '-')}
                        ${dtRow('Tempat Lahir', u.TEMPAT_LAHIR || '-')}
                        ${dtRow('Tgl. Lahir', fmtDate(u.TGL_LAHIR))}
                        ${dtRow('Status SDM', u.sdm_aktif || '-')}
                    </div>
                </div>`;

            if (u.PHONE || u.EMAIL) {
                html += `
                <div>
                    <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-dark/40">
                        <span class="h-px flex-1 bg-dark/10"></span>
                        <i class="fas fa-address-book"></i> Kontak
                        <span class="h-px flex-1 bg-dark/10"></span>
                    </p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        ${dtRow('Telepon', u.PHONE || '-')}
                        ${dtRow('Email', u.EMAIL || '-')}
                    </div>
                </div>`;
            }

            if (d.gm || (d.managers && d.managers.length)) {
                html += `
                <div>
                    <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-dark/40">
                        <span class="h-px flex-1 bg-dark/10"></span>
                        <i class="fas fa-sitemap"></i> Hierarki
                        <span class="h-px flex-1 bg-dark/10"></span>
                    </p>`;
                if (d.gm) {
                    html += `<div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">${dtRow('Di bawah GM', nameTitle(d.gm.nama))}</div>`;
                }
                if (d.managers && d.managers.length) {
                    html += `<p class="mt-3 mb-1.5 text-xs text-dark/40 font-medium">Manager di bawah GM ini:</p>
                    <ul class="space-y-1.5">
                        ${d.managers.map(m => `
                        <li class="flex items-center gap-2 rounded-lg bg-dark/3 px-3 py-2 text-sm">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">
                                ${(m.nama || m.USERNAME || '?').charAt(0).toUpperCase()}
                            </span>
                            <span class="font-medium text-dark">${nameTitle(m.nama)}</span>
                            <span class="text-xs text-dark/40">(${m.USERNAME})</span>
                            ${m.data_aktif === 'Aktif'
                                ? '<span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Aktif</span>'
                                : '<span class="ml-auto rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Non-Aktif</span>'}
                        </li>`).join('')}
                    </ul>`;
                }
                html += '</div>';
            }

            html += '</div>';
            document.getElementById('detail-body').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('detail-body').innerHTML =
                '<p class="py-6 text-center text-red-500 text-sm">Gagal memuat data. Coba lagi.</p>';
        });
}

function dtRow(label, value) {
    return `<div>
        <p class="text-xs text-dark/40 mb-0.5">${label}</p>
        <p class="font-medium text-dark text-sm leading-snug">${value ?? '-'}</p>
    </div>`;
}

// ── Hapus ──────────────────────────────────────────────────────────────────
function openDelete(nik, nama) {
    document.getElementById('hapus-label').textContent = 'User "' + nama + '" (NIK: ' + nik + ') akan dihapus secara permanen.';
    document.getElementById('form-hapus').action = DETAIL_URL + '/' + nik;
    document.getElementById('modal-hapus').showModal();
}
</script>
@endpush

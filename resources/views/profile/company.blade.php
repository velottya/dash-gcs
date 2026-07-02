@extends('layouts.app')

@section('title', 'Profil Perusahaan')

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green/10 px-4 py-3 text-sm text-green">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">

        {{-- Latar belakang --}}
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-heading flex items-center gap-2 text-base font-extrabold text-dark">
                    <i class="fas fa-money-check text-green"></i> Latar Belakang Perusahaan
                </h2>
                <button type="button" onclick="document.getElementById('modal-edit-company').showModal()"
                    class="rounded-lg border border-dark/20 px-3 py-1.5 text-xs font-medium text-dark transition hover:bg-dark/5">
                    <i class="fas fa-pencil-alt mr-1"></i> Edit
                </button>
            </div>
            <p class="text-sm leading-relaxed text-dark/70">{{ $company['background'] }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Dewan Komisaris & Direksi --}}
            <div class="space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="font-heading mb-3 flex items-center gap-2 text-sm font-extrabold text-dark">
                        <i class="fas fa-users text-green"></i> Dewan Komisaris
                    </h3>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-dark/5">
                            @foreach ($company['komisaris'] as $row)
                                <tr>
                                    <td class="py-2 text-dark/60">{{ $row['jabatan'] }}</td>
                                    <td class="py-2 font-medium text-dark">{{ $row['nama'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="font-heading mb-3 flex items-center gap-2 text-sm font-extrabold text-dark">
                        <i class="fas fa-users text-green"></i> Dewan Direksi
                    </h3>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-dark/5">
                            @foreach ($company['direksi'] as $row)
                                <tr>
                                    <td class="py-2 text-dark/60">{{ $row['jabatan'] }}</td>
                                    <td class="py-2 font-medium text-dark">{{ $row['nama'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pemegang Saham --}}
            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-dark/10 px-6 py-4">
                    <h3 class="font-heading flex items-center gap-2 text-sm font-extrabold text-dark">
                        <i class="fas fa-share text-green"></i> Pemegang Saham
                    </h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5">
                        @foreach ($company['pemegang_saham'] as $row)
                            <tr>
                                <td class="px-6 py-3">{{ $row['nama'] }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full bg-green/10 px-2 py-0.5 text-xs font-semibold text-green">
                                        {{ $row['persentase'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Alamat --}}
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="font-heading mb-3 flex items-center gap-2 text-sm font-extrabold text-dark">
                    <i class="fas fa-map-marker-alt text-green"></i> Alamat Kantor Pusat
                </h3>
                <div class="space-y-1.5 text-sm text-dark/70">
                    <p>{{ $company['alamat'] }}</p>
                    <p>Phone : {{ $company['phone'] }}</p>
                    <p>Fax : {{ $company['fax'] }}</p>
                    <p>Email : {{ $company['email'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Edit modal ─────────────────────────────────────────────────────── --}}
    <dialog id="modal-edit-company" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Edit Profil Perusahaan</h3>
            <button type="button" onclick="document.getElementById('modal-edit-company').close()"
                class="text-dark/40 hover:text-dark">&times;</button>
        </div>

        <form method="POST" action="{{ route('profile.company.update') }}"
            class="max-h-[75vh] overflow-y-auto p-5">
            @csrf

            {{-- Latar belakang --}}
            <div class="mb-5">
                <label class="mb-1 block text-sm font-semibold text-dark">Latar Belakang Perusahaan</label>
                <textarea name="background" rows="4"
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">{{ $company['background'] }}</textarea>
            </div>

            {{-- Komisaris --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-semibold text-dark">Dewan Komisaris</label>
                <div id="komisaris-rows" class="space-y-2">
                    @foreach ($company['komisaris'] as $i => $row)
                        <div class="flex gap-2">
                            <input type="text" name="komisaris_jabatan[]" value="{{ $row['jabatan'] }}"
                                placeholder="Jabatan"
                                class="w-1/2 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                            <input type="text" name="komisaris_nama[]" value="{{ $row['nama'] }}"
                                placeholder="Nama"
                                class="w-1/2 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addRow('komisaris-rows', 'komisaris_jabatan', 'komisaris_nama')"
                    class="mt-2 text-xs text-green hover:underline"><i class="fas fa-plus mr-1"></i>Tambah baris</button>
            </div>

            {{-- Direksi --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-semibold text-dark">Dewan Direksi</label>
                <div id="direksi-rows" class="space-y-2">
                    @foreach ($company['direksi'] as $i => $row)
                        <div class="flex gap-2">
                            <input type="text" name="direksi_jabatan[]" value="{{ $row['jabatan'] }}"
                                placeholder="Jabatan"
                                class="w-1/2 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                            <input type="text" name="direksi_nama[]" value="{{ $row['nama'] }}"
                                placeholder="Nama"
                                class="w-1/2 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addRow('direksi-rows', 'direksi_jabatan', 'direksi_nama')"
                    class="mt-2 text-xs text-green hover:underline"><i class="fas fa-plus mr-1"></i>Tambah baris</button>
            </div>

            {{-- Pemegang Saham --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-semibold text-dark">Pemegang Saham</label>
                <div id="saham-rows" class="space-y-2">
                    @foreach ($company['pemegang_saham'] as $row)
                        <div class="flex gap-2">
                            <input type="text" name="saham_nama[]" value="{{ $row['nama'] }}"
                                placeholder="Nama perusahaan"
                                class="w-2/3 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                            <input type="text" name="saham_pct[]" value="{{ $row['persentase'] }}"
                                placeholder="Persentase"
                                class="w-1/3 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addSahamRow()"
                    class="mt-2 text-xs text-green hover:underline"><i class="fas fa-plus mr-1"></i>Tambah baris</button>
            </div>

            {{-- Kontak --}}
            <div class="mb-5 space-y-3">
                <label class="block text-sm font-semibold text-dark">Kontak</label>
                <div>
                    <label class="mb-1 block text-xs text-dark/50">Alamat</label>
                    <input type="text" name="alamat" value="{{ $company['alamat'] }}"
                        class="w-full rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs text-dark/50">Telepon</label>
                        <input type="text" name="phone" value="{{ $company['phone'] }}"
                            class="w-full rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-dark/50">Fax</label>
                        <input type="text" name="fax" value="{{ $company['fax'] }}"
                            class="w-full rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-dark/50">Email</label>
                    <input type="email" name="email" value="{{ $company['email'] }}"
                        class="w-full rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-dark/10 pt-4">
                <button type="button" onclick="document.getElementById('modal-edit-company').close()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">Simpan</button>
            </div>
        </form>
    </dialog>

    <script>
        function addRow(containerId, name1, name2) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `<input type="text" name="${name1}[]" placeholder="Jabatan" class="w-1/2 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                <input type="text" name="${name2}[]" placeholder="Nama" class="w-1/2 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">`;
            container.appendChild(div);
        }

        function addSahamRow() {
            const container = document.getElementById('saham-rows');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `<input type="text" name="saham_nama[]" placeholder="Nama perusahaan" class="w-2/3 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">
                <input type="text" name="saham_pct[]" placeholder="Persentase" class="w-1/3 rounded-lg border border-dark/20 px-3 py-1.5 text-sm focus:border-green focus:outline-none">`;
            container.appendChild(div);
        }
    </script>
@endsection

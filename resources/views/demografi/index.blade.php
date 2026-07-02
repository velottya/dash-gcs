@extends('layouts.app')

@section('title', 'Demografi Karyawan')
@section('subtitle', 'Total karyawan aktif: ' . number_format($total, 0, ',', '.'))

@php
    $pct = fn ($value, $base) => $base > 0 ? number_format(($value / $base) * 100, 2, ',', '.') : '0,00';
@endphp

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Status --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between rounded-t-xl bg-gold px-5 py-3">
                <h3 class="font-heading text-sm font-extrabold text-dark">Komposisi Berdasarkan Status</h3>
                <button type="button" onclick="openDetailStatus()" class="text-xs font-semibold text-dark/70 hover:underline">Detail</button>
            </div>
            <div class="p-5">
                <canvas id="chart-status" height="120"></canvas>
                <x-insight-box :items="$insightStatus" />
                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="text-center text-xs uppercase text-dark/40">
                            <th class="py-2">Status</th><th class="py-2">Jumlah</th><th class="py-2">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5 text-center">
                        @php($jumlah = 0)
                        @foreach ($statusKaryawan as $row)
                            <tr>
                                <td class="py-1.5 text-left">{{ $row->JENIS_PEGAWAI }}</td>
                                <td class="py-1.5">{{ $row->TOTAL }}</td>
                                <td class="py-1.5">{{ $pct($row->TOTAL, $total) }}</td>
                            </tr>
                            @php($jumlah += $row->TOTAL)
                        @endforeach
                        <tr class="bg-dark font-semibold text-white"><td class="py-1.5 text-left">JUMLAH</td><td class="py-1.5">{{ $jumlah }}</td><td class="py-1.5"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Usia --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between rounded-t-xl bg-gold px-5 py-3">
                <h3 class="font-heading text-sm font-extrabold text-dark">Komposisi Karyawan Berdasarkan Usia</h3>
                <button type="button" onclick="openDetailUsia()" class="text-xs font-semibold text-dark/70 hover:underline">Detail</button>
            </div>
            <div class="p-5">
                <canvas id="chart-usia" height="150"></canvas>
                <x-insight-box :items="$insightUsia" />
                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="text-center text-xs uppercase text-dark/40">
                            <th class="py-2">Usia</th><th class="py-2">Tetap</th><th class="py-2">%</th><th class="py-2">Kontrak</th><th class="py-2">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5 text-center">
                        @php($jumlahTetap = 0) @php($jumlahKontrak = 0)
                        @foreach ($usia as $row)
                            <tr>
                                <td class="py-1.5 text-left">{{ $row->KATEGORI_USIA }}</td>
                                <td class="py-1.5">{{ $row->TETAP }}</td>
                                <td class="py-1.5">{{ $pct($row->TETAP, $totTetap) }}</td>
                                <td class="py-1.5">{{ $row->KONTRAK }}</td>
                                <td class="py-1.5">{{ $pct($row->KONTRAK, $totKontrak) }}</td>
                            </tr>
                            @php($jumlahTetap += $row->TETAP) @php($jumlahKontrak += $row->KONTRAK)
                        @endforeach
                        <tr class="bg-dark font-semibold text-white"><td class="py-1.5 text-left">JUMLAH</td><td class="py-1.5">{{ $jumlahTetap }}</td><td></td><td class="py-1.5">{{ $jumlahKontrak }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kelamin --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between rounded-t-xl bg-gold px-5 py-3">
                <h3 class="font-heading text-sm font-extrabold text-dark">Komposisi Karyawan Berdasarkan Kelamin</h3>
                <button type="button" onclick="openDetailKelamin()" class="text-xs font-semibold text-dark/70 hover:underline">Detail</button>
            </div>
            <div class="p-5">
                <canvas id="chart-kelamin" height="120"></canvas>
                <x-insight-box :items="$insightKelamin" />
                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="text-center text-xs uppercase text-dark/40">
                            <th class="py-2">Kelamin</th><th class="py-2">Tetap</th><th class="py-2">%</th><th class="py-2">Mitra</th><th class="py-2">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5 text-center">
                        @php($jumlahTetap = 0) @php($jumlahKontrak = 0)
                        @foreach ($kelamin as $row)
                            <tr>
                                <td class="py-1.5 text-left">{{ $row->JENIS_KELAMIN }}</td>
                                <td class="py-1.5">{{ $row->TETAP }}</td>
                                <td class="py-1.5">{{ $pct($row->TETAP, $total) }}</td>
                                <td class="py-1.5">{{ $row->KONTRAK }}</td>
                                <td class="py-1.5">{{ $pct($row->KONTRAK, $total) }}</td>
                            </tr>
                            @php($jumlahTetap += $row->TETAP) @php($jumlahKontrak += $row->KONTRAK)
                        @endforeach
                        <tr class="bg-dark font-semibold text-white"><td class="py-1.5 text-left">JUMLAH</td><td class="py-1.5">{{ $jumlahTetap }}</td><td></td><td class="py-1.5">{{ $jumlahKontrak }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Wilayah --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between rounded-t-xl bg-gold px-5 py-3">
                <h3 class="font-heading text-sm font-extrabold text-dark">Komposisi Karyawan Berdasarkan Wilayah</h3>
                <button type="button" onclick="openDetailWilayah()" class="text-xs font-semibold text-dark/70 hover:underline">Detail</button>
            </div>
            <div class="p-5">
                <canvas id="chart-wilayah" height="150"></canvas>
                <x-insight-box :items="$insightWilayah" />
                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="text-center text-xs uppercase text-dark/40">
                            <th class="py-2">Wilayah</th><th class="py-2">Tetap</th><th class="py-2">%</th><th class="py-2">Mitra</th><th class="py-2">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5 text-center">
                        @php($jumlahTetap = 0) @php($jumlahKontrak = 0)
                        @foreach ($wilayah as $row)
                            <tr>
                                <td class="py-1.5 text-left">{{ $row->WILAYAH }}</td>
                                <td class="py-1.5">{{ $row->TETAP }}</td>
                                <td class="py-1.5">{{ $pct($row->TETAP, $totTetap) }}</td>
                                <td class="py-1.5">{{ $row->KONTRAK }}</td>
                                <td class="py-1.5">{{ $pct($row->KONTRAK, $totKontrak) }}</td>
                            </tr>
                            @php($jumlahTetap += $row->TETAP) @php($jumlahKontrak += $row->KONTRAK)
                        @endforeach
                        <tr class="bg-dark font-semibold text-white"><td class="py-1.5 text-left">JUMLAH</td><td class="py-1.5">{{ $jumlahTetap }}</td><td></td><td class="py-1.5">{{ $jumlahKontrak }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pendidikan --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between rounded-t-xl bg-gold px-5 py-3">
                <h3 class="font-heading text-sm font-extrabold text-dark">Komposisi Karyawan Berdasarkan Pendidikan</h3>
                <button type="button" onclick="openDetailPendidikan()" class="text-xs font-semibold text-dark/70 hover:underline">Detail</button>
            </div>
            <div class="p-5">
                <canvas id="chart-pendidikan" height="150"></canvas>
                <x-insight-box :items="$insightPendidikan" />
                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="text-center text-xs uppercase text-dark/40">
                            <th class="py-2">Pendidikan</th><th class="py-2">Tetap</th><th class="py-2">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5 text-center">
                        @php($jumlah = 0)
                        @foreach ($pendidikan as $row)
                            <tr>
                                <td class="py-1.5 text-left">{{ $row->NM_PENDIDIKAN }}</td>
                                <td class="py-1.5">{{ $row->TETAP }}</td>
                                <td class="py-1.5">{{ $pct($row->TETAP, $total) }}</td>
                            </tr>
                            @php($jumlah += $row->TETAP)
                        @endforeach
                        <tr class="bg-dark font-semibold text-white"><td class="py-1.5 text-left">JUMLAH</td><td class="py-1.5">{{ $jumlah }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Jabatan --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between rounded-t-xl bg-gold px-5 py-3">
                <h3 class="font-heading text-sm font-extrabold text-dark">Komposisi Karyawan Berdasarkan Jabatan</h3>
                <button type="button" onclick="openDetailJabatan()" class="text-xs font-semibold text-dark/70 hover:underline">Detail</button>
            </div>
            <div class="p-5">
                <canvas id="chart-jabatan" height="150"></canvas>
                <x-insight-box :items="$insightJabatan" />
                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr class="text-center text-xs uppercase text-dark/40">
                            <th class="py-2">Jabatan</th><th class="py-2">Tetap</th><th class="py-2">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5 text-center">
                        @php($jumlah = 0)
                        @foreach ($jabatan as $row)
                            <tr>
                                <td class="py-1.5 text-left">{{ $row->NM_JABATAN }}</td>
                                <td class="py-1.5">{{ $row->TETAP }}</td>
                                <td class="py-1.5">{{ $pct($row->TETAP, $total) }}</td>
                            </tr>
                            @php($jumlah += $row->TETAP)
                        @endforeach
                        <tr class="bg-dark font-semibold text-white"><td class="py-1.5 text-left">JUMLAH</td><td class="py-1.5">{{ $jumlah }}</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: detail komposisi (shared, diisi client-side dari data yang sudah dimuat) --}}
    <dialog id="modal-demografi-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 id="modal-demografi-detail-title" class="font-heading font-extrabold text-dark">Detail</h3>
            <button type="button" onclick="document.getElementById('modal-demografi-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-demografi-detail-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>
@endsection


@push('scripts')
    <script>
        const statusKaryawanData = @json($statusKaryawan);
        const usiaData = @json($usia);
        const kelaminData = @json($kelamin);
        const wilayahData = @json($wilayah);
        const pendidikanData = @json($pendidikan);
        const jabatanData = @json($jabatan);

        function showDemografiDetail(title, head, bodyRows) {
            const modal = document.getElementById('modal-demografi-detail');
            document.getElementById('modal-demografi-detail-title').textContent = title;
            modal.showModal();
            document.getElementById('modal-demografi-detail-body').innerHTML =
                `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">${head}</tr></thead><tbody class="divide-y divide-dark/5">${bodyRows}</tbody></table>`;
        }

        function openDetailStatus() {
            const rows = statusKaryawanData.map(r => `<tr><td class="py-1.5">${r.JENIS_PEGAWAI}</td><td class="py-1.5 text-right">${r.TOTAL}</td></tr>`).join('');
            showDemografiDetail('Detail Status Karyawan', '<th class="py-1">Status</th><th class="py-1 text-right">Jumlah</th>', rows);
        }

        function openDetailUsia() {
            const rows = usiaData.map(r => `<tr><td class="py-1.5">${r.KATEGORI_USIA}</td><td class="py-1.5 text-right">${r.TETAP}</td><td class="py-1.5 text-right">${r.KONTRAK}</td></tr>`).join('');
            showDemografiDetail('Detail Usia Karyawan', '<th class="py-1">Usia</th><th class="py-1 text-right">Tetap</th><th class="py-1 text-right">Kontrak</th>', rows);
        }

        function openDetailKelamin() {
            const rows = kelaminData.map(r => `<tr><td class="py-1.5">${r.JENIS_KELAMIN}</td><td class="py-1.5 text-right">${r.TETAP}</td><td class="py-1.5 text-right">${r.KONTRAK}</td></tr>`).join('');
            showDemografiDetail('Detail Kelamin Karyawan', '<th class="py-1">Kelamin</th><th class="py-1 text-right">Tetap</th><th class="py-1 text-right">Kontrak</th>', rows);
        }

        function openDetailWilayah() {
            const rows = wilayahData.map(r => `<tr><td class="py-1.5">${r.WILAYAH}</td><td class="py-1.5 text-right">${r.TETAP}</td><td class="py-1.5 text-right">${r.KONTRAK}</td></tr>`).join('');
            showDemografiDetail('Detail Wilayah Karyawan', '<th class="py-1">Wilayah</th><th class="py-1 text-right">Tetap</th><th class="py-1 text-right">Kontrak</th>', rows);
        }

        function openDetailPendidikan() {
            const rows = pendidikanData.map(r => `<tr><td class="py-1.5">${r.NM_PENDIDIKAN}</td><td class="py-1.5 text-right">${r.TETAP}</td></tr>`).join('');
            showDemografiDetail('Detail Pendidikan Karyawan', '<th class="py-1">Pendidikan</th><th class="py-1 text-right">Tetap</th>', rows);
        }

        function openDetailJabatan() {
            const rows = jabatanData.map(r => `<tr><td class="py-1.5">${r.NM_JABATAN}</td><td class="py-1.5 text-right">${r.TETAP}</td></tr>`).join('');
            showDemografiDetail('Detail Jabatan Karyawan', '<th class="py-1">Jabatan</th><th class="py-1 text-right">Tetap</th>', rows);
        }

        function barChart(id, labels, datasets) {
            new Chart(document.getElementById(id), {
                type: 'bar',
                data: { labels, datasets },
                options: { responsive: true, plugins: { legend: { display: datasets.length > 1 } } },
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            barChart('chart-status', @json(array_map(fn ($r) => $r->JENIS_PEGAWAI, $statusKaryawan)), [
                { label: 'Jumlah', data: @json(array_map(fn ($r) => $r->TOTAL, $statusKaryawan)), backgroundColor: '#2F6C3F' },
            ]);

            barChart('chart-usia', @json(array_map(fn ($r) => $r->KATEGORI_USIA, $usia)), [
                { label: 'Tetap', data: @json(array_map(fn ($r) => $r->TETAP, $usia)), backgroundColor: '#2F6C3F' },
                { label: 'Kontrak', data: @json(array_map(fn ($r) => $r->KONTRAK, $usia)), backgroundColor: '#DAA628' },
            ]);

            barChart('chart-kelamin', @json(array_map(fn ($r) => $r->JENIS_KELAMIN, $kelamin)), [
                { label: 'Tetap', data: @json(array_map(fn ($r) => $r->TETAP, $kelamin)), backgroundColor: '#2F6C3F' },
                { label: 'Kontrak', data: @json(array_map(fn ($r) => $r->KONTRAK, $kelamin)), backgroundColor: '#DAA628' },
            ]);

            barChart('chart-wilayah', @json(array_map(fn ($r) => $r->WILAYAH, $wilayah)), [
                { label: 'Tetap', data: @json(array_map(fn ($r) => $r->TETAP, $wilayah)), backgroundColor: '#2F6C3F' },
                { label: 'Kontrak', data: @json(array_map(fn ($r) => $r->KONTRAK, $wilayah)), backgroundColor: '#DAA628' },
            ]);

            barChart('chart-pendidikan', @json(array_map(fn ($r) => $r->NM_PENDIDIKAN, $pendidikan)), [
                { label: 'Tetap', data: @json(array_map(fn ($r) => $r->TETAP, $pendidikan)), backgroundColor: '#2F6C3F' },
            ]);

            barChart('chart-jabatan', @json(array_map(fn ($r) => $r->NM_JABATAN, $jabatan)), [
                { label: 'Tetap', data: @json(array_map(fn ($r) => $r->TETAP, $jabatan)), backgroundColor: '#2F6C3F' },
            ]);
        });
    </script>
@endpush

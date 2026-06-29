@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan kinerja PT Gresik Cipta Sejahtera')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Profil & penjualan hari ini --}}
        <div class="space-y-6">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="px-5 pt-5 pb-5">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gold text-2xl font-black text-dark shadow">
                        {{ strtoupper(substr($pegawai->NAMA ?? '?', 0, 1)) }}
                    </div>
                    <h3 class="font-heading mt-3 text-base font-extrabold text-dark">{{ $pegawai->NAMA ?? '-' }}</h3>
                    <p class="text-sm text-dark/50">{{ $pegawai->NM_JABATAN ?? '-' }}</p>

                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-dark/10 pt-4 text-sm">
                        <div>
                            <dt class="text-xs uppercase text-dark/40">NIK</dt>
                            <dd class="font-semibold text-dark">{{ $pegawai->NIK ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-dark/40">Login Terakhir</dt>
                            <dd class="font-semibold text-dark">{{ $lastLogin ? \Illuminate\Support\Carbon::parse($lastLogin)->format('d/m/Y H:i') : '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-dark/40">Penjualan Hari Ini · {{ $kpi['periode_label'] }}</p>
                <button
                    type="button"
                    onclick="openPenjualanHariIni()"
                    class="font-heading mt-2 w-full rounded-lg bg-dark/5 px-4 py-3 text-left text-xl font-extrabold text-dark transition hover:bg-gold/20">
                    Rp {{ number_format($penjualanHariIniTotal, 2, ',', '.') }}
                </button>
            </div>
        </div>

        {{-- Piutang jatuh tempo --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
                <h3 class="font-heading flex items-center gap-2 text-sm font-extrabold text-dark">
                    <i class="fas fa-exclamation-circle text-red-500"></i> Piutang Jatuh Tempo
                </h3>
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Dalam Ribu</span>
            </div>
            <div class="max-h-80 divide-y divide-dark/5 overflow-y-auto">
                @forelse ($piutangJatuhTempo as $row)
                    <button
                        type="button"
                        onclick="openAging('{{ $row->KODEREKANAN }}')"
                        class="flex w-full items-center justify-between px-5 py-2.5 text-left text-sm transition hover:bg-dark/5">
                        <span class="truncate text-dark/80">{{ $row->NAMA }}</span>
                        <span class="font-semibold text-dark">{{ \App\Support\FormatHelper::mask(\App\Support\FormatHelper::redenominasi($row->NILAI)) }}</span>
                    </button>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-dark/40">Tidak ada piutang jatuh tempo.</p>
                @endforelse
            </div>
        </div>

        {{-- KPI bulan ini --}}
        <div class="rounded-xl bg-white shadow-sm">
            <div class="border-b border-dark/10 px-5 py-4">
                <h3 class="font-heading text-sm font-extrabold text-dark">Kinerja Bulan Ini · {{ $kpi['periode_label'] }}</h3>
            </div>
            <div class="divide-y divide-dark/5">
                @foreach (['penjualan' => 'Penjualan', 'hpp' => 'Beban Pokok', 'laba_kotor' => 'Laba Kotor'] as $key => $label)
                    @php($row = $kpi[$key])
                    <div class="px-5 py-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-dark/70">{{ $label }}</span>
                            <span class="font-heading font-bold text-dark">{{ \App\Support\FormatHelper::mask($row['real']) }} / {{ \App\Support\FormatHelper::mask($row['rkap']) }}</span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-dark/5">
                            <div class="h-full {{ \App\Support\FormatHelper::barColorPos($row['capaian']) }}" style="width: {{ min(100, max(0, $row['capaian'])) }}%"></div>
                        </div>
                        <p class="mt-1 text-right text-xs text-dark/40">{{ $row['capaian'] }}% capaian</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pencapaian per sektor --}}
    <div class="mt-6 rounded-xl bg-white shadow-sm">
        <div class="border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading text-sm font-extrabold text-dark">Pencapaian Penjualan per Sektor · {{ $kpi['periode_label'] }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                        <th class="px-5 py-3">Sektor</th>
                        <th class="px-5 py-3 text-right">Realisasi</th>
                        <th class="px-5 py-3 text-right">RKAP</th>
                        <th class="px-5 py-3">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark/5">
                    @forelse ($sektorAchievement as $row)
                        <tr>
                            <td class="px-5 py-3">
                                <button
                                    type="button"
                                    onclick="openSektor('{{ $row['id_sektor'] }}', '{{ $row['kode_sektor'] }}')"
                                    class="rounded-md bg-green/10 px-3 py-1 font-semibold text-green transition hover:bg-green hover:text-white">
                                    {{ strtoupper($row['kode_sektor']) }}
                                </button>
                            </td>
                            <td class="px-5 py-3 text-right font-medium">{{ \App\Support\FormatHelper::mask($row['real']) }}</td>
                            <td class="px-5 py-3 text-right font-medium">{{ \App\Support\FormatHelper::mask($row['rkap']) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-32 overflow-hidden rounded-full bg-dark/5">
                                        <div class="h-full {{ \App\Support\FormatHelper::barColorPos($row['capaian']) }}" style="width: {{ min(100, max(0, $row['capaian'])) }}%"></div>
                                    </div>
                                    <span class="text-xs text-dark/50">{{ $row['capaian'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-dark/40">Belum ada data RKAP sektor untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal: detail penjualan hari ini --}}
    <dialog id="modal-penjualan-hari" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail Penjualan Hari Ini</h3>
            <button type="button" onclick="document.getElementById('modal-penjualan-hari').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-penjualan-hari-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    {{-- Modal: detail aging piutang --}}
    <dialog id="modal-aging" class="w-full max-w-3xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail Piutang Jatuh Tempo</h3>
            <button type="button" onclick="document.getElementById('modal-aging').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-aging-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    {{-- Modal: detail sektor --}}
    <dialog id="modal-sektor" class="w-full max-w-4xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 id="modal-sektor-title" class="font-heading font-extrabold text-dark">Detail Sektor</h3>
            <button type="button" onclick="document.getElementById('modal-sektor').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-sektor-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>
@endsection

@push('scripts')
    <script>
        function openPenjualanHariIni() {
            const modal = document.getElementById('modal-penjualan-hari');
            const body = document.getElementById('modal-penjualan-hari-body');
            modal.showModal();
            body.innerHTML = 'Memuat...';

            axios.post('{{ route('dashboard1.penjHari') }}').then(({ data }) => {
                if (!data.length) {
                    body.innerHTML = '<p class="text-dark/40">Belum ada transaksi hari ini.</p>';
                    return;
                }
                body.innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Sektor</th><th class="py-1">Barang</th><th class="py-1 text-right">Qty</th><th class="py-1 text-right">Nilai</th>
                    </tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map(r => `<tr><td class="py-1.5">${r.KODE_SEKTOR}</td><td class="py-1.5">${r.NAMA_BARANG}</td><td class="py-1.5 text-right">${r.QTY} ${r.SATUAN}</td><td class="py-1.5 text-right font-medium">${Number(r.NILAI).toLocaleString('id-ID')}</td></tr>`).join('') +
                    `</tbody></table>`;
            });
        }

        function openAging(koderekanan) {
            const modal = document.getElementById('modal-aging');
            const body = document.getElementById('modal-aging-body');
            modal.showModal();
            body.innerHTML = 'Memuat...';

            axios.post('{{ route('dashboard1.detail_aging') }}', { korek: koderekanan }).then(({ data }) => {
                if (!data.length) {
                    body.innerHTML = '<p class="text-dark/40">Tidak ada data.</p>';
                    return;
                }
                body.innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">No. Faktur</th><th class="py-1">Tgl Jatuh Tempo</th><th class="py-1 text-right">1-30</th><th class="py-1 text-right">31-60</th><th class="py-1 text-right">61-90</th><th class="py-1 text-right">91-365</th><th class="py-1 text-right">&gt;365</th>
                    </tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map(r => `<tr><td class="py-1.5"><button class="text-green underline" onclick="openFaktur('${r.FAKTUR_DETAIL}')">${r.NOFAKTUR}</button></td><td class="py-1.5">${r.TGL_JTEMPO}</td><td class="py-1.5 text-right">${Number(r.JTH1_30).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(r.JTH31_60).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(r.JTH61_90).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(r.JTH91_365).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(r.JTH365).toLocaleString('id-ID')}</td></tr>`).join('') +
                    `</tbody></table>`;
            });
        }

        function openFaktur(nofaktur) {
            axios.post('{{ route('dashboard1.detail2') }}', { nofaktur }).then(({ data }) => {
                if (!data.header) {
                    alert('Detail faktur tidak ditemukan.');
                    return;
                }
                const h = data.header;
                let html = `<div class="mb-4 grid grid-cols-2 gap-2 text-sm">
                    <div><span class="text-dark/40">No. FPB</span><br><strong>${h.NOMOR_FPB}</strong></div>
                    <div><span class="text-dark/40">Pelanggan</span><br><strong>${h.NAMA}</strong></div>
                    <div><span class="text-dark/40">Tanggal</span><br><strong>${h.TGL_FPB}</strong></div>
                    <div><span class="text-dark/40">Jatuh Tempo</span><br><strong>${h.TGL_JTEMPO}</strong></div>
                </div>`;
                html += `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Keterangan</th><th class="py-1 text-right">Qty</th><th class="py-1 text-right">Nilai</th>
                    </tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.detail.map(d => `<tr><td class="py-1.5">${d.KETERANGAN}</td><td class="py-1.5 text-right">${d.QTY_JUAL} ${d.SATUAN}</td><td class="py-1.5 text-right">${Number(d.NILAI).toLocaleString('id-ID')}</td></tr>`).join('') +
                    `</tbody></table>`;

                document.getElementById('modal-aging-body').innerHTML = html;
            });
        }

        function openSektor(idSektor, kodeSektor) {
            const modal = document.getElementById('modal-sektor');
            const body = document.getElementById('modal-sektor-body');
            document.getElementById('modal-sektor-title').textContent = 'Detail Sektor · ' + kodeSektor.toUpperCase();
            modal.showModal();
            body.innerHTML = 'Memuat...';

            axios.post('{{ route('dashboard1.detail1') }}', { idSektor1: idSektor }).then(({ data }) => {
                if (!data.length) {
                    body.innerHTML = '<p class="text-dark/40">Tidak ada data.</p>';
                    return;
                }
                body.innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Wilayah</th><th class="py-1">Barang</th><th class="py-1 text-right">Qty Real</th><th class="py-1 text-right">Qty RKAP</th><th class="py-1 text-right">Nilai Real</th><th class="py-1 text-right">Nilai RKAP</th><th class="py-1 text-right">Capaian</th>
                    </tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map(r => `<tr><td class="py-1.5">${r.KODE_SEKTOR}</td><td class="py-1.5">${r.NAMA_BARANG}</td><td class="py-1.5 text-right">${r.QTY_REAL} ${r.SATUAN}</td><td class="py-1.5 text-right">${r.QTY_RKAP} ${r.SATUAN}</td><td class="py-1.5 text-right">${Number(r.NILAI_REAL).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(r.NILAI_RKAP).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${r.CAP_NILAI}%</td></tr>`).join('') +
                    `</tbody></table>`;
            });
        }
    </script>
@endpush

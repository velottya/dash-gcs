@extends('layouts.app')

@section('title', 'Laporan Laba / (Rugi)')

@section('content')
    <div class="mb-3 flex items-center justify-end gap-2">
        <label for="select-historis-tahun" class="text-sm text-dark/60">Rentang Historis:</label>
        <select id="select-historis-tahun" onchange="location.href = '{{ route('labar.index') }}?historis=' + this.value"
            class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
            @foreach ($historisOptions as $opt)
                <option value="{{ $opt }}" @selected($opt === $historisTahun)>{{ $opt }} Tahun Terakhir</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Historis Penjualan {{ $historisTahun }} Tahun Terakhir</h3>
                <button type="button" onclick="openHistorisDetail()" class="text-xs font-semibold text-green hover:underline">Detail</button>
            </div>
            <canvas id="histogram9" height="180"></canvas>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Historis Laba Bersih {{ $historisTahun }} Tahun Terakhir</h3>
                <button type="button" onclick="openHistorisDetail()" class="text-xs font-semibold text-green hover:underline">Detail</button>
            </div>
            <canvas id="histogram10" height="180"></canvas>
        </div>
    </div>
    <x-insight-box :items="$insightHistoris" class="mt-3" />

    <dialog id="modal-historis-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Historis Penjualan &amp; Laba {{ $historisTahun }} Tahun Terakhir</h3>
            <button type="button" onclick="document.getElementById('modal-historis-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-historis-detail-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    <div class="mt-6 rounded-xl bg-white p-5 shadow-sm">
        <div class="flex items-center gap-2">
            <label for="periodeLabar" class="text-sm font-medium text-dark/70">Periode:</label>
            <input type="text" id="periodeLabar" value="{{ now()->format('Ym') }}" class="w-28 rounded-lg border border-dark/15 px-2 py-1 text-sm">
            <button type="button" onclick="getLRbulan()" class="rounded-lg bg-green px-3 py-1.5 text-sm text-white transition hover:bg-green/90">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-5 shadow-sm">
        <h3 class="font-heading mb-3 text-sm font-extrabold text-dark">Laporan Laba / (Rugi)</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                    <th class="px-3 py-2">Keterangan</th>
                    <th class="px-3 py-2 text-right" id="bulanRealisasi">{{ \App\Support\FormatHelper::getBulan((int) now()->format('n')) }} {{ now()->format('Y') }}</th>
                    <th class="px-3 py-2 text-right">RKAP</th>
                    <th class="px-3 py-2 text-center">Progress</th>
                    <th class="px-3 py-2 text-right" id="bulanSbl">{{ \App\Support\FormatHelper::getBulan((int) now()->format('n')) }} {{ now()->subYear()->format('Y') }}</th>
                    <th class="px-3 py-2 text-center">Progress</th>
                </tr>
            </thead>
            <tbody id="content-labar" class="divide-y divide-dark/5">
                @php($jumlahReal = 0)
                @php($jumlahSbl = 0)
                @php($jumlahRkap = 0)
                @php($totalReal = 0)
                @php($totalSbl = 0)
                @php($totalRkap = 0)
                @php($grupLaporan = '')
                @foreach ($data as $rec)
                    @php($totalReal += $rec->REALISASI)
                    @php($totalRkap += $rec->RKAP)
                    @php($totalSbl += $rec->THNSBL)
                    @if ($grupLaporan !== '' && trim($rec->GRUP_LAPORAN) !== $grupLaporan)
                        @php($capaianRkap = \App\Support\FormatHelper::targetPersen($jumlahRkap, $jumlahReal))
                        @php($capaianSbl = \App\Support\FormatHelper::targetPersen($jumlahSbl, $jumlahReal))
                        <tr class="bg-gold/10 font-semibold">
                            <td class="px-3 py-2">{{ $grupLaporan }}</td>
                            <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($jumlahReal) }}</td>
                            <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($jumlahRkap) }}</td>
                            <td class="px-3 py-2">
                                <div class="h-4 w-full overflow-hidden rounded-full bg-dark/10">
                                    <div class="h-4 {{ \App\Support\FormatHelper::barColor($jumlahReal, $capaianRkap) }} text-center text-[10px] leading-4 text-white" style="width: {{ min(100, max(0, $capaianRkap)) }}%">{{ $capaianRkap }}%</div>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($jumlahSbl) }}</td>
                            <td class="px-3 py-2">
                                <div class="h-4 w-full overflow-hidden rounded-full bg-dark/10">
                                    <div class="h-4 {{ \App\Support\FormatHelper::barColor($jumlahReal, $capaianSbl) }} text-center text-[10px] leading-4 text-white" style="width: {{ min(100, max(0, $capaianSbl)) }}%">{{ $capaianSbl }}%</div>
                                </div>
                            </td>
                        </tr>
                        @php($jumlahReal = 0)
                        @php($jumlahSbl = 0)
                        @php($jumlahRkap = 0)
                    @endif
                    @php($grupLaporan = trim($rec->GRUP_LAPORAN))
                    @php($realisasiK = \App\Support\FormatHelper::redenominasi($rec->REALISASI))
                    @php($thnsblK = \App\Support\FormatHelper::redenominasi($rec->THNSBL))
                    @php($jumlahReal += $realisasiK)
                    @php($jumlahSbl += $thnsblK)
                    @php($jumlahRkap += $rec->RKAP)
                    @php($capaianRkap1 = \App\Support\FormatHelper::targetPersen($rec->RKAP, $realisasiK))
                    @php($capaianSbl1 = \App\Support\FormatHelper::targetPersen($rec->THNSBL, $rec->REALISASI))
                    <tr>
                        <td class="px-3 py-2">{{ trim($rec->KETERANGAN) }}</td>
                        <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($realisasiK) }}</td>
                        <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($rec->RKAP) }}</td>
                        <td class="px-3 py-2">
                            <div class="h-4 w-full overflow-hidden rounded-full bg-dark/10">
                                <div class="h-4 {{ \App\Support\FormatHelper::barColor($jumlahReal, $capaianRkap1) }} text-center text-[10px] leading-4 text-white" style="width: {{ min(100, max(0, $capaianRkap1)) }}%">{{ $capaianRkap1 }}%</div>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($thnsblK) }}</td>
                        <td class="px-3 py-2">
                            <div class="h-4 w-full overflow-hidden rounded-full bg-dark/10">
                                <div class="h-4 {{ \App\Support\FormatHelper::barColor($jumlahReal, $capaianSbl1) }} text-center text-[10px] leading-4 text-white" style="width: {{ min(100, max(0, $capaianSbl1)) }}%">{{ $capaianSbl1 }}%</div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @php($capaianTotal = \App\Support\FormatHelper::targetPersen($totalRkap, \App\Support\FormatHelper::redenominasi($totalReal)))
                @php($capaianTotalSbl = \App\Support\FormatHelper::targetPersen($totalSbl, $totalReal))
                <tr class="bg-gold/10 font-bold">
                    <td class="px-3 py-2 text-right">Laba Bersih</td>
                    <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp(\App\Support\FormatHelper::redenominasi($totalReal)) }}</td>
                    <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($totalRkap) }}</td>
                    <td class="px-3 py-2">
                        <div class="h-4 w-full overflow-hidden rounded-full bg-dark/10">
                            <div class="h-4 {{ \App\Support\FormatHelper::barColor($totalReal, $capaianTotal) }} text-center text-[10px] leading-4 text-white" style="width: {{ min(100, max(0, $capaianTotal)) }}%">{{ $capaianTotal }}%</div>
                        </div>
                    </td>
                    <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::maskRp(\App\Support\FormatHelper::redenominasi($totalSbl)) }}</td>
                    <td class="px-3 py-2">
                        <div class="h-4 w-full overflow-hidden rounded-full bg-dark/10">
                            <div class="h-4 {{ \App\Support\FormatHelper::barColor($totalReal, $capaianTotalSbl) }} text-center text-[10px] leading-4 text-white" style="width: {{ min(100, max(0, $capaianTotalSbl)) }}%">{{ $capaianTotalSbl }}%</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection


@push('scripts')
    <script>
        const historisData = @json($datax);
        function rupiah(v) { return Number(v).toLocaleString('id-ID'); }
        function barClass(nilai1, nilai) {
            if (nilai1 > 0) {
                return nilai < 75 ? 'bg-red-500' : (nilai <= 90 ? 'bg-amber-500' : 'bg-green');
            }
            return nilai > 100 ? 'bg-red-500' : (nilai >= 90 ? 'bg-amber-500' : 'bg-green');
        }

        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('histogram9'), {
                type: 'bar',
                data: {
                    labels: historisData.map(r => r.TAHUN),
                    datasets: [{ label: 'Penjualan (Dalam Ribu)', data: historisData.map(r => Math.trunc(r.PENJUALAN / 1000)), backgroundColor: '#2F6C3F' }],
                },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });

            new Chart(document.getElementById('histogram10'), {
                data: {
                    labels: historisData.map(r => r.TAHUN),
                    datasets: [
                        { type: 'line', label: 'Net Profit Margin', data: historisData.map(r => (r.LABA / r.PENJUALAN) * 100), borderColor: '#DAA628', yAxisID: 'y1', fill: false },
                        { type: 'bar', label: 'Laba', data: historisData.map(r => Math.trunc(r.LABA / 1000)), backgroundColor: '#0F261F', yAxisID: 'y' },
                    ],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { type: 'linear', position: 'left' },
                        y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false } },
                    },
                },
            });
        });

        function openHistorisDetail() {
            const modal = document.getElementById('modal-historis-detail');
            modal.showModal();

            const rows = historisData.map(r => `<tr><td class="py-1.5">${r.TAHUN}</td><td class="py-1.5 text-right">${rupiah(r.PENJUALAN)}</td><td class="py-1.5 text-right">${rupiah(r.LABA)}</td><td class="py-1.5 text-right">${((r.LABA / r.PENJUALAN) * 100).toFixed(2)}%</td></tr>`).join('');
            document.getElementById('modal-historis-detail-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                <th class="py-1">Tahun</th><th class="py-1 text-right">Penjualan</th><th class="py-1 text-right">Laba</th><th class="py-1 text-right">NPM</th></tr></thead><tbody class="divide-y divide-dark/5">${rows}</tbody></table>`;
        }

        function getLRbulan() {
            const periode = document.getElementById('periodeLabar').value;
            const tahun = periode.substring(0, 4);
            const bulan = periode.substring(4, 6);
            const tahunSbl = parseInt(tahun) - 1;

            axios.post('{{ route('labar.detail1') }}', { periodeLabar: periode }).then(({ data }) => {
                let html = '';
                let sektor = null;
                let jumlahReal = 0, jumlahSbl = 0, jumlahRkap = 0;

                data.forEach((item) => {
                    if (sektor !== null && item.GRUP_LAPORAN !== sektor) {
                        const capRkap = Math.round((jumlahReal / jumlahRkap) * 100);
                        const capSbl = Math.round((jumlahReal / jumlahSbl) * 100);
                        html += `<tr class="bg-gold/10 font-semibold"><td class="px-3 py-2">${sektor}</td><td class="px-3 py-2 text-right">${rupiah(jumlahReal)}</td><td class="px-3 py-2 text-right">${rupiah(jumlahRkap)}</td><td class="px-3 py-2"><div class="h-4 w-full overflow-hidden rounded-full bg-dark/10"><div class="h-4 ${barClass(jumlahReal, capRkap)} text-center text-[10px] leading-4 text-white" style="width:${Math.min(100, Math.max(0, capRkap))}%">${capRkap}%</div></div></td><td class="px-3 py-2 text-right">${rupiah(jumlahSbl)}</td><td class="px-3 py-2"><div class="h-4 w-full overflow-hidden rounded-full bg-dark/10"><div class="h-4 ${barClass(jumlahReal, capSbl)} text-center text-[10px] leading-4 text-white" style="width:${Math.min(100, Math.max(0, capSbl))}%">${capSbl}%</div></div></td></tr>`;
                        jumlahReal = 0; jumlahSbl = 0; jumlahRkap = 0;
                    }

                    html += `<tr><td class="px-3 py-2">${item.KETERANGAN}</td><td class="px-3 py-2 text-right">${rupiah(item.REALISASI)}</td><td class="px-3 py-2 text-right">${rupiah(item.RKAP)}</td><td class="px-3 py-2"><div class="h-4 w-full overflow-hidden rounded-full bg-dark/10"><div class="h-4 ${barClass(item.REALISASI, item.CAP_RKAP)} text-center text-[10px] leading-4 text-white" style="width:${Math.min(100, Math.max(0, item.CAP_RKAP))}%">${item.CAP_RKAP}%</div></div></td><td class="px-3 py-2 text-right">${rupiah(item.THNSBL)}</td><td class="px-3 py-2"><div class="h-4 w-full overflow-hidden rounded-full bg-dark/10"><div class="h-4 ${barClass(item.REALISASI, item.CAP_THNSBL)} text-center text-[10px] leading-4 text-white" style="width:${Math.min(100, Math.max(0, item.CAP_THNSBL))}%">${item.CAP_THNSBL}%</div></div></td></tr>`;

                    sektor = item.GRUP_LAPORAN;
                    jumlahReal += parseInt(item.REALISASI);
                    jumlahSbl += parseInt(item.THNSBL);
                    jumlahRkap += parseInt(item.RKAP);
                });

                const labaRkap = Math.round((jumlahReal / jumlahRkap) * 100);
                const labaSbl = Math.round((jumlahReal / jumlahSbl) * 100);
                html += `<tr class="bg-gold/10 font-bold"><td class="px-3 py-2 text-right">Laba Bersih</td><td class="px-3 py-2 text-right">${rupiah(jumlahReal)}</td><td class="px-3 py-2 text-right">${rupiah(jumlahRkap)}</td><td class="px-3 py-2"><div class="h-4 w-full overflow-hidden rounded-full bg-dark/10"><div class="h-4 ${barClass(jumlahReal, labaRkap)} text-center text-[10px] leading-4 text-white" style="width:${Math.min(100, Math.max(0, labaRkap))}%">${labaRkap}%</div></div></td><td class="px-3 py-2 text-right">${rupiah(jumlahSbl)}</td><td class="px-3 py-2"><div class="h-4 w-full overflow-hidden rounded-full bg-dark/10"><div class="h-4 ${barClass(jumlahReal, labaSbl)} text-center text-[10px] leading-4 text-white" style="width:${Math.min(100, Math.max(0, labaSbl))}%">${labaSbl}%</div></div></td></tr>`;

                document.getElementById('content-labar').innerHTML = html;
                document.getElementById('bulanRealisasi').textContent = `Bulan ${bulan}/${tahun}`;
                document.getElementById('bulanSbl').textContent = `Bulan ${bulan}/${tahunSbl}`;
            });
        }
    </script>
@endpush

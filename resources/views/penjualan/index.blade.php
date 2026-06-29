@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">(YTD) Year to Date (x1000)</h3>
                <select id="select-chart1" onchange="loadChart1()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                    <option value="penjualan">Penjualan (Year To Date)</option>
                    <option value="labkor">Laba Kotor (Year to Date)</option>
                </select>
            </div>
            <canvas id="chart1" height="110"></canvas>
            <div class="mt-3 flex justify-end gap-4 text-xs text-dark/50">
                <span><i class="fas fa-square text-green"></i> Real</span>
                <span><i class="fas fa-square text-gold"></i> RKAP</span>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Per Sektor (Dalam Ribu)</h3>
                <select id="select-chart2" onchange="loadChart2()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                    @for ($i = 1; $i <= $maxBulan; $i++)
                        <option value="{{ $i }}" @selected($i === $maxBulan)>{{ \App\Support\FormatHelper::getBulan($i) }}</option>
                    @endfor
                </select>
            </div>
            <canvas id="chart2" height="160"></canvas>
            <button type="button" onclick="openDetail1()" class="mt-3 w-full rounded-lg border border-green/30 px-3 py-1.5 text-sm text-green transition hover:bg-green hover:text-white">
                <i class="fas fa-angle-up"></i> Detail
            </button>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-heading text-sm font-extrabold text-dark">Per Wilayah (Dalam Ribu)</h3>
            <select id="select-chart3" onchange="loadChart3()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                @for ($i = 1; $i <= $maxBulan; $i++)
                    <option value="{{ $i }}" @selected($i === $maxBulan)>{{ \App\Support\FormatHelper::getBulan($i) }}</option>
                @endfor
            </select>
        </div>
        <canvas id="chart3" height="100"></canvas>
        <button type="button" onclick="openDetail2()" class="mt-3 rounded-lg border border-green/30 px-3 py-1.5 text-sm text-green transition hover:bg-green hover:text-white">
            <i class="fas fa-angle-up"></i> Detail
        </button>
    </div>

    <dialog id="modal-detail1" class="w-full max-w-4xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail Penjualan Per Sektor</h3>
            <button type="button" onclick="document.getElementById('modal-detail1').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-detail1-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    <dialog id="modal-detail2" class="w-full max-w-4xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail Penjualan Per Wilayah</h3>
            <button type="button" onclick="document.getElementById('modal-detail2').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-detail2-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>
@endsection

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@push('scripts')
    <script>
        const rkapYtd = @json($rkap);
        const monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
        function rupiah(v) { return Number(v).toLocaleString('id-ID'); }

        let chart1, chart2, chart3;

        function buildChart1(labels, real, rkap) {
            if (chart1) chart1.destroy();
            chart1 = new Chart(document.getElementById('chart1'), {
                type: 'bar',
                data: { labels, datasets: [
                    { label: 'Real', data: real, backgroundColor: '#2F6C3F' },
                    { label: 'RKAP', data: rkap, backgroundColor: '#DAA628' },
                ] },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });
        }

        function loadChart1() {
            const opt = document.getElementById('select-chart1').value;
            axios.post('{{ route('penjualan.chart_detail') }}', { option: opt }).then(({ data }) => {
                buildChart1(data.map((_, i) => rkapYtd[i]?.PERIODE ?? ''), data.map(d => d.AMOUNT), data.map(d => d.RKAP));
            });
        }

        buildChart1(monthLabels, @json(array_map(fn ($r) => $r->AMOUNT, $chart1)), rkapYtd.map(r => r.NILAI));

        function buildChart2(labels, data) {
            if (chart2) chart2.destroy();
            chart2 = new Chart(document.getElementById('chart2'), {
                type: 'pie',
                data: { labels, datasets: [{ data, backgroundColor: ['#2F6C3F', '#3C8A51', '#DAA628', '#F6D30F', '#0F261F', '#95EEA1'] }] },
                options: { responsive: true },
            });
        }

        buildChart2(@json(array_map(fn ($r) => $r->SUB_GROUP, $chart2)), @json(array_map(fn ($r) => $r->AMOUNT, $chart2)));

        function loadChart2() {
            axios.post('{{ route('penjualan.chart2') }}', { option: document.getElementById('select-chart2').value }).then(({ data }) => {
                buildChart2(data.map(d => d.SUB_GROUP), data.map(d => d.AMOUNT));
            });
        }

        function buildChart3(labels, data) {
            if (chart3) chart3.destroy();
            chart3 = new Chart(document.getElementById('chart3'), {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Nilai', data, backgroundColor: '#2F6C3F' }] },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });
        }

        buildChart3(@json(array_map(fn ($r) => $r->SUB_GROUP, $chart3)), @json(array_map(fn ($r) => $r->AMOUNT, $chart3)));

        function loadChart3() {
            axios.post('{{ route('penjualan.chart3') }}', { option: document.getElementById('select-chart3').value }).then(({ data }) => {
                buildChart3(data.map(d => d.SUB_GROUP), data.map(d => d.AMOUNT));
            });
        }

        function openDetail1() {
            const modal = document.getElementById('modal-detail1');
            modal.showModal();
            document.getElementById('modal-detail1-body').innerHTML = 'Memuat...';

            axios.post('{{ route('penjualan.detail1') }}', { option: document.getElementById('select-chart2').value }).then(({ data }) => {
                document.getElementById('modal-detail1-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Sektor</th><th class="py-1">Barang</th><th class="py-1 text-right">Qty</th><th class="py-1">Satuan</th><th class="py-1 text-right">Nilai</th></tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map(r => `<tr><td class="py-1.5">${r.KODE_SEKTOR}</td><td class="py-1.5">${r.NAMA_BARANG}</td><td class="py-1.5 text-right">${rupiah(r.QTY)}</td><td class="py-1.5">${r.SATUAN}</td><td class="py-1.5 text-right">${rupiah(r.AMOUNT)}</td></tr>`).join('') +
                    `</tbody></table>`;
            });
        }

        function openDetail2() {
            const modal = document.getElementById('modal-detail2');
            modal.showModal();
            document.getElementById('modal-detail2-body').innerHTML = 'Memuat...';

            axios.post('{{ route('penjualan.detail2') }}', { option: document.getElementById('select-chart3').value }).then(({ data }) => {
                document.getElementById('modal-detail2-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Sub Wilayah</th><th class="py-1">Kode CC</th><th class="py-1">Wilayah</th><th class="py-1 text-right">Nilai</th></tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map(r => `<tr><td class="py-1.5">${r.SUBWIL}</td><td class="py-1.5">${r.KODE_CC}</td><td class="py-1.5">${r.WILAYAH}</td><td class="py-1.5 text-right">${rupiah(r.NILAI)}</td></tr>`).join('') +
                    `</tbody></table>`;
            });
        }
    </script>
@endpush

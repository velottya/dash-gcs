@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">(YTD) Year to Date (Dalam Ribu)</h3>
                <div class="flex items-center gap-3">
                    <select id="select-tahun1" onchange="location.href = '{{ route('penjualan.index') }}?tahun=' + this.value"
                        class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                        @foreach ($tahunOptions as $ty)
                            <option value="{{ $ty }}" @selected($ty === $tahun)>{{ $ty }}</option>
                        @endforeach
                    </select>
                    <select id="select-chart1" onchange="loadChart1()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                        <option value="penjualan">Penjualan (Year To Date)</option>
                        <option value="labkor">Laba Kotor (Year to Date)</option>
                    </select>
                    <button type="button" onclick="openChart1Detail()" class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">
                        <i class="fas fa-angle-up"></i> Detail
                    </button>
                </div>
            </div>
            <canvas id="chart1" height="110"></canvas>
            <div class="mt-3 flex justify-end gap-4 text-xs text-dark/50">
                <span><i class="fas fa-square text-green"></i> Real</span>
                <span><i class="fas fa-square text-gold"></i> RKAP</span>
                <span><i class="fas fa-square text-dark/40"></i> Thn Sbl</span>
            </div>
            <x-insight-box :items="$insightChart1" />
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Per Sektor (Dalam Ribu)</h3>
                <div class="flex items-center gap-3">
                    <select id="select-chart2" onchange="loadChart2()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                        @for ($i = 1; $i <= $maxBulan; $i++)
                            <option value="{{ $i }}" @selected($i === $maxBulan)>{{ \App\Support\FormatHelper::getBulan($i) }}</option>
                        @endfor
                    </select>
                    <button type="button" onclick="openDetail1()" class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">
                        <i class="fas fa-angle-up"></i> Detail
                    </button>
                </div>
            </div>
            <canvas id="chart2" height="160"></canvas>
            <x-insight-box :items="$insightChart2" />
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-heading text-sm font-extrabold text-dark">Per Wilayah (Dalam Ribu)</h3>
            <div class="flex items-center gap-3">
                <select id="select-chart3" onchange="loadChart3()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                    @for ($i = 1; $i <= $maxBulan; $i++)
                        <option value="{{ $i }}" @selected($i === $maxBulan)>{{ \App\Support\FormatHelper::getBulan($i) }}</option>
                    @endfor
                </select>
                <button type="button" onclick="openDetail2()" class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">
                    <i class="fas fa-angle-up"></i> Detail
                </button>
            </div>
        </div>
        <canvas id="chart3" height="100"></canvas>
        <x-insight-box :items="$insightChart3" />
    </div>

    <dialog id="modal-chart1-detail" class="w-full max-w-3xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail (YTD)</h3>
            <button type="button" onclick="document.getElementById('modal-chart1-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div class="flex gap-1 border-b border-dark/10 px-5 pt-3">
            <button type="button" id="tabbtn-c1-bulanan" onclick="switchChart1Tab('bulanan')" class="chart1-tab-btn border-b-2 px-3 py-2 text-xs font-semibold transition">Bulanan</button>
            <button type="button" id="tabbtn-c1-sektor" onclick="switchChart1Tab('sektor')" class="chart1-tab-btn border-b-2 px-3 py-2 text-xs font-semibold transition">Per Sektor</button>
            <button type="button" id="tabbtn-c1-wilayah" onclick="switchChart1Tab('wilayah')" class="chart1-tab-btn border-b-2 px-3 py-2 text-xs font-semibold transition">Per Wilayah</button>
        </div>
        <div id="tab-c1-bulanan" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
        <div id="tab-c1-sektor" class="hidden max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
        <div id="tab-c1-wilayah" class="hidden max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

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


@push('scripts')
    <script>
        const rkapYtd = @json($rkap);
        const CURRENT_TAHUN = {{ $tahun }};
        const monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
        function rupiah(v) { return Number(v).toLocaleString('id-ID'); }

        let chart1, chart2, chart3;

        function buildChart1(labels, real, rkap, sbl) {
            if (chart1) chart1.destroy();
            chart1 = new Chart(document.getElementById('chart1'), {
                type: 'bar',
                data: { labels, datasets: [
                    { label: 'Real', data: real, backgroundColor: '#2F6C3F' },
                    { label: 'RKAP', data: rkap, backgroundColor: '#DAA628' },
                    { label: 'Thn Sbl', data: sbl, backgroundColor: 'rgba(15, 38, 31, 0.4)' },
                ] },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });
        }

        function loadChart1() {
            const opt = document.getElementById('select-chart1').value;
            axios.post('{{ route('penjualan.chart_detail') }}', { option: opt, tahun: CURRENT_TAHUN }).then(({ data }) => {
                const real = Array(12).fill(0);
                const rkap = Array(12).fill(0);
                const sbl = Array(12).fill(0);
                data.forEach((r, i) => { real[i] = r.AMOUNT ?? 0; rkap[i] = r.RKAP ?? 0; sbl[i] = r.THNSBL ?? 0; });
                buildChart1(monthLabels, real, rkap, sbl);
            });
        }

        function buildChart2(labels, data) {
            if (chart2) chart2.destroy();
            chart2 = new Chart(document.getElementById('chart2'), {
                type: 'pie',
                data: { labels, datasets: [{ data, backgroundColor: ['#2F6C3F', '#DAA628', '#0F261F', '#95EEA1', '#F6D30F', '#3C8A51', '#E74C3C', '#2980B9', '#8E44AD', '#7F8C8D'] }] },
                options: { responsive: true },
            });
        }

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

        function loadChart3() {
            axios.post('{{ route('penjualan.chart3') }}', { option: document.getElementById('select-chart3').value }).then(({ data }) => {
                buildChart3(data.map(d => d.SUB_GROUP), data.map(d => d.AMOUNT));
            });
        }

        let chart1BreakdownLoaded = false;

        function openChart1Detail() {
            const modal = document.getElementById('modal-chart1-detail');
            modal.showModal();
            switchChart1Tab('bulanan');
            document.getElementById('tab-c1-bulanan').innerHTML = 'Memuat...';

            axios.post('{{ route('penjualan.chart_detail') }}', { option: document.getElementById('select-chart1').value, tahun: CURRENT_TAHUN }).then(({ data }) => {
                document.getElementById('tab-c1-bulanan').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Bulan</th><th class="py-1 text-right">Real</th><th class="py-1 text-right">RKAP</th><th class="py-1 text-right">Capaian</th><th class="py-1 text-right">Thn Sbl</th></tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map((r, i) => `<tr><td class="py-1.5">${monthLabels[i] ?? ''}</td><td class="py-1.5 text-right">${rupiah(r.AMOUNT)}</td><td class="py-1.5 text-right">${rupiah(r.RKAP)}</td><td class="py-1.5 text-right">${r.RKAP ? Math.round((r.AMOUNT / r.RKAP) * 100) : 0}%</td><td class="py-1.5 text-right">${rupiah(r.THNSBL)}</td></tr>`).join('') +
                    `</tbody></table>`;
            });

            if (!chart1BreakdownLoaded) {
                chart1BreakdownLoaded = true;
                axios.post('{{ route('penjualan.breakdown') }}', { tahun: CURRENT_TAHUN }).then(({ data }) => {
                    renderSubGroupTable('tab-c1-sektor', data.sektor);
                    renderSubGroupTable('tab-c1-wilayah', data.wilayah);
                });
            }
        }

        function renderSubGroupTable(elId, rows) {
            const el = document.getElementById(elId);
            if (!rows || !rows.length) {
                el.innerHTML = '<p class="text-dark/40">Tidak ada data.</p>';
                return;
            }
            const total = rows.reduce((sum, r) => sum + Number(r.AMOUNT), 0);
            const body = rows.map(r => {
                const pct = total ? ((Number(r.AMOUNT) / total) * 100).toFixed(1) : '0.0';
                return `<tr><td class="py-1.5">${r.SUB_GROUP}</td><td class="py-1.5 text-right">${rupiah(r.AMOUNT)}</td><td class="py-1.5 text-right">${pct}%</td></tr>`;
            }).join('');
            el.innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                <th class="py-1">Kelompok</th><th class="py-1 text-right">Nilai</th><th class="py-1 text-right">%</th></tr></thead><tbody class="divide-y divide-dark/5">${body}</tbody></table>`;
        }

        function switchChart1Tab(tab) {
            ['bulanan', 'sektor', 'wilayah'].forEach(t => {
                document.getElementById('tab-c1-' + t).classList.toggle('hidden', t !== tab);
                const btn = document.getElementById('tabbtn-c1-' + t);
                btn.classList.toggle('text-green', t === tab);
                btn.classList.toggle('border-green', t === tab);
                btn.classList.toggle('text-dark/40', t !== tab);
                btn.classList.toggle('border-transparent', t !== tab);
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

        // DOMContentLoaded fires after ES-module scripts (app.js) have run, so window.Chart is ready.
        document.addEventListener('DOMContentLoaded', function () {
            // Align real data to correct month index (DB returns only months with entries)
            const c1Raw = @json($chart1);
            const c1Real = Array(12).fill(0);
            c1Raw.forEach(r => {
                const idx = parseInt(r.BULAN) - 1;
                if (idx >= 0 && idx < 12) c1Real[idx] = r.AMOUNT ?? 0;
            });

            // Align RKAP to month (PERIODE = "202401" → index 0)
            const c1Rkap = Array(12).fill(0);
            rkapYtd.forEach(r => {
                const idx = parseInt(String(r.PERIODE).slice(-2)) - 1;
                if (idx >= 0 && idx < 12) c1Rkap[idx] = r.NILAI ?? 0;
            });

            // Thn Sbl: same series as Real but for the previous year
            const c1SblRaw = @json($chart1Sbl);
            const c1Sbl = Array(12).fill(0);
            c1SblRaw.forEach(r => {
                const idx = parseInt(r.BULAN) - 1;
                if (idx >= 0 && idx < 12) c1Sbl[idx] = r.AMOUNT ?? 0;
            });

            buildChart1(monthLabels, c1Real, c1Rkap, c1Sbl);
            buildChart2(@json(array_map(fn ($r) => $r->SUB_GROUP, $chart2)), @json(array_map(fn ($r) => $r->AMOUNT, $chart2)));
            buildChart3(@json(array_map(fn ($r) => $r->SUB_GROUP, $chart3)), @json(array_map(fn ($r) => $r->AMOUNT, $chart3)));
        });
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Dashboard Tahunan')
@section('subtitle', now()->format('Y'))

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-sm text-dark/60">
                <span>Penjualan</span>
                <span class="{{ $kpi['penjualan']['naik'] ? 'text-green' : 'text-red-600' }} text-xs font-semibold">
                    <i class="fas {{ $kpi['penjualan']['naik'] ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ $kpi['penjualan']['mom'] }}% MoM
                </span>
            </div>
            <div class="font-heading mt-1 text-xl font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($kpi['penjualan']['real']) }}</div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-dark/10">
                <div class="h-2 {{ \App\Support\FormatHelper::barColorPos($kpi['penjualan']['capaian']) }}" style="width: {{ min(100, max(0, $kpi['penjualan']['capaian'])) }}%"></div>
            </div>
            <div class="mt-1 text-xs text-dark/50">{{ $kpi['penjualan']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpi['penjualan']['rkap']) }}</div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-sm text-dark/60">
                <span>Laba Kotor</span>
                <span class="text-xs font-semibold text-gold">GPM {{ $kpi['laba_kotor']['gpm'] }}%</span>
            </div>
            <div class="font-heading mt-1 text-xl font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($kpi['laba_kotor']['real']) }}</div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-dark/10">
                <div class="h-2 {{ \App\Support\FormatHelper::barColorPos($kpi['laba_kotor']['capaian']) }}" style="width: {{ min(100, max(0, $kpi['laba_kotor']['capaian'])) }}%"></div>
            </div>
            <div class="mt-1 text-xs text-dark/50">{{ $kpi['laba_kotor']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpi['laba_kotor']['rkap']) }}</div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-sm text-dark/60">
                <span>Laba Operasi</span>
                <span class="text-xs font-semibold text-green">OPM {{ $kpi['laba_operasi']['opm'] }}%</span>
            </div>
            <div class="font-heading mt-1 text-xl font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($kpi['laba_operasi']['real']) }}</div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-dark/10">
                <div class="h-2 {{ \App\Support\FormatHelper::barColorPos($kpi['laba_operasi']['capaian']) }}" style="width: {{ min(100, max(0, $kpi['laba_operasi']['capaian'])) }}%"></div>
            </div>
            <div class="mt-1 text-xs text-dark/50">{{ $kpi['laba_operasi']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpi['laba_operasi']['rkap']) }}</div>
        </div>

        <div class="rounded-xl bg-dark p-5 shadow-sm">
            <div class="flex items-center justify-between text-sm text-white/60">
                <span>Laba Bersih</span>
                <span class="text-xs font-semibold text-gold">NPM {{ $kpi['laba_bersih']['npm'] }}%</span>
            </div>
            <div class="font-heading mt-1 text-xl font-extrabold text-white">Rp {{ \App\Support\FormatHelper::maskRp($kpi['laba_bersih']['real']) }}</div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white/10">
                <div class="h-2 bg-gold" style="width: {{ min(100, max(0, $kpi['laba_bersih']['capaian'])) }}%"></div>
            </div>
            <div class="mt-1 text-xs text-white/50">{{ $kpi['laba_bersih']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpi['laba_bersih']['rkap']) }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="font-heading mb-3 text-sm font-extrabold text-dark">Penjualan (x1000)</h3>
            <canvas id="lineChart1" height="160"></canvas>
            <div class="mt-3 flex justify-end gap-4 text-xs text-dark/50">
                <span><i class="fas fa-square text-green"></i> Real</span>
                <span><i class="fas fa-square text-gold"></i> Anggaran</span>
                <span><i class="fas fa-square text-dark/40"></i> Thn Sbl</span>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Laba & Margin (x1000)</h3>
                <select id="selectLaba" onchange="loadLineChart()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                    <option value="labkor">Laba Kotor (Year To Date)</option>
                    <option value="penjualan">Penjualan (Year To Date)</option>
                </select>
            </div>
            <canvas id="lineChart" height="160"></canvas>
        </div>
    </div>
@endsection

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@push('scripts')
    <script>
        const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const penjualanTahunan = @json($penjualanTahunan);
        const labaTahunan = @json($labaTahunan);

        let lineChart1, lineChart;

        lineChart1 = new Chart(document.getElementById('lineChart1'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [
                    { label: 'Real', data: penjualanTahunan.real, backgroundColor: '#2F6C3F' },
                    { label: 'Anggaran', data: penjualanTahunan.rkap, backgroundColor: '#DAA628' },
                    { label: 'Thn Sbl', data: penjualanTahunan.sbl, backgroundColor: '#0F261F' },
                ],
            },
            options: { responsive: true, plugins: { legend: { display: false } } },
        });

        function buildLineChart(real, rkap, sbl) {
            if (lineChart) lineChart.destroy();
            lineChart = new Chart(document.getElementById('lineChart'), {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [
                        { label: 'Real', data: real, borderColor: '#2F6C3F', fill: false },
                        { label: 'Anggaran', data: rkap, borderColor: '#DAA628', fill: false },
                        { label: 'Thn Sbl', data: sbl, borderColor: '#0F261F', fill: false },
                    ],
                },
                options: { responsive: true },
            });
        }

        buildLineChart(labaTahunan.real, labaTahunan.rkap, labaTahunan.sbl);

        function loadLineChart() {
            const opt = document.getElementById('selectLaba').value;
            const series = opt === 'labkor' ? labaTahunan : penjualanTahunan;
            buildLineChart(series.real, series.rkap, series.sbl);
        }
    </script>
@endpush

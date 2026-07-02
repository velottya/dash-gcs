@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan kinerja PT Gresik Cipta Sejahtera')

@section('content')
    {{-- Kinerja bulan berjalan --}}
    <div class="mt-2">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-heading text-sm font-extrabold uppercase tracking-wide text-dark/50">Kinerja Bulan Berjalan</h2>
            <span class="rounded-lg bg-dark/5 px-3 py-1 text-xs font-semibold text-dark/60">Periode: {{ $kpi['periode_label'] }}</span>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between text-sm text-dark/60">
                    <span>Penjualan</span>
                    <div class="flex items-center gap-2">
                        <span class="{{ $kpiTahunan['penjualan']['naik'] ? 'text-green' : 'text-red-600' }} text-xs font-semibold">
                            <i class="fas {{ $kpiTahunan['penjualan']['naik'] ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ $kpiTahunan['penjualan']['mom'] }}% MoM
                        </span>
                        <button type="button" onclick="openKpiDetail('penjualan', 'Penjualan')" class="text-dark/30 hover:text-green" title="Detail"><i class="fas fa-circle-info"></i></button>
                    </div>
                </div>
                <div class="font-heading mt-1 text-xl font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['penjualan']['real']) }}</div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-dark/10">
                    <div class="h-2 {{ \App\Support\FormatHelper::barColorPos($kpiTahunan['penjualan']['capaian']) }}" style="width: {{ min(100, max(0, $kpiTahunan['penjualan']['capaian'])) }}%"></div>
                </div>
                <div class="mt-1 text-xs text-dark/50">{{ $kpiTahunan['penjualan']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['penjualan']['rkap']) }}</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between text-sm text-dark/60">
                    <span>Laba Kotor</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gold">GPM {{ $kpiTahunan['laba_kotor']['gpm'] }}%</span>
                        <button type="button" onclick="openKpiDetail('labkor', 'Laba Kotor')" class="text-dark/30 hover:text-green" title="Detail"><i class="fas fa-circle-info"></i></button>
                    </div>
                </div>
                <div class="font-heading mt-1 text-xl font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['laba_kotor']['real']) }}</div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-dark/10">
                    <div class="h-2 {{ \App\Support\FormatHelper::barColorPos($kpiTahunan['laba_kotor']['capaian']) }}" style="width: {{ min(100, max(0, $kpiTahunan['laba_kotor']['capaian'])) }}%"></div>
                </div>
                <div class="mt-1 text-xs text-dark/50">{{ $kpiTahunan['laba_kotor']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['laba_kotor']['rkap']) }}</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between text-sm text-dark/60">
                    <span>Laba Operasi</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-green">OPM {{ $kpiTahunan['laba_operasi']['opm'] }}%</span>
                        <button type="button" onclick="openKpiDetail('labops', 'Laba Operasi')" class="text-dark/30 hover:text-green" title="Detail"><i class="fas fa-circle-info"></i></button>
                    </div>
                </div>
                <div class="font-heading mt-1 text-xl font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['laba_operasi']['real']) }}</div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-dark/10">
                    <div class="h-2 {{ \App\Support\FormatHelper::barColorPos($kpiTahunan['laba_operasi']['capaian']) }}" style="width: {{ min(100, max(0, $kpiTahunan['laba_operasi']['capaian'])) }}%"></div>
                </div>
                <div class="mt-1 text-xs text-dark/50">{{ $kpiTahunan['laba_operasi']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['laba_operasi']['rkap']) }}</div>
            </div>

            <div class="rounded-xl bg-dark p-5 shadow-sm">
                <div class="flex items-center justify-between text-sm text-white/60">
                    <span>Laba Bersih</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gold">NPM {{ $kpiTahunan['laba_bersih']['npm'] }}%</span>
                        <button type="button" onclick="openKpiDetail('laba', 'Laba Bersih')" class="text-white/40 hover:text-white" title="Detail"><i class="fas fa-circle-info"></i></button>
                    </div>
                </div>
                <div class="font-heading mt-1 text-xl font-extrabold text-white">Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['laba_bersih']['real']) }}</div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white/10">
                    <div class="h-2 bg-gold" style="width: {{ min(100, max(0, $kpiTahunan['laba_bersih']['capaian'])) }}%"></div>
                </div>
                <div class="mt-1 text-xs text-white/50">{{ $kpiTahunan['laba_bersih']['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($kpiTahunan['laba_bersih']['rkap']) }}</div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-heading text-sm font-extrabold text-dark">Penjualan (Dalam Ribu)</h3>
                    <div class="flex items-center gap-3">
                        <select id="select-tahun-penjualan" onchange="location.href = '{{ route('dashboard') }}?tahun=' + this.value"
                            class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                            @foreach ($tahunOptions as $ty)
                                <option value="{{ $ty }}" @selected($ty === $tahun)>{{ $ty }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="openPenjualanDetail()"
                            class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">
                            <i class="fas fa-angle-up"></i> Detail
                        </button>
                    </div>
                </div>
                <canvas id="lineChart1" height="160"></canvas>
                <div class="mt-3 flex justify-end gap-4 text-xs text-dark/50">
                    <span><i class="fas fa-square text-green"></i> Real</span>
                    <span><i class="fas fa-square text-gold"></i> RKAP</span>
                    <span><i class="fas fa-square text-dark/40"></i> Thn Sbl</span>
                </div>
                <x-insight-box :items="$insightPenjualan" />
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-heading text-sm font-extrabold text-dark">Laba & Margin (Dalam Ribu)</h3>
                    <select id="selectLaba" onchange="loadLineChart()" class="rounded-lg border border-dark/15 px-2 py-1 text-sm">
                        <option value="labkor">Laba Kotor (Year To Date)</option>
                        <option value="labops">Laba Usaha (Year To Date)</option>
                        <option value="laba">Laba Bersih (Year To Date)</option>
                    </select>
                </div>
                <canvas id="lineChart" height="160"></canvas>
                <x-insight-box :items="$insightLaba" />
            </div>
        </div>
    </div>

    {{-- Ringkasan Piutang & Hutang --}}
    <div class="mt-8">
        <h2 class="font-heading mb-3 text-sm font-extrabold uppercase tracking-wide text-dark/50">Ringkasan Piutang &amp; Hutang</h2>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="font-heading text-sm font-extrabold text-dark">Piutang</h3>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="openPiutangDetail()" class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">Detail</button>
                        <span class="rounded-lg bg-dark px-3 py-1.5 text-sm font-bold text-white">Rp {{ \App\Support\FormatHelper::maskRp($totalPiutangAktif) }}</span>
                    </div>
                </div>
                <x-insight-box :items="$insightPiutang" />
                <div class="mt-4 divide-y divide-dark/5">
                    @forelse (array_slice($piutangJatuhTempo, 0, 5) as $row)
                        <button type="button" onclick="openAging('{{ trim($row->KODEREKANAN) }}')" class="flex w-full items-center justify-between py-2 text-left text-sm transition hover:bg-dark/5">
                            <span class="text-dark/70">{{ trim($row->NAMA) }}</span>
                            <span class="font-semibold text-red-600">Rp {{ \App\Support\FormatHelper::maskRp($row->NILAI) }}</span>
                        </button>
                    @empty
                        <p class="py-2 text-xs text-dark/40">Tidak ada piutang jatuh tempo.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="font-heading text-sm font-extrabold text-dark">Hutang</h3>
                    <button type="button" disabled title="Data belum tersedia" class="cursor-not-allowed rounded-lg border border-dark/10 px-3 py-1 text-xs text-dark/30">Detail</button>
                </div>
                <div class="mt-3 flex h-32 items-center justify-center rounded-lg border border-dashed border-dark/15 text-center text-xs text-dark/40">
                    Data hutang belum tersedia di sistem.
                </div>
            </div>
        </div>
    </div>

    {{-- Beban Pemasaran & Beban Umum Administrasi --}}
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Beban Pemasaran (Dalam Ribu)</h3>
                <button type="button" onclick="openBebanDetail('pemasaran')" class="text-xs font-semibold text-green hover:underline">Detail</button>
            </div>
            <canvas id="chart-beban-pemasaran" class="mt-3" height="160"></canvas>
            <x-insight-box :items="$insightBebanPemasaran" />
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Beban Umum &amp; Administrasi (Dalam Ribu)</h3>
                <button type="button" onclick="openBebanDetail('umum')" class="text-xs font-semibold text-green hover:underline">Detail</button>
            </div>
            <canvas id="chart-beban-umum" class="mt-3" height="160"></canvas>
            <x-insight-box :items="$insightBebanUmum" />
        </div>
    </div>

    {{-- Penjualan per Sektor (label tampilan vs kode_sektor database, lihat DASH.MST_RKAP_SEKTOR) --}}
    @php($sektorLabelMap = ['Subsidi' => 'subsidi', 'Non Subsidi' => 'non subsidi', 'Pestisida' => 'pestisida', 'Bahan Kimia' => 'bahan kimia', 'Kesuplieran' => 'kesuplieran', 'Jasa Angkut' => 'angkutan', 'Jasa Gudang' => 'gudang'])
    @php($sektorByKode = collect($sektorAchievement)->keyBy(fn ($row) => strtolower($row['kode_sektor'])))
    <div class="mt-8">
        <h2 class="font-heading mb-3 text-sm font-extrabold uppercase tracking-wide text-dark/50">Penjualan per Sektor · {{ $kpi['periode_label'] }}</h2>
        <x-insight-box :items="$insightSektor" />
        <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($sektorLabelMap as $sektorLabel => $kode)
                @php($row = $sektorByKode->get($kode))
                <div class="rounded-xl bg-white p-5 shadow-sm {{ $row ? 'cursor-pointer transition hover:shadow-md' : '' }}" @if ($row) onclick="openSektor('{{ $row['id_sektor'] }}', '{{ $row['kode_sektor'] }}')" @endif>
                    <h4 class="font-heading text-sm font-extrabold text-dark">{{ $sektorLabel }}</h4>
                    @if ($row)
                        <div class="font-heading mt-2 text-lg font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($row['real']) }}</div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-dark/10">
                            <div class="h-2 {{ \App\Support\FormatHelper::barColorPos($row['capaian']) }}" style="width: {{ min(100, max(0, $row['capaian'])) }}%"></div>
                        </div>
                        <div class="mt-1 text-xs text-dark/50">{{ $row['capaian'] }}% dari Rp {{ \App\Support\FormatHelper::maskRp($row['rkap']) }}</div>
                        <div class="mt-2 text-xs font-semibold text-green">Lihat detail &rarr;</div>
                    @else
                        <div class="mt-3 flex h-16 items-center justify-center rounded-lg border border-dashed border-dark/15 text-xs text-dark/30">
                            Belum ada RKAP sektor ini
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Penjualan & Beban per Wilayah --}}
    <div class="mt-8">
        <h2 class="font-heading mb-3 text-sm font-extrabold uppercase tracking-wide text-dark/50">Penjualan &amp; Beban per Wilayah · {{ $kpi['periode_label'] }}</h2>
        <x-insight-box :items="$insightWilayah" />
        <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">
            @foreach ($wilayahCards as $row)
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h4 class="font-heading text-sm font-extrabold text-dark">{{ $row['wilayah'] }}</h4>
                        <button type="button" onclick="openWilayahDetail('{{ $row['wilayah'] }}')" class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">Detail</button>
                    </div>

                    <p class="mt-3 text-xs uppercase tracking-wide text-dark/40">Realisasi Penjualan</p>
                    <div class="font-heading text-lg font-extrabold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($row['real']) }}</div>

                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-dark/10 pt-3 text-xs">
                        <div>
                            <dt class="text-dark/40">Beban Pemasaran</dt>
                            <dd class="font-semibold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($row['beban_pemasaran']) }}</dd>
                        </div>
                        <div>
                            <dt class="text-dark/40">Beban Umum &amp; Adm.</dt>
                            <dd class="font-semibold text-dark">Rp {{ \App\Support\FormatHelper::maskRp($row['beban_umum']) }}</dd>
                        </div>
                    </div>
                </div>
            @endforeach
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

    {{-- Modal: detail beban per wilayah --}}
    <dialog id="modal-beban" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 id="modal-beban-title" class="font-heading font-extrabold text-dark">Detail Beban</h3>
            <button type="button" onclick="document.getElementById('modal-beban').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-beban-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    {{-- Modal: detail KPI tahunan (Penjualan / Laba Kotor / Laba Operasi / Laba Bersih) --}}
    <dialog id="modal-kpi-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 id="modal-kpi-detail-title" class="font-heading font-extrabold text-dark">Detail</h3>
            <button type="button" onclick="document.getElementById('modal-kpi-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-kpi-detail-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    {{-- Modal: detail Penjualan (Bulanan / Per Sektor / Per Wilayah) --}}
    <dialog id="modal-penjualan-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail Penjualan</h3>
            <button type="button" onclick="document.getElementById('modal-penjualan-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div class="flex gap-1 border-b border-dark/10 px-5 pt-3">
            <button type="button" id="tabbtn-pj-bulanan" onclick="switchPenjualanTab('bulanan')" class="penjualan-tab-btn border-b-2 px-3 py-2 text-xs font-semibold transition">Bulanan</button>
            <button type="button" id="tabbtn-pj-sektor" onclick="switchPenjualanTab('sektor')" class="penjualan-tab-btn border-b-2 px-3 py-2 text-xs font-semibold transition">Per Sektor</button>
            <button type="button" id="tabbtn-pj-wilayah" onclick="switchPenjualanTab('wilayah')" class="penjualan-tab-btn border-b-2 px-3 py-2 text-xs font-semibold transition">Per Wilayah</button>
        </div>
        <div id="tab-pj-bulanan" class="max-h-[60vh] overflow-y-auto p-5 text-sm"></div>
        <div id="tab-pj-sektor" class="hidden max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
        <div id="tab-pj-wilayah" class="hidden max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    {{-- Modal: detail seluruh piutang jatuh tempo --}}
    <dialog id="modal-piutang-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Seluruh Piutang Jatuh Tempo</h3>
            <button type="button" onclick="document.getElementById('modal-piutang-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-piutang-detail-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    {{-- Modal: detail perbandingan wilayah --}}
    <dialog id="modal-wilayah-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 id="modal-wilayah-detail-title" class="font-heading font-extrabold text-dark">Detail Wilayah</h3>
            <button type="button" onclick="document.getElementById('modal-wilayah-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-wilayah-detail-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>
@endsection


@push('scripts')
    <script>
        function openPenjualanHariIni() {
            const modal = document.getElementById('modal-penjualan-hari');
            const body = document.getElementById('modal-penjualan-hari-body');
            modal.showModal();
            body.innerHTML = 'Memuat...';

            axios.post('{{ route('dashboard.penjHari') }}').then(({ data }) => {
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

            axios.post('{{ route('dashboard.detail_aging') }}', { korek: koderekanan }).then(({ data }) => {
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
            axios.post('{{ route('dashboard.detail2') }}', { nofaktur }).then(({ data }) => {
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

            axios.post('{{ route('dashboard.detail1') }}', { idSektor1: idSektor, periode: '{{ $periode }}' }).then(({ data }) => {
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

        function openKpiDetail(key, label) {
            const modal = document.getElementById('modal-kpi-detail');
            const series = { penjualan: penjualanTahunan, labkor: labaKotorTahunan, labops: labaOperasiTahunan, laba: labaBersihTahunan }[key];
            document.getElementById('modal-kpi-detail-title').textContent = 'Detail ' + label + ' per Bulan';
            modal.showModal();

            const rows = monthLabels.map((bulan, i) => `<tr><td class="py-1.5">${bulan}</td><td class="py-1.5 text-right">${Number(series.real[i] ?? 0).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(series.rkap[i] ?? 0).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(series.sbl[i] ?? 0).toLocaleString('id-ID')}</td></tr>`).join('');
            document.getElementById('modal-kpi-detail-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                <th class="py-1">Bulan</th><th class="py-1 text-right">Real</th><th class="py-1 text-right">RKAP</th><th class="py-1 text-right">Thn Sbl</th></tr></thead><tbody class="divide-y divide-dark/5">${rows}</tbody></table>`;
        }

        function openPiutangDetail() {
            const modal = document.getElementById('modal-piutang-detail');
            modal.showModal();

            if (!piutangJatuhTempoData.length) {
                document.getElementById('modal-piutang-detail-body').innerHTML = '<p class="text-dark/40">Tidak ada piutang jatuh tempo.</p>';
                return;
            }
            const rows = piutangJatuhTempoData.map(r => `<tr><td class="py-1.5"><button class="text-green underline" onclick="document.getElementById('modal-piutang-detail').close(); openAging('${r.KODEREKANAN.trim()}')">${r.NAMA}</button></td><td class="py-1.5 text-right text-red-600">${Number(r.NILAI).toLocaleString('id-ID')}</td></tr>`).join('');
            document.getElementById('modal-piutang-detail-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                <th class="py-1">Customer</th><th class="py-1 text-right">Piutang Jatuh Tempo</th></tr></thead><tbody class="divide-y divide-dark/5">${rows}</tbody></table>`;
        }

        function openWilayahDetail(wilayah) {
            const modal = document.getElementById('modal-wilayah-detail');
            document.getElementById('modal-wilayah-detail-title').textContent = 'Detail Wilayah · ' + wilayah;
            modal.showModal();

            const row = wilayahCardsData.find(r => r.wilayah === wilayah);
            if (!row) {
                document.getElementById('modal-wilayah-detail-body').innerHTML = '<p class="text-dark/40">Data tidak ditemukan.</p>';
                return;
            }

            const totalBeban = Number(row.beban_pemasaran) + Number(row.beban_umum);
            const rasio = Number(row.real) > 0 ? ((totalBeban / Number(row.real)) * 100).toFixed(1) : '0.0';

            document.getElementById('modal-wilayah-detail-body').innerHTML = `
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2 border-b border-dark/10 pb-3">
                        <dt class="text-xs uppercase tracking-wide text-dark/40">Realisasi Penjualan</dt>
                        <dd class="font-heading text-lg font-extrabold text-dark">Rp ${Number(row.real).toLocaleString('id-ID')}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-dark/40">Beban Pemasaran</dt>
                        <dd class="font-semibold text-dark">Rp ${Number(row.beban_pemasaran).toLocaleString('id-ID')}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-dark/40">Beban Umum &amp; Adm.</dt>
                        <dd class="font-semibold text-dark">Rp ${Number(row.beban_umum).toLocaleString('id-ID')}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-dark/40">Total Beban</dt>
                        <dd class="font-semibold text-dark">Rp ${totalBeban.toLocaleString('id-ID')}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-dark/40">Rasio Beban thd Penjualan</dt>
                        <dd class="font-semibold text-dark">${rasio}%</dd>
                    </div>
                </dl>`;
        }

        function openBebanDetail(jenis) {
            const modal = document.getElementById('modal-beban');
            const key = jenis === 'pemasaran' ? 'beban_pemasaran' : 'beban_umum';
            document.getElementById('modal-beban-title').textContent = jenis === 'pemasaran' ? 'Detail Beban Pemasaran per Wilayah' : 'Detail Beban Umum & Administrasi per Wilayah';
            modal.showModal();

            const rows = wilayahCardsData.map(r => `<tr><td class="py-1.5">${r.wilayah}</td><td class="py-1.5 text-right">${Number(r[key]).toLocaleString('id-ID')}</td></tr>`).join('');
            document.getElementById('modal-beban-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                <th class="py-1">Wilayah</th><th class="py-1 text-right">Nilai (Dalam Ribu)</th></tr></thead><tbody class="divide-y divide-dark/5">${rows}</tbody></table>`;
        }

        // ── Detail Penjualan (Bulanan / Per Sektor / Per Wilayah) ────────────────
        let penjualanBreakdownLoaded = false;

        function openPenjualanDetail() {
            document.getElementById('modal-penjualan-detail').showModal();
            renderPenjualanBulanan();
            switchPenjualanTab('bulanan');

            if (!penjualanBreakdownLoaded) {
                penjualanBreakdownLoaded = true;
                axios.post(PENJUALAN_BREAKDOWN_URL, { tahun: CURRENT_TAHUN }).then(({ data }) => {
                    renderSubGroupTable('tab-pj-sektor', data.sektor);
                    renderSubGroupTable('tab-pj-wilayah', data.wilayah);
                });
            }
        }

        function renderPenjualanBulanan() {
            const rows = monthLabels.map((bulan, i) => `<tr><td class="py-1.5">${bulan}</td><td class="py-1.5 text-right">${Number(penjualanTahunan.real[i] ?? 0).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(penjualanTahunan.rkap[i] ?? 0).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${Number(penjualanTahunan.sbl[i] ?? 0).toLocaleString('id-ID')}</td></tr>`).join('');
            document.getElementById('tab-pj-bulanan').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                <th class="py-1">Bulan</th><th class="py-1 text-right">Real</th><th class="py-1 text-right">RKAP</th><th class="py-1 text-right">Thn Sbl</th></tr></thead><tbody class="divide-y divide-dark/5">${rows}</tbody></table>`;
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
                return `<tr><td class="py-1.5">${r.SUB_GROUP}</td><td class="py-1.5 text-right">${Number(r.AMOUNT).toLocaleString('id-ID')}</td><td class="py-1.5 text-right">${pct}%</td></tr>`;
            }).join('');
            el.innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                <th class="py-1">Kelompok</th><th class="py-1 text-right">Nilai (Dalam Ribu)</th><th class="py-1 text-right">%</th></tr></thead><tbody class="divide-y divide-dark/5">${body}</tbody></table>`;
        }

        function switchPenjualanTab(tab) {
            ['bulanan', 'sektor', 'wilayah'].forEach(t => {
                document.getElementById('tab-pj-' + t).classList.toggle('hidden', t !== tab);
                const btn = document.getElementById('tabbtn-pj-' + t);
                btn.classList.toggle('text-green', t === tab);
                btn.classList.toggle('border-green', t === tab);
                btn.classList.toggle('text-dark/40', t !== tab);
                btn.classList.toggle('border-transparent', t !== tab);
            });
        }

        const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const CURRENT_TAHUN = {{ $tahun }};
        const PENJUALAN_BREAKDOWN_URL = "{{ route('dashboard.penjualan_breakdown') }}";
        const penjualanTahunan = @json($penjualanTahunan);
        const labaKotorTahunan = @json($labaKotorTahunan);
        const labaOperasiTahunan = @json($labaOperasiTahunan);
        const labaBersihTahunan = @json($labaBersihTahunan);
        const bebanPemasaranTahunan = @json($bebanPemasaranTahunan);
        const bebanUmumTahunan = @json($bebanUmumTahunan);
        const wilayahCardsData = @json($wilayahCards);
        const piutangJatuhTempoData = @json($piutangJatuhTempo);

        function buildBebanChart(canvasId, series) {
            new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [
                        { label: 'Real', data: series.real, backgroundColor: '#2F6C3F' },
                        { label: 'RKAP', data: series.rkap, backgroundColor: '#DAA628' },
                        { label: 'Thn Sbl', data: series.sbl, backgroundColor: 'rgba(15, 38, 31, 0.4)' },
                    ],
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
            });
        }

        let lineChart1, lineChart;

        function marginOf(labaReal) {
            return labaReal.map((v, i) => {
                const penjualan = parseFloat(penjualanTahunan.real[i]);
                return penjualan ? (parseFloat(v) / penjualan) * 100 : 0;
            });
        }

        function buildLineChart(real, rkap, sbl, margin) {
            if (lineChart) lineChart.destroy();
            lineChart = new Chart(document.getElementById('lineChart'), {
                data: {
                    labels: monthLabels,
                    datasets: [
                        { type: 'line', label: 'Margin (%)', data: margin, borderColor: '#DC2626', yAxisID: 'y1', fill: false },
                        { type: 'bar', label: 'Real', data: real, backgroundColor: '#2F6C3F', yAxisID: 'y' },
                        { type: 'bar', label: 'RKAP', data: rkap, backgroundColor: '#DAA628', yAxisID: 'y' },
                        { type: 'bar', label: 'Thn Sbl', data: sbl, backgroundColor: 'rgba(15, 38, 31, 0.4)', yAxisID: 'y' },
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
        }

        function loadLineChart() {
            const opt = document.getElementById('selectLaba').value;
            const series = { labkor: labaKotorTahunan, labops: labaOperasiTahunan, laba: labaBersihTahunan }[opt];
            buildLineChart(series.real, series.rkap, series.sbl, marginOf(series.real));
        }

        document.addEventListener('DOMContentLoaded', function () {
            buildBebanChart('chart-beban-pemasaran', bebanPemasaranTahunan);
            buildBebanChart('chart-beban-umum', bebanUmumTahunan);

            lineChart1 = new Chart(document.getElementById('lineChart1'), {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [
                        { label: 'Real', data: penjualanTahunan.real, backgroundColor: '#2F6C3F' },
                        { label: 'RKAP', data: penjualanTahunan.rkap, backgroundColor: '#DAA628' },
                        { label: 'Thn Sbl', data: penjualanTahunan.sbl, backgroundColor: 'rgba(15, 38, 31, 0.4)' },
                    ],
                },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });

            buildLineChart(labaKotorTahunan.real, labaKotorTahunan.rkap, labaKotorTahunan.sbl, marginOf(labaKotorTahunan.real));
        });
    </script>
@endpush

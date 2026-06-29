@extends('layouts.app')

@section('title', 'Aging Piutang')

@section('content')
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Total Piutang Aktif</h3>
                <span class="rounded-lg bg-dark px-4 py-2 font-heading font-bold text-white">
                    Rp {{ \App\Support\FormatHelper::maskRp($totalOpenAmount) }}
                </span>
            </div>
            <canvas id="chart-sektor" class="mt-4" height="100"></canvas>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h3 class="font-heading mb-3 text-sm font-extrabold text-dark">Detail Aging Piutang Berdasarkan Sektor</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Angkutan' => 'fa-truck', 'Bahan Kimia' => 'fa-flask', 'Gudang' => 'fa-university', 'Kesuplieran' => 'fa-cogs', 'Non Subsidi' => 'fa-cubes', 'Pestisida' => 'fa-bug', 'Subsidi' => 'fa-cube'] as $sektor => $icon)
                        <button type="button" onclick="openSektor('{{ $sektor }}')" class="flex flex-col items-center rounded-lg border border-dark/10 px-4 py-2 text-xs text-dark/70 transition hover:bg-green hover:text-white">
                            <i class="fas {{ $icon }} mb-1 text-base"></i>{{ $sektor }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm">
                <h3 class="font-heading mb-3 text-sm font-extrabold text-dark">Detail Aging Piutang Berdasarkan Hari Jatuh Tempo</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach (['bjt' => 'Blm Jth Tempo', '1_30' => '1-30', '31_60' => '31-60', '61_90' => '61-90', '91_365' => '90-365', '365' => '>365'] as $key => $label)
                        <button type="button" onclick="openJatuhTempo('{{ $key }}', '{{ $label }}')" class="rounded-lg border border-dark/10 px-4 py-2 text-xs text-dark/70 transition hover:bg-gold hover:text-dark">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <select id="optionSearch" class="rounded-lg border border-dark/15 px-3 py-2 text-sm">
                    <option value="customer">Cari Berdasarkan Customer</option>
                    <option value="wilayah">Cari Berdasarkan Wilayah</option>
                </select>
                <input type="text" id="form-pencarian" oninput="searchAging()" placeholder="Ketik untuk mencari..." class="flex-1 rounded-lg border border-dark/15 px-3 py-2 text-sm">
            </div>

            <div id="search-result" class="hidden overflow-x-auto"></div>

            <div id="customer-accordion" class="divide-y divide-dark/5">
                @foreach ($customers as $row)
                    <details class="group py-1">
                        <summary onclick="loadCustomerDetail('{{ $row->KODEREKANAN }}')" class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm transition hover:bg-dark/5">
                            <span class="font-medium text-green">{{ $row->NAMA }}</span>
                            <span class="font-semibold text-red-600">{{ \App\Support\FormatHelper::maskRp($row->PIUTANG) }}</span>
                        </summary>
                        <div class="overflow-x-auto px-3 py-2">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-left text-dark/40">
                                        <th class="py-1">No Faktur</th><th class="py-1">Wilayah</th><th class="py-1">Tgl Jth Tempo</th>
                                        <th class="py-1 text-right">Blm JT</th><th class="py-1 text-right">1-30</th><th class="py-1 text-right">31-60</th>
                                        <th class="py-1 text-right">61-90</th><th class="py-1 text-right">91-365</th><th class="py-1 text-right">&gt;365</th>
                                    </tr>
                                </thead>
                                <tbody id="customer-body-{{ $row->KODEREKANAN }}" class="divide-y divide-dark/5">
                                    <tr><td colspan="9" class="py-2 text-dark/30">Memuat...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Modal: faktur detail --}}
    <dialog id="modal-faktur" class="w-full max-w-3xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Detail Piutang</h3>
            <button type="button" onclick="document.getElementById('modal-faktur').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-faktur-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>

    {{-- Modal: jatuh tempo / sektor list --}}
    <dialog id="modal-list" class="w-full max-w-4xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 id="modal-list-title" class="font-heading font-extrabold text-dark">Detail Piutang</h3>
            <button type="button" onclick="document.getElementById('modal-list').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-list-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>
@endsection

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@push('scripts')
    <script>
        const sektorChart = @json($sektorChart);

        new Chart(document.getElementById('chart-sektor'), {
            type: 'bar',
            data: {
                labels: sektorChart.map(r => r.SEKTOR),
                datasets: [
                    { label: 'Blm Jth Tempo', data: sektorChart.map(r => r.BLM_JTHTEMPO), backgroundColor: '#0F261F' },
                    { label: '1-30', data: sektorChart.map(r => r.JTH1_30), backgroundColor: '#2F6C3F' },
                    { label: '31-60', data: sektorChart.map(r => r.JTH31_60), backgroundColor: '#3C8A51' },
                    { label: '61-90', data: sektorChart.map(r => r.JTH61_90), backgroundColor: '#DAA628' },
                    { label: '91-365', data: sektorChart.map(r => r.JTH91_365), backgroundColor: '#F6D30F' },
                    { label: '>365', data: sektorChart.map(r => r.JTH365), backgroundColor: '#ef4444' },
                ],
            },
            options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true } } },
        });

        function rupiah(v) { return Number(v).toLocaleString('id-ID'); }

        function loadCustomerDetail(koderekanan) {
            const body = document.getElementById('customer-body-' + koderekanan);
            if (body.dataset.loaded) return;
            body.dataset.loaded = '1';

            axios.post('{{ route('agingpiut.detail_aging') }}', { korek: koderekanan }).then(({ data }) => {
                body.innerHTML = data.map(r => `<tr>
                    <td class="py-1"><button class="text-green underline" onclick="openFaktur('${r.FAKTUR_DETAIL}')">${r.NOFAKTUR}</button></td>
                    <td class="py-1">${r.WILAYAH}</td><td class="py-1">${r.TGL_JTEMPO}</td>
                    <td class="py-1 text-right">${rupiah(r.BLM_JTEMPO)}</td><td class="py-1 text-right">${rupiah(r.JTH1_30)}</td><td class="py-1 text-right">${rupiah(r.JTH31_60)}</td>
                    <td class="py-1 text-right">${rupiah(r.JTH61_90)}</td><td class="py-1 text-right">${rupiah(r.JTH91_365)}</td><td class="py-1 text-right">${rupiah(r.JTH365)}</td>
                    </tr>`).join('') || '<tr><td colspan="9" class="py-2 text-dark/30">Tidak ada data.</td></tr>';
            });
        }

        function openFaktur(nofaktur) {
            const modal = document.getElementById('modal-faktur');
            const body = document.getElementById('modal-faktur-body');
            modal.showModal();
            body.innerHTML = 'Memuat...';

            axios.post('{{ route('agingpiut.detail2') }}', { nofaktur }).then(({ data }) => {
                if (!data.header) { body.innerHTML = '<p class="text-dark/40">Detail tidak ditemukan.</p>'; return; }
                const h = data.header;
                let html = `<div class="mb-4 grid grid-cols-2 gap-2 text-sm">
                    <div><span class="text-dark/40">No. FPB</span><br><strong>${h.NOMOR_FPB}</strong></div>
                    <div><span class="text-dark/40">Pelanggan</span><br><strong>${h.NAMA}</strong></div>
                    <div><span class="text-dark/40">Tanggal</span><br><strong>${h.TGL_FPB}</strong></div>
                    <div><span class="text-dark/40">Jatuh Tempo</span><br><strong>${h.TGL_JTEMPO}</strong></div>
                    <div><span class="text-dark/40">Nilai Total</span><br><strong>${rupiah(h.NILAI_TOTAL)}</strong></div>
                    <div><span class="text-dark/40">Syarat Bayar</span><br><strong>${h.SYARAT_PEMBAYARAN}</strong></div>
                </div>`;
                html += `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Keterangan</th><th class="py-1 text-right">Qty</th><th class="py-1 text-right">Nilai</th></tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.detail.map(d => `<tr><td class="py-1.5">${d.KETERANGAN}</td><td class="py-1.5 text-right">${d.QTY_JUAL} ${d.SATUAN}</td><td class="py-1.5 text-right">${rupiah(d.NILAI)}</td></tr>`).join('') +
                    `</tbody></table>`;
                body.innerHTML = html;
            });
        }

        function renderListTable(rows, withJatuhTempo) {
            if (!rows.length) return '<p class="text-dark/40">Tidak ada data.</p>';
            const head = withJatuhTempo
                ? `<th class="py-1">No Faktur</th><th class="py-1">Customer</th><th class="py-1">Wilayah</th><th class="py-1">Tgl Jth Tempo</th><th class="py-1 text-right">Blm JT</th><th class="py-1 text-right">1-30</th><th class="py-1 text-right">31-60</th><th class="py-1 text-right">61-90</th><th class="py-1 text-right">91-365</th><th class="py-1 text-right">&gt;365</th>`
                : `<th class="py-1">No Faktur</th><th class="py-1">Customer</th><th class="py-1">Wilayah</th><th class="py-1">Tgl Jth Tempo</th><th class="py-1 text-right">Nilai</th>`;
            const body = rows.map(r => withJatuhTempo
                ? `<tr><td class="py-1"><button class="text-green underline" onclick="openFaktur('${r.FAKTUR_DETAIL}')">${r.NOFAKTUR}</button></td><td class="py-1">${r.NAMA}</td><td class="py-1">${r.WILAYAH}</td><td class="py-1">${r.TGL_JTEMPO}</td><td class="py-1 text-right">${rupiah(r.BLM_JTEMPO)}</td><td class="py-1 text-right">${rupiah(r.JTH1_30)}</td><td class="py-1 text-right">${rupiah(r.JTH31_60)}</td><td class="py-1 text-right">${rupiah(r.JTH61_90)}</td><td class="py-1 text-right">${rupiah(r.JTH91_365)}</td><td class="py-1 text-right">${rupiah(r.JTH365)}</td></tr>`
                : `<tr><td class="py-1"><button class="text-green underline" onclick="openFaktur('${r.FAKTUR_DETAIL}')">${r.NOFAKTUR}</button></td><td class="py-1">${r.NAMA}</td><td class="py-1">${r.WILAYAH}</td><td class="py-1">${r.TGL_JTEMPO}</td><td class="py-1 text-right">${rupiah(r.NILAI)}</td></tr>`
            ).join('');
            return `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">${head}</tr></thead><tbody class="divide-y divide-dark/5">${body}</tbody></table>`;
        }

        function openJatuhTempo(key, label) {
            const modal = document.getElementById('modal-list');
            document.getElementById('modal-list-title').textContent = 'Detail Piutang · ' + label;
            modal.showModal();
            document.getElementById('modal-list-body').innerHTML = 'Memuat...';

            axios.post('{{ route('agingpiut.detail3') }}', { xval: key }).then(({ data }) => {
                document.getElementById('modal-list-body').innerHTML = renderListTable(data, false);
            });
        }

        function openSektor(sektor) {
            const modal = document.getElementById('modal-list');
            document.getElementById('modal-list-title').textContent = 'Detail Piutang Sektor · ' + sektor;
            modal.showModal();
            document.getElementById('modal-list-body').innerHTML = 'Memuat...';

            axios.post('{{ route('agingpiut.detail5') }}', { sektor }).then(({ data }) => {
                document.getElementById('modal-list-body').innerHTML = renderListTable(data, true);
            });
        }

        let searchTimer = null;
        function searchAging() {
            clearTimeout(searchTimer);
            const pencarian = document.getElementById('form-pencarian').value.trim();
            const kategori = document.getElementById('optionSearch').value;
            const resultBox = document.getElementById('search-result');
            const accordion = document.getElementById('customer-accordion');

            if (!pencarian) {
                resultBox.classList.add('hidden');
                accordion.classList.remove('hidden');
                return;
            }

            searchTimer = setTimeout(() => {
                axios.post('{{ route('agingpiut.detail4') }}', { kategori, pencarian }).then(({ data }) => {
                    accordion.classList.add('hidden');
                    resultBox.classList.remove('hidden');

                    if (kategori === 'customer') {
                        resultBox.innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                            <th class="py-1">Kode</th><th class="py-1">Customer</th><th class="py-1 text-right">Piutang</th></tr></thead><tbody class="divide-y divide-dark/5">` +
                            data.map(r => `<tr><td class="py-1.5">${r.KODEREKANAN}</td><td class="py-1.5">${r.NAMA}</td><td class="py-1.5 text-right">${r.PIUTANG}</td></tr>`).join('') +
                            `</tbody></table>`;
                    } else {
                        resultBox.innerHTML = renderListTable(data, true);
                    }
                });
            }, 350);
        }
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Cash In')
@section('subtitle', \App\Support\FormatHelper::getPeriodeTrans(\App\Support\FormatHelper::periodeTrans()))

@section('content')
    <x-insight-box :items="$insightCash" class="mb-6" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="rounded-xl bg-white p-5 shadow-sm lg:col-span-5">
            <h3 class="font-heading mb-3 text-sm font-extrabold text-dark">Cash In per Wilayah</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                        <th class="px-3 py-2">Wilayah</th>
                        <th class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::getPeriodeTrans(\App\Support\FormatHelper::periodeTrans()) }}</th>
                        <th class="px-3 py-2 text-right">s.d {{ \App\Support\FormatHelper::getPeriodeTrans(\App\Support\FormatHelper::periodeTrans()) }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark/5">
                    @php($jumlah = 0)
                    @php($jumlah1 = 0)
                    @forelse ($cashWilayah as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row->WILAYAH }}</td>
                            <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::mask($row->NILAI) }}</td>
                            <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::mask($row->SDBLN) }}</td>
                        </tr>
                        @php($jumlah += $row->NILAI)
                        @php($jumlah1 += $row->SDBLN)
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-center text-dark/40">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
                @if (count($cashWilayah))
                    <tfoot>
                        <tr class="bg-gold/10 font-semibold">
                            <td class="px-3 py-2 text-center">Jumlah</td>
                            <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::mask($jumlah) }}</td>
                            <td class="px-3 py-2 text-right">{{ \App\Support\FormatHelper::mask($jumlah1) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm lg:col-span-7">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Piutang vs Cash In per Customer (Top 5)</h3>
                <button type="button" onclick="openDetail()" class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">
                    <i class="fas fa-angle-up"></i> Detail
                </button>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                        <th class="px-3 py-2" rowspan="2">Kode</th><th class="px-3 py-2" rowspan="2">Customer</th><th class="px-3 py-2 text-center">Piutang Jth Tempo</th><th class="px-3 py-2 text-center">Cash In</th>
                    </tr>
                    <tr class="bg-dark/5 text-center text-xs uppercase text-dark/50">
                        <th class="px-3 py-1">Bln Ini</th><th class="px-3 py-1">Bln Ini</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark/5">
                    @forelse ($cashCustomer as $row)
                        <tr>
                            <td class="px-3 py-2">{{ trim($row->KODEREKANAN) }}</td>
                            <td class="px-3 py-2">{{ $row->NAMA }}</td>
                            <td class="px-3 py-2 text-right text-red-600">{{ \App\Support\FormatHelper::mask($row->JTEMPO) }}</td>
                            <td class="px-3 py-2 text-right font-medium">{{ \App\Support\FormatHelper::mask($row->NILAI) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-center text-dark/40">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <dialog id="modal-detail" class="w-full max-w-4xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">Piutang Jatuh Tempo vs Cash In per Customer</h3>
            <button type="button" onclick="document.getElementById('modal-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-detail-body" class="max-h-[60vh] overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>
@endsection

@push('scripts')
    <script>
        function rupiah(v) { return Number(v).toLocaleString('id-ID'); }

        function openDetail() {
            const modal = document.getElementById('modal-detail');
            modal.showModal();
            document.getElementById('modal-detail-body').innerHTML = 'Memuat...';

            axios.post('{{ route('cash.detail1') }}').then(({ data }) => {
                document.getElementById('modal-detail-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Kode</th><th class="py-1">Customer</th><th class="py-1 text-right">Total Piutang</th><th class="py-1 text-right">Jth Tempo</th><th class="py-1 text-right">Cash In</th></tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map(r => `<tr><td class="py-1.5">${r.KODE}</td><td class="py-1.5">${r.NAMA}</td><td class="py-1.5 text-right">${rupiah(r.TOT_PIUTANG)}</td><td class="py-1.5 text-right">${rupiah(r.JTEMPO)}</td><td class="py-1.5 text-right">${rupiah(r.NILAI)}</td></tr>`).join('') +
                    `</tbody></table>`;
            });
        }
    </script>
@endpush

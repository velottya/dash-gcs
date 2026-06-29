@extends('layouts.app')

@section('title', 'Cash In per Sektor')
@section('subtitle', \App\Support\FormatHelper::getPeriodeTrans(\App\Support\FormatHelper::periodeTrans()))

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="font-heading mb-3 text-sm font-extrabold text-dark">Cash In per Wilayah</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                        <th class="px-3 py-2">Kode CC</th><th class="px-3 py-2">Wilayah</th><th class="px-3 py-2 text-right">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark/5">
                    @forelse ($cashWilayah as $row)
                        <tr>
                            <td class="px-3 py-2">{{ trim($row->KODE_CC) }}</td>
                            <td class="px-3 py-2">{{ $row->WILAYAH }}</td>
                            <td class="px-3 py-2 text-right font-medium">{{ \App\Support\FormatHelper::mask($row->NILAI) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-center text-dark/40">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-heading text-sm font-extrabold text-dark">Top 3 Customer Jatuh Tempo</h3>
                <button type="button" onclick="openDetail()" class="rounded-lg border border-green/30 px-3 py-1 text-xs text-green transition hover:bg-green hover:text-white">
                    Lihat Semua
                </button>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                        <th class="px-3 py-2">Customer</th><th class="px-3 py-2 text-right">Cash In</th><th class="px-3 py-2 text-right">Jth Tempo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark/5">
                    @forelse ($cashCustomer as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row->NAMA }}</td>
                            <td class="px-3 py-2 text-right font-medium">{{ \App\Support\FormatHelper::mask($row->NILAI) }}</td>
                            <td class="px-3 py-2 text-right text-red-600">{{ \App\Support\FormatHelper::mask($row->JTEMPO) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-center text-dark/40">Tidak ada data.</td></tr>
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

            axios.post('{{ route('sektor.detail1') }}').then(({ data }) => {
                document.getElementById('modal-detail-body').innerHTML = `<table class="w-full text-sm"><thead><tr class="text-left text-xs uppercase text-dark/40">
                    <th class="py-1">Kode</th><th class="py-1">Customer</th><th class="py-1 text-right">Total Piutang</th><th class="py-1 text-right">Jth Tempo</th><th class="py-1 text-right">Cash In</th><th class="py-1">Status</th></tr></thead><tbody class="divide-y divide-dark/5">` +
                    data.map(r => `<tr><td class="py-1.5">${r.KODE}</td><td class="py-1.5">${r.NAMA}</td><td class="py-1.5 text-right">${rupiah(r.TOT_PIUTANG)}</td><td class="py-1.5 text-right">${rupiah(r.JTEMPO)}</td><td class="py-1.5 text-right">${rupiah(r.NILAI)}</td><td class="py-1.5"><span class="rounded-full px-2 py-0.5 text-xs font-semibold ${r.STATUS === 'Cukup' ? 'bg-green/10 text-green' : 'bg-red-100 text-red-600'}">${r.STATUS}</span></td></tr>`).join('') +
                    `</tbody></table>`;
            });
        }
    </script>
@endpush

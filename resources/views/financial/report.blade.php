@extends('layouts.app')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <div class="flex items-center justify-end">
            <button type="button" onclick="openFinancialDetail()" class="text-xs font-semibold text-green hover:underline">Detail per Bulan</button>
        </div>
        <canvas id="chart-{{ $chartId }}" height="120"></canvas>
        <div class="mt-4 flex flex-wrap justify-end gap-4 text-sm text-dark/60">
            @foreach ($legend as $item)
                <span><i class="fas fa-square" style="color: {{ $item['color'] }}"></i> {{ $item['label'] }}</span>
            @endforeach
        </div>
        <x-insight-box :items="$insights ?? []" />
    </div>

    {{-- Modal: detail per bulan --}}
    <dialog id="modal-financial-detail" class="w-full max-w-4xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading font-extrabold text-dark">{{ $title }} · Detail per Bulan</h3>
            <button type="button" onclick="document.getElementById('modal-financial-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <div id="modal-financial-detail-body" class="max-h-[60vh] overflow-x-auto overflow-y-auto p-5 text-sm">Memuat...</div>
    </dialog>
@endsection


@push('scripts')
    @php
        $datasets = array_map(fn ($item) => ['type' => $item['type'] ?? 'bar', 'label' => $item['label'], 'data' => array_map('floatval', $series[$item['key']] ?? []), 'backgroundColor' => $item['color'], 'borderColor' => $item['color'], 'fill' => false], $legend);
    @endphp
    <script>
        const monthLabelsFinancial = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        const financialDatasets = @json($datasets);

        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('chart-{{ $chartId }}'), {
                data: {
                    labels: monthLabelsFinancial,
                    datasets: financialDatasets,
                },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });
        });

        function openFinancialDetail() {
            const modal = document.getElementById('modal-financial-detail');
            modal.showModal();

            const head = '<th class="py-1 text-left">Bulan</th>' + financialDatasets.map(d => `<th class="py-1 text-right">${d.label}</th>`).join('');
            const rows = monthLabelsFinancial.map((bulan, i) => '<tr><td class="py-1.5 text-left">' + bulan + '</td>' +
                financialDatasets.map(d => `<td class="py-1.5 text-right">${Number(d.data[i] ?? 0).toLocaleString('id-ID')}</td>`).join('') + '</tr>').join('');

            document.getElementById('modal-financial-detail-body').innerHTML =
                `<table class="w-full text-sm"><thead><tr class="text-xs uppercase text-dark/40">${head}</tr></thead><tbody class="divide-y divide-dark/5">${rows}</tbody></table>`;
        }
    </script>
@endpush

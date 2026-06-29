@extends('layouts.app')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <canvas id="chart-{{ $chartId }}" height="120"></canvas>
        <div class="mt-4 flex justify-end gap-4 text-sm text-dark/60">
            @foreach ($legend as $item)
                <span><i class="fas fa-square {{ $item['color'] }}"></i> {{ $item['label'] }}</span>
            @endforeach
        </div>
    </div>
@endsection

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@push('scripts')
    <script>
        new Chart(document.getElementById('chart-{{ $chartId }}'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: @json(array_map(fn ($item) => ['label' => $item['label'], 'data' => [], 'backgroundColor' => str_contains($item['color'], 'green') ? '#2F6C3F' : (str_contains($item['color'], 'gold') ? '#DAA628' : '#ef4444')], $legend)),
            },
            options: { responsive: true, plugins: { legend: { display: false } } },
        });
    </script>
@endpush

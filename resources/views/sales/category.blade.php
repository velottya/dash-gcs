@extends('layouts.app')

@section('title', $title)
@section('subtitle', 'Dalam Juta')

@section('content')
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <canvas id="chart-{{ $chartId }}" height="120"></canvas>
        <div class="mt-4 flex justify-end gap-4 text-sm text-dark/60">
            <span><i class="fas fa-square text-green"></i> Real</span>
            <span><i class="fas fa-square text-gold"></i> RKAP</span>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('chart-{{ $chartId }}'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [
                        { label: 'Real', data: [], backgroundColor: '#2F6C3F' },
                        { label: 'RKAP', data: [], backgroundColor: '#DAA628' },
                    ],
                },
                options: { responsive: true, plugins: { legend: { display: false } } },
            });
        });
    </script>
@endpush

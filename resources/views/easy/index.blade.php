@extends('layouts.app')

@section('title', 'Daily Dashboard Report')
@section('subtitle', 'Dashboard Date : ' . $tanggalLabel)

@section('content')
    <div class="rounded-xl bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 class="font-heading text-sm font-extrabold text-dark">Dashboard Date : {{ $tanggalLabel }}</h3>
            <form method="GET" action="{{ route('easy.index') }}" class="flex items-center gap-2">
                <input type="date" name="tanggal" value="{{ $tanggalInput }}" class="rounded-lg border border-dark/15 px-3 py-1.5 text-sm">
                <button type="submit" class="rounded-lg bg-dark px-3 py-1.5 text-sm text-white transition hover:bg-dark-soft">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                        <th class="px-5 py-3">Keterangan</th>
                        <th class="px-5 py-3 text-right">Saldo Awal Bulan</th>
                        <th class="px-5 py-3 text-right">Realisasi Per Tanggal</th>
                        <th class="px-5 py-3 text-right">Saldo s/d Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark/5">
                    @php($groupShown = null)
                    @forelse ($rows as $row)
                        @php
                            $badge = match ($row->POSISI) {
                                'OPERASIONAL' => ['bg-amber-500', 'fa-dolly'],
                                'KEUANGAN' => ['bg-green', 'fa-calculator'],
                                default => ['bg-dark', 'fa-chart-line'],
                            };
                        @endphp
                        @if ($groupShown !== $row->POSISI)
                            <tr>
                                <td colspan="4" class="px-5 py-2">
                                    <span class="inline-flex items-center gap-2 rounded-md {{ $badge[0] }} px-3 py-1 text-xs font-semibold text-white">
                                        <i class="fas {{ $badge[1] }}"></i> {{ $row->POSISI }}
                                    </span>
                                </td>
                            </tr>
                            @php($groupShown = $row->POSISI)
                        @endif
                        <tr>
                            <td class="px-5 py-2">{{ $row->KETERANGAN }}</td>
                            <td class="px-5 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($row->SALDO_AWAL) }}</td>
                            <td class="px-5 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($row->REALISASI) }}</td>
                            <td class="px-5 py-2 text-right">{{ \App\Support\FormatHelper::maskRp($row->SALDO_AKHIR) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-dark/40">Tidak ada data untuk tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

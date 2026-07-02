@extends('layouts.app')

@section('title', 'RKAP Saya')
@section('subtitle', 'Rencana Kerja dan Anggaran Perusahaan')

@php
    $statusColors = [
        'draft'        => 'bg-gray-100 text-gray-600',
        'submitted'    => 'bg-blue-100 text-blue-700',
        'gm_approved'  => 'bg-amber-100 text-amber-700',
        'gm_rejected'  => 'bg-red-100 text-red-600',
        'approved'     => 'bg-green/10 text-green',
        'rejected'     => 'bg-red-100 text-red-600',
    ];
    $statusLabels = [
        'draft'        => 'Draft',
        'submitted'    => 'Menunggu GM',
        'gm_approved'  => 'Menunggu Direksi',
        'gm_rejected'  => 'Ditolak GM',
        'approved'     => 'Disahkan',
        'rejected'     => 'Ditolak Direksi',
    ];
    $grandQty   = collect($rkaps)->sum('TOTAL_QTY');
    $grandNilai = collect($rkaps)->sum('TOTAL_NILAI');
@endphp

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green/10 px-4 py-3 text-sm text-green">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('info'))
        <div class="mb-4 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700">
            <i class="fas fa-info-circle mr-1"></i> {{ session('info') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex items-center gap-2">
            <select name="tahun" onchange="this.form.submit()"
                class="rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                @foreach ($years as $y)
                    <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('rkap.create') }}"
            class="flex items-center gap-2 rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">
            <i class="fas fa-plus"></i> Buat RKAP Baru
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="border-b border-dark/10 bg-dark/5">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Tahun</th>
                    <th class="px-4 py-3 text-right font-semibold text-dark/70">Qty (Ton)</th>
                    <th class="px-4 py-3 text-right font-semibold text-dark/70">Nilai (Rp)</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Tgl Ajuan</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Catatan</th>
                    <th class="px-4 py-3 text-center font-semibold text-dark/70">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark/5">
                @forelse ($rkaps as $r)
                    <tr class="hover:bg-dark/[0.02]">
                        <td class="px-4 py-3 font-medium text-dark">{{ $r->JUMLAH_PRODUK }} Produk</td>
                        <td class="px-4 py-3 text-dark/70">{{ $r->TAHUN }}</td>
                        <td class="px-4 py-3 text-right text-dark/70">{{ number_format($r->TOTAL_QTY, 3, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-dark/70">Rp {{ number_format($r->TOTAL_NILAI, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColors[$r->STATUS] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$r->STATUS] ?? $r->STATUS }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-dark/50">
                            {{ $r->TGL_SUBMIT ? \Carbon\Carbon::parse($r->TGL_SUBMIT)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-dark/60 max-w-xs">
                            @if ($r->STATUS === 'gm_rejected' && $r->CATATAN_GM)
                                <span class="text-red-600"><i class="fas fa-comment mr-1"></i>{{ Str::limit($r->CATATAN_GM, 60) }}</span>
                            @elseif ($r->STATUS === 'rejected' && $r->CATATAN_DIREKSI)
                                <span class="text-red-600"><i class="fas fa-comment mr-1"></i>{{ Str::limit($r->CATATAN_DIREKSI, 60) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" onclick="openDetailModal({{ $r->ID }})" title="Detail"
                                    class="rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs text-blue-600 transition hover:bg-blue-100">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if (in_array($r->STATUS, ['draft','gm_rejected','rejected']))
                                    <a href="{{ route('rkap.edit', $r->ID) }}" title="Edit"
                                        class="rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs text-amber-600 transition hover:bg-amber-100">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                @endif
                                <a href="{{ route('rkap.export', $r->ID) }}" title="Download Excel"
                                    class="rounded-lg bg-green/10 px-2.5 py-1.5 text-xs text-green transition hover:bg-green/20">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                                @if ($r->STATUS === 'draft')
                                    <form method="POST" action="{{ route('rkap.destroy', $r->ID) }}" onsubmit="return confirm('Hapus draft ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="rounded-lg bg-red-50 px-2.5 py-1.5 text-xs text-red-600 transition hover:bg-red-100">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-dark/40">
                            Belum ada RKAP untuk tahun {{ $tahun }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($rkaps) > 0)
                <tfoot class="border-t-2 border-dark/20 bg-dark/5">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-right font-bold text-dark">Total Keseluruhan</td>
                        <td class="px-4 py-3 text-right font-bold text-dark">{{ number_format($grandQty, 3, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-dark">Rp {{ number_format($grandNilai, 0, ',', '.') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @include('rkap._detail_modal')
@endsection

@push('head')
<script>
const DETAIL_URL = "{{ url('rkap') }}";
function openDetailModal(id) {
    document.getElementById('detail-body').innerHTML = '<p class="text-dark/40">Memuat...</p>';
    document.getElementById('modal-detail').showModal();
    fetch(DETAIL_URL + '/' + id + '/detail')
        .then(r => r.json())
        .then(renderDetail);
}
</script>
@include('rkap._detail_script')
@endpush

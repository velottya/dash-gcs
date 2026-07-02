@extends('layouts.app')

@section('title', 'RKAP — Semua Pengajuan')
@section('subtitle', 'Ringkasan seluruh RKAP')

@php
    $statusColors = ['draft'=>'bg-gray-100 text-gray-600','submitted'=>'bg-blue-100 text-blue-700','gm_approved'=>'bg-amber-100 text-amber-700','gm_rejected'=>'bg-red-100 text-red-600','approved'=>'bg-green/10 text-green','rejected'=>'bg-red-100 text-red-600'];
    $statusLabels = ['draft'=>'Draft','submitted'=>'Menunggu GM','gm_approved'=>'Menunggu Direksi','gm_rejected'=>'Ditolak GM','approved'=>'Disahkan','rejected'=>'Ditolak Direksi'];
@endphp

@section('content')
    <div class="mb-4">
        <form method="GET">
            <select name="tahun" onchange="this.form.submit()"
                class="rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none">
                @foreach ($years as $y)
                    <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="border-b border-dark/10 bg-dark/5">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Manager</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">GM Validasi</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Tgl Ajuan</th>
                    <th class="px-4 py-3 text-center font-semibold text-dark/70">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark/5">
                @forelse ($rkaps as $r)
                    <tr class="hover:bg-dark/[0.02]">
                        <td class="px-4 py-3 font-medium text-dark">{{ ucwords(strtolower($r->nama_manager ?? '-')) }}</td>
                        <td class="px-4 py-3 text-dark/60">{{ ucwords(strtolower($r->nama_gm ?? '-')) }}</td>
                        <td class="px-4 py-3 text-dark/70">{{ $r->PRODUK }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColors[$r->STATUS] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$r->STATUS] ?? $r->STATUS }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-dark/50">
                            {{ $r->TGL_SUBMIT ? \Carbon\Carbon::parse($r->TGL_SUBMIT)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" onclick="openDetailModal({{ $r->ID }})" title="Detail"
                                    class="rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs text-blue-600 transition hover:bg-blue-100">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('rkap.export', $r->ID) }}" title="Download Excel"
                                    class="rounded-lg bg-green/10 px-2.5 py-1.5 text-xs text-green transition hover:bg-green/20">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-dark/40">Belum ada data RKAP.</td>
                    </tr>
                @endforelse
            </tbody>
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
    fetch(DETAIL_URL + '/' + id + '/detail').then(r => r.json()).then(renderDetail);
}
</script>
@include('rkap._detail_script')
@endpush

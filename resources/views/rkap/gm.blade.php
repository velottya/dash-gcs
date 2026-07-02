@extends('layouts.app')

@section('title', 'Validasi RKAP')
@section('subtitle', 'Pengajuan RKAP dari Manager')

@php
    $statusColors = ['submitted'=>'bg-blue-100 text-blue-700','gm_approved'=>'bg-amber-100 text-amber-700','gm_rejected'=>'bg-red-100 text-red-600','approved'=>'bg-green/10 text-green','rejected'=>'bg-red-100 text-red-600'];
    $statusLabels = ['submitted'=>'Menunggu Validasi','gm_approved'=>'Menunggu Direksi','gm_rejected'=>'Ditolak','approved'=>'Disahkan','rejected'=>'Ditolak Direksi'];
    $grandQty   = collect($rkaps)->sum('TOTAL_QTY');
    $grandNilai = collect($rkaps)->sum('TOTAL_NILAI');
@endphp

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green/10 px-4 py-3 text-sm text-green">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex items-center gap-3">
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
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Produk</th>
                    <th class="px-4 py-3 text-right font-semibold text-dark/70">Qty (Ton)</th>
                    <th class="px-4 py-3 text-right font-semibold text-dark/70">Nilai (Rp)</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-dark/70">Tgl Ajuan</th>
                    <th class="px-4 py-3 text-center font-semibold text-dark/70">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark/5">
                @forelse ($rkaps as $r)
                    <tr class="hover:bg-dark/[0.02]">
                        <td class="px-4 py-3 font-medium text-dark">{{ ucwords(strtolower($r->nama_manager ?? '-')) }}</td>
                        <td class="px-4 py-3 text-dark/70">{{ $r->JUMLAH_PRODUK }} Produk</td>
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
                                @if ($r->STATUS === 'submitted')
                                    <button type="button"
                                        onclick="openValidasi({{ $r->ID }}, 'approve')"
                                        title="Setujui"
                                        class="rounded-lg bg-green/10 px-2.5 py-1.5 text-xs text-green transition hover:bg-green/20">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button"
                                        onclick="openValidasi({{ $r->ID }}, 'reject')"
                                        title="Tolak"
                                        class="rounded-lg bg-red-50 px-2.5 py-1.5 text-xs text-red-600 transition hover:bg-red-100">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-dark/40">
                            Tidak ada pengajuan RKAP untuk tahun {{ $tahun }}.
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
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Modal Validasi GM --}}
    <dialog id="modal-validasi" class="w-full max-w-md rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
        <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
            <h3 id="validasi-title" class="font-heading font-extrabold text-dark">Validasi RKAP</h3>
            <button type="button" onclick="document.getElementById('modal-validasi').close()" class="text-dark/40 hover:text-dark">&times;</button>
        </div>
        <form id="form-validasi" method="POST" class="space-y-4 p-5">
            @csrf
            @method('PATCH')
            <input type="hidden" name="action" id="validasi-action">
            <div>
                <label class="mb-1 block text-sm font-medium text-dark">Catatan (opsional)</label>
                <textarea name="catatan" rows="3" placeholder="Tuliskan catatan jika diperlukan..."
                    class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-validasi').close()"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</button>
                <button type="submit" id="validasi-submit-btn"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition">Konfirmasi</button>
            </div>
        </form>
    </dialog>

    @include('rkap._detail_modal')
@endsection

@push('head')
<script>
const DETAIL_URL = "{{ url('rkap') }}";
const GM_VALIDATE_URL = "{{ url('rkap') }}";

function openDetailModal(id) {
    document.getElementById('detail-body').innerHTML = '<p class="text-dark/40">Memuat...</p>';
    document.getElementById('modal-detail').showModal();
    fetch(DETAIL_URL + '/' + id + '/detail').then(r => r.json()).then(renderDetail);
}

function openValidasi(id, action) {
    const isApprove = action === 'approve';
    document.getElementById('validasi-title').textContent = isApprove ? 'Setujui RKAP' : 'Tolak RKAP';
    document.getElementById('validasi-action').value = action;
    const btn = document.getElementById('validasi-submit-btn');
    btn.textContent = isApprove ? 'Setujui' : 'Tolak';
    btn.className = 'rounded-lg px-4 py-2 text-sm font-semibold text-white transition ' + (isApprove ? 'bg-green hover:bg-green/80' : 'bg-red-600 hover:bg-red-700');
    document.getElementById('form-validasi').action = GM_VALIDATE_URL + '/' + id + '/gm-validate';
    document.getElementById('modal-validasi').showModal();
}
</script>
@include('rkap._detail_script')
@endpush

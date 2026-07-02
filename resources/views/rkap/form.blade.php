@extends('layouts.app')

@section('title', $rkap ? 'Edit RKAP' : 'Buat RKAP Baru')

@php
    $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp

@section('content')
    <div class="mb-4">
        <a href="{{ route('rkap.index') }}" class="text-sm text-dark/50 hover:text-dark">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke RKAP
        </a>
    </div>

    @if ($rkap && in_array($rkap->STATUS, ['gm_rejected','rejected']))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">
                <i class="fas fa-times-circle mr-1"></i>
                {{ $rkap->STATUS === 'gm_rejected' ? 'Ditolak oleh GM' : 'Ditolak oleh Direksi' }}
            </p>
            <p>{{ $rkap->STATUS === 'gm_rejected' ? ($rkap->CATATAN_GM ?? '-') : ($rkap->CATATAN_DIREKSI ?? '-') }}</p>
        </div>
    @endif

    <div class="rounded-xl bg-white shadow-sm overflow-hidden">
        <form method="POST" action="{{ $rkap ? route('rkap.update', $rkap->ID) : route('rkap.store') }}">
            @csrf
            @if ($rkap) @method('PUT') @endif

            <div class="p-5 border-b border-dark/10 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-dark">Tahun RKAP</label>
                    <input type="number" name="tahun" value="{{ old('tahun', $rkap->TAHUN ?? $tahun) }}"
                        {{ $rkap ? 'readonly' : '' }}
                        class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green {{ $rkap ? 'bg-dark/5 cursor-not-allowed' : '' }}">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-dark">Produk</label>
                    <input type="text" name="produk" value="{{ old('produk', $rkap->PRODUK ?? '') }}" required
                        placeholder="Nama produk..."
                        class="w-full rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                </div>
            </div>

            {{-- Tabel input bulanan --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-dark/5 border-b border-dark/10">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-dark/70 w-32">Bulan</th>
                            <th class="px-4 py-3 text-right font-semibold text-dark/70">Qty (Ton)</th>
                            <th class="px-4 py-3 text-right font-semibold text-dark/70">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5">
                        @for ($b = 1; $b <= 12; $b++)
                            <tr class="hover:bg-dark/[0.02]">
                                <td class="px-4 py-2 font-medium text-dark">{{ $bulanNames[$b] }}</td>
                                <td class="px-4 py-2">
                                    <input type="number" name="qty[{{ $b }}]" step="0.001" min="0"
                                        value="{{ old("qty.$b", isset($details[$b]) ? $details[$b]->QTY_TON : '') }}"
                                        placeholder="0"
                                        class="w-full rounded-lg border border-dark/20 px-2 py-1.5 text-right text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="nilai[{{ $b }}]" step="1" min="0"
                                        value="{{ old("nilai.$b", isset($details[$b]) ? $details[$b]->NILAI_RUPIAH : '') }}"
                                        placeholder="0"
                                        class="w-full rounded-lg border border-dark/20 px-2 py-1.5 text-right text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot class="border-t-2 border-dark/20 bg-dark/5">
                        <tr>
                            <td class="px-4 py-3 font-bold text-dark">Total</td>
                            <td class="px-4 py-3 text-right font-bold text-dark" id="total-qty">—</td>
                            <td class="px-4 py-3 text-right font-bold text-dark" id="total-nilai">—</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex items-center justify-end gap-2 p-5 border-t border-dark/10">
                <a href="{{ route('rkap.index') }}"
                    class="rounded-lg border border-dark/20 px-4 py-2 text-sm text-dark transition hover:bg-dark/5">Batal</a>
                <button type="submit" name="action" value="draft"
                    class="rounded-lg border border-green px-4 py-2 text-sm font-semibold text-green transition hover:bg-green/5">
                    <i class="fas fa-save mr-1"></i> Simpan Draft
                </button>
                <button type="submit" name="action" value="submit"
                    class="rounded-lg bg-green px-4 py-2 text-sm font-semibold text-white transition hover:bg-green/80">
                    <i class="fas fa-paper-plane mr-1"></i> Ajukan ke GM
                </button>
            </div>
        </form>
    </div>
@endsection

@push('head')
<script>
function calcTotals() {
    let qty = 0, nilai = 0;
    document.querySelectorAll('input[name^="qty"]').forEach(el => qty += parseFloat(el.value || 0));
    document.querySelectorAll('input[name^="nilai"]').forEach(el => nilai += parseFloat(el.value || 0));
    document.getElementById('total-qty').textContent = qty.toLocaleString('id-ID', {minimumFractionDigits:3}) + ' ton';
    document.getElementById('total-nilai').textContent = 'Rp ' + nilai.toLocaleString('id-ID');
}
document.querySelectorAll('input[name^="qty"], input[name^="nilai"]').forEach(el => el.addEventListener('input', calcTotals));
calcTotals();
</script>
@endpush

{{-- Blok 1 produk: combobox pencarian (dbo.INVENTORY) + grid bulanan 2-kolom.
     Dipakai berulang di form create/edit RKAP; $i bisa berupa angka (blok
     nyata) atau token "__INDEX__" (template untuk di-clone lewat JS). --}}
@php
    $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $months ??= [];
    $stockid ??= '';
    $produk ??= '';
@endphp
<div class="product-block mb-4 rounded-lg border border-dark/10" data-index="{{ $i }}">
    <div class="flex items-center justify-between border-b border-dark/10 bg-dark/5 px-4 py-2">
        <span class="text-sm font-semibold text-dark">Produk #<span class="block-number">{{ is_numeric($i) ? $i + 1 : '' }}</span></span>
        <button type="button" class="remove-block-btn text-xs text-red-500 transition hover:text-red-700" onclick="removeProductBlock(this)">
            <i class="fas fa-trash mr-1"></i> Hapus Produk
        </button>
    </div>

    <div class="p-4">
        <label class="mb-1 block text-sm font-medium text-dark">Produk <span class="text-red-500">*</span></label>
        <div class="produk-combobox relative">
            <div class="flex items-center overflow-hidden rounded-lg border border-dark/20 bg-white focus-within:border-green focus-within:ring-1 focus-within:ring-green">
                <span class="flex items-center pl-3 text-dark/30"><i class="fas fa-search text-xs leading-none"></i></span>
                <input type="text" class="search-produk min-w-0 flex-1 bg-transparent px-3 py-2 text-sm outline-none"
                    autocomplete="off" data-lpignore="true" data-1p-ignore
                    placeholder="Ketik kode barang atau nama produk..."
                    value="{{ $stockid ? $stockid.'  —  '.$produk : '' }}"
                    {{ $stockid ? 'readonly' : '' }}
                    {{ $stockid ? 'style=color:#0F261F;font-weight:500' : '' }}>
                <button type="button" class="clear-produk-btn shrink-0 px-3 py-2 text-dark/30 transition hover:text-red-500 {{ $stockid ? '' : 'hidden' }}"
                    onclick="clearProduk(this)" tabindex="-1">
                    <i class="fas fa-times-circle text-sm leading-none"></i>
                </button>
            </div>
            <div class="produk-results absolute left-0 right-0 z-30 mt-1 hidden max-h-56 overflow-y-auto rounded-lg border border-dark/10 bg-white shadow-xl"></div>
        </div>
        <input type="hidden" name="products[{{ $i }}][stockid]" class="input-stockid" value="{{ $stockid }}">
        <input type="hidden" name="products[{{ $i }}][produk]" class="input-produk" value="{{ $produk }}">
    </div>

    <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
        @foreach ([['from' => 1, 'to' => 6], ['from' => 7, 'to' => 12]] as $half)
            <div class="overflow-hidden rounded-lg border border-dark/10">
                <table class="w-full text-sm">
                    <thead class="border-b border-dark/10 bg-dark/5">
                        <tr>
                            <th class="w-24 px-3 py-2 text-left font-semibold text-dark/70">Bulan</th>
                            <th class="px-3 py-2 text-right font-semibold text-dark/70">Qty (Ton)</th>
                            <th class="px-3 py-2 text-right font-semibold text-dark/70">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5">
                        @for ($b = $half['from']; $b <= $half['to']; $b++)
                            <tr class="hover:bg-dark/[0.02]">
                                <td class="px-3 py-1.5 font-medium text-dark">{{ $bulanNames[$b] }}</td>
                                <td class="px-3 py-1.5">
                                    <input type="number" name="products[{{ $i }}][qty][{{ $b }}]" step="0.001" min="0"
                                        value="{{ isset($months[$b]) ? $months[$b]->QTY_TON : '' }}" placeholder="0"
                                        class="block-qty w-full rounded-lg border border-dark/20 px-2 py-1 text-right text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                                </td>
                                <td class="px-3 py-1.5">
                                    <input type="number" name="products[{{ $i }}][nilai][{{ $b }}]" step="1" min="0"
                                        value="{{ isset($months[$b]) ? $months[$b]->NILAI_RUPIAH : '' }}" placeholder="0"
                                        class="block-nilai w-full rounded-lg border border-dark/20 px-2 py-1 text-right text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green">
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-end gap-6 border-t border-dark/10 bg-dark/5 px-4 py-2 text-xs">
        <span class="font-semibold text-dark/60">Subtotal Produk Ini:</span>
        <span class="block-subtotal-qty font-semibold text-dark">—</span>
        <span class="block-subtotal-nilai font-semibold text-dark">—</span>
    </div>
</div>

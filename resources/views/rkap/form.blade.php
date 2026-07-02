@extends('layouts.app')

@section('title', $rkap ? 'Edit RKAP' : 'Buat RKAP Baru')

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
        <form method="POST" action="{{ $rkap ? route('rkap.update', $rkap->ID) : route('rkap.store') }}" id="form-rkap">
            @csrf
            @if ($rkap) @method('PUT') @endif

            <div class="p-5 border-b border-dark/10">
                <label class="mb-1 block text-sm font-medium text-dark">Tahun RKAP</label>
                <input type="number" name="tahun" value="{{ old('tahun', $rkap->TAHUN ?? $tahun) }}"
                    {{ $rkap ? 'readonly' : '' }}
                    class="w-full max-w-xs rounded-lg border border-dark/20 px-3 py-2 text-sm focus:border-green focus:outline-none focus:ring-1 focus:ring-green {{ $rkap ? 'bg-dark/5 cursor-not-allowed' : '' }}">
            </div>

            {{-- Produk (repeatable): tiap blok = 1 produk (pilih dari INVENTORY) + 12 bulan Qty/Nilai --}}
            <div class="p-4 border-b border-dark/10">
                <div id="product-blocks">
                    @forelse ($products as $i => $p)
                        @include('rkap._product_block', ['i' => $i, 'stockid' => $p['stockid'], 'produk' => $p['produk'], 'months' => $p['months']])
                    @empty
                        @include('rkap._product_block', ['i' => 0, 'stockid' => '', 'produk' => '', 'months' => []])
                    @endforelse
                </div>
                <button type="button" onclick="addProductBlock()"
                    class="w-full rounded-lg border border-dashed border-green/40 px-4 py-2 text-sm font-semibold text-green transition hover:bg-green/5">
                    <i class="fas fa-plus mr-1"></i> Tambah Produk
                </button>
            </div>

            <div class="flex items-center justify-end gap-6 border-t border-dark/10 bg-dark/5 px-5 py-3">
                <span class="text-sm font-bold text-dark">Total Keseluruhan</span>
                <span class="text-sm font-bold text-dark" id="grand-total-qty">—</span>
                <span class="text-sm font-bold text-dark" id="grand-total-nilai">—</span>
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

    {{-- Template blok kosong untuk di-clone lewat JS saat "Tambah Produk" --}}
    <template id="product-block-template">
        @include('rkap._product_block', ['i' => '__INDEX__', 'stockid' => '', 'produk' => '', 'months' => []])
    </template>
@endsection

@push('head')
<script>
const SEARCH_PRODUK_URL = "{{ route('rkap.search-produk') }}";
let blockCount = {{ max(1, count($products)) }};

// ── Tambah / hapus blok produk ──────────────────────────────────────────────
function addProductBlock() {
    const tpl = document.getElementById('product-block-template').content.cloneNode(true);
    const wrapper = tpl.querySelector('.product-block');
    wrapper.innerHTML = wrapper.innerHTML.replaceAll('__INDEX__', blockCount);
    wrapper.dataset.index = blockCount;
    document.getElementById('product-blocks').appendChild(tpl);
    const added = document.querySelector('#product-blocks .product-block:last-child');
    attachComboboxHandlers(added);
    attachTotalHandlers(added);
    blockCount++;
    renumberBlocks();
    calcGrandTotal();
}

function removeProductBlock(btn) {
    const blocks = document.querySelectorAll('#product-blocks .product-block');
    if (blocks.length <= 1) {
        alert('Minimal harus ada 1 produk.');
        return;
    }
    btn.closest('.product-block').remove();
    renumberBlocks();
    calcGrandTotal();
}

function renumberBlocks() {
    document.querySelectorAll('#product-blocks .product-block').forEach((el, idx) => {
        el.querySelector('.block-number').textContent = idx + 1;
    });
}

// ── Combobox pencarian produk (dbo.INVENTORY), diskop per blok ─────────────
function attachComboboxHandlers(block) {
    const input   = block.querySelector('.search-produk');
    const results = block.querySelector('.produk-results');
    let timer;

    input.addEventListener('input', function () {
        if (this.readOnly) return;
        clearTimeout(timer);
        const q = this.value.trim();
        if (!q) { results.classList.add('hidden'); return; }
        timer = setTimeout(() => {
            fetch(SEARCH_PRODUK_URL + '?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => renderProdukList(data, block));
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!block.contains(e.target)) results.classList.add('hidden');
    });
}

function renderProdukList(data, block) {
    const box = block.querySelector('.produk-results');
    box.innerHTML = '';
    if (!data.length) {
        box.innerHTML = '<p class="px-4 py-3 text-xs text-dark/40 italic">Tidak ditemukan</p>';
        box.classList.remove('hidden');
        return;
    }
    data.forEach(p => {
        const el = document.createElement('button');
        el.type = 'button';
        el.className = 'block w-full border-b border-dark/5 px-3 py-2.5 text-left text-sm transition last:border-0 hover:bg-emerald-50';
        el.innerHTML = `<span class="font-semibold text-dark">${p.STOCKID}</span>`
            + `<span class="mx-1.5 text-dark/30">—</span>`
            + `<span class="text-dark/80">${p.NAMA_BARANG}</span>`;
        el.addEventListener('click', () => selectProduk(p, block));
        box.appendChild(el);
    });
    box.classList.remove('hidden');
}

function selectProduk(p, block) {
    block.querySelector('.input-stockid').value = p.STOCKID;
    block.querySelector('.input-produk').value  = p.NAMA_BARANG;
    const input = block.querySelector('.search-produk');
    input.value = p.STOCKID + '  —  ' + p.NAMA_BARANG;
    input.readOnly = true;
    input.style.color = '#0F261F';
    input.style.fontWeight = '500';
    block.querySelector('.clear-produk-btn').classList.remove('hidden');
    block.querySelector('.produk-results').classList.add('hidden');
}

function clearProduk(btn) {
    const block = btn.closest('.product-block');
    block.querySelector('.input-stockid').value = '';
    block.querySelector('.input-produk').value  = '';
    const input = block.querySelector('.search-produk');
    input.value = '';
    input.readOnly = false;
    input.style.fontWeight = '';
    btn.classList.add('hidden');
    input.focus();
}

// ── Subtotal per blok & total keseluruhan ───────────────────────────────────
function attachTotalHandlers(block) {
    block.querySelectorAll('.block-qty, .block-nilai').forEach(el => el.addEventListener('input', function () {
        calcBlockSubtotal(block);
        calcGrandTotal();
    }));
    calcBlockSubtotal(block);
}

function calcBlockSubtotal(block) {
    let qty = 0, nilai = 0;
    block.querySelectorAll('.block-qty').forEach(el => qty += parseFloat(el.value || 0));
    block.querySelectorAll('.block-nilai').forEach(el => nilai += parseFloat(el.value || 0));
    block.querySelector('.block-subtotal-qty').textContent   = qty.toLocaleString('id-ID', {minimumFractionDigits:3}) + ' ton';
    block.querySelector('.block-subtotal-nilai').textContent = 'Rp ' + nilai.toLocaleString('id-ID');
}

function calcGrandTotal() {
    let qty = 0, nilai = 0;
    document.querySelectorAll('#product-blocks .block-qty').forEach(el => qty += parseFloat(el.value || 0));
    document.querySelectorAll('#product-blocks .block-nilai').forEach(el => nilai += parseFloat(el.value || 0));
    document.getElementById('grand-total-qty').textContent   = qty.toLocaleString('id-ID', {minimumFractionDigits:3}) + ' ton';
    document.getElementById('grand-total-nilai').textContent = 'Rp ' + nilai.toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#product-blocks .product-block').forEach(block => {
        attachComboboxHandlers(block);
        attachTotalHandlers(block);
    });
    renumberBlocks();
    calcGrandTotal();
});
</script>
@endpush

{{-- Shared JS untuk render detail RKAP --}}
<script>
const BULAN_NAMES = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const STATUS_LABELS = {draft:'Draft',submitted:'Menunggu GM',gm_approved:'Menunggu Direksi',gm_rejected:'Ditolak GM',approved:'Disahkan',rejected:'Ditolak Direksi'};
const STATUS_COLORS = {draft:'bg-gray-100 text-gray-600',submitted:'bg-blue-100 text-blue-700',gm_approved:'bg-amber-100 text-amber-700',gm_rejected:'bg-red-100 text-red-600',approved:'bg-green/10 text-green',rejected:'bg-red-100 text-red-600'};

function fmt(n) { return Number(n||0).toLocaleString('id-ID'); }

function renderDetail(data) {
    const r = data.rkap;
    const d = data.details;
    let rows = '';
    let totalQty = 0, totalNilai = 0;
    for (let b = 1; b <= 12; b++) {
        const det = d[b] || {QTY_TON:0, NILAI_RUPIAH:0};
        totalQty   += parseFloat(det.QTY_TON   || 0);
        totalNilai += parseFloat(det.NILAI_RUPIAH || 0);
        rows += `<tr class="border-b border-dark/5">
            <td class="py-1.5 pr-4 text-dark/60">${BULAN_NAMES[b]}</td>
            <td class="py-1.5 pr-4 text-right">${fmt(det.QTY_TON)}</td>
            <td class="py-1.5 text-right">Rp ${fmt(det.NILAI_RUPIAH)}</td>
        </tr>`;
    }

    let html = `
    <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
        <div><p class="text-xs text-dark/40">Produk</p><p class="font-semibold">${r.PRODUK}</p></div>
        <div><p class="text-xs text-dark/40">Tahun</p><p class="font-semibold">${r.TAHUN}</p></div>
        <div><p class="text-xs text-dark/40">Manager</p><p class="font-semibold">${r.nama_manager||r.NIK_MANAGER}</p></div>
        <div><p class="text-xs text-dark/40">Status</p>
            <span class="rounded-full px-2 py-0.5 text-xs font-semibold ${STATUS_COLORS[r.STATUS]||'bg-gray-100 text-gray-600'}">${STATUS_LABELS[r.STATUS]||r.STATUS}</span>
        </div>
        ${r.CATATAN_GM ? `<div class="col-span-2"><p class="text-xs text-dark/40">Catatan GM</p><p class="text-sm ${r.STATUS==='gm_rejected'?'text-red-600':''}">${r.CATATAN_GM}</p></div>` : ''}
        ${r.CATATAN_DIREKSI ? `<div class="col-span-2"><p class="text-xs text-dark/40">Catatan Direksi</p><p class="text-sm ${r.STATUS==='rejected'?'text-red-600':''}">${r.CATATAN_DIREKSI}</p></div>` : ''}
    </div>
    <table class="w-full text-sm">
        <thead class="bg-dark/5"><tr>
            <th class="py-2 pr-4 text-left font-semibold text-dark/70">Bulan</th>
            <th class="py-2 pr-4 text-right font-semibold text-dark/70">Qty (Ton)</th>
            <th class="py-2 text-right font-semibold text-dark/70">Nilai (Rp)</th>
        </tr></thead>
        <tbody>${rows}</tbody>
        <tfoot class="border-t-2 border-dark/20 bg-dark/5">
            <tr>
                <td class="py-2 pr-4 font-bold">Total</td>
                <td class="py-2 pr-4 text-right font-bold">${fmt(totalQty)}</td>
                <td class="py-2 text-right font-bold">Rp ${fmt(totalNilai)}</td>
            </tr>
        </tfoot>
    </table>`;
    document.getElementById('detail-body').innerHTML = html;
}
</script>

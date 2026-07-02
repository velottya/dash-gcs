{{-- Shared: modal detail RKAP (reuse di semua role view) --}}
<dialog id="modal-detail" class="w-full max-w-2xl rounded-xl p-0 shadow-2xl backdrop:bg-dark/50">
    <div class="flex items-center justify-between border-b border-dark/10 px-5 py-4">
        <h3 class="font-heading font-extrabold text-dark">Detail RKAP</h3>
        <button type="button" onclick="document.getElementById('modal-detail').close()" class="text-dark/40 hover:text-dark">&times;</button>
    </div>
    <div id="detail-body" class="p-5 overflow-y-auto max-h-[70vh]">
        <p class="text-dark/40">Memuat...</p>
    </div>
</dialog>

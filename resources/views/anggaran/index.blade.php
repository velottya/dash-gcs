@extends('layouts.app')

@section('title', 'Anggaran Tahun ' . now()->year)
@section('subtitle', 'Bagian Pengembangan Usaha')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Penjualan', 'value' => '41.410', 'icon' => 'fa-bookmark', 'color' => 'bg-green'],
                ['label' => 'Laba Kotor', 'value' => '41.410', 'icon' => 'fa-thumbs-up', 'color' => 'bg-green-light'],
                ['label' => 'Beban Pemasaran', 'value' => '41.410', 'icon' => 'fa-calendar-alt', 'color' => 'bg-gold'],
                ['label' => 'Beban Umum & Adm', 'value' => '41.410', 'icon' => 'fa-comments', 'color' => 'bg-dark'],
            ] as $box)
                <div class="rounded-xl {{ $box['color'] }} p-5 text-white shadow-sm">
                    <i class="far {{ $box['icon'] }} text-2xl opacity-70"></i>
                    <p class="mt-2 text-sm opacity-80">{{ $box['label'] }}</p>
                    <p class="font-heading text-2xl font-extrabold">{{ $box['value'] }}</p>
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                        <div class="h-full bg-white/80" style="width: 70%"></div>
                    </div>
                    <p class="mt-1 text-xs opacity-70">80% dari realisasi tahun {{ now()->year - 1 }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h3 class="font-heading mb-4 text-sm font-extrabold text-dark">Tambah Anggaran Barang · Wilayah {{ $kodeWilayah ?: '-' }}</h3>

            <form class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-dark">Nama Barang</label>
                        <select class="w-full rounded-lg border border-dark/15 px-3 py-2 text-sm">
                            @forelse ($inventoryOptions as $item)
                                <option value="{{ $item->REFSTOCK }}">({{ $item->REFSTOCK }}) {{ $item->NAMA_BARANG }}</option>
                            @empty
                                <option value="">Tidak ada data barang untuk wilayah ini</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-dark">Kode CC Penjualan</label>
                        <select class="w-full rounded-lg border border-dark/15 px-3 py-2 text-sm">
                            @forelse ($ccOptions as $cc)
                                <option value="{{ $cc->KODE_CC }}">({{ $cc->KODE_CC }}) {{ $cc->WILAYAH }}</option>
                            @empty
                                <option value="">Tidak ada data CC untuk wilayah ini</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                                <th class="px-3 py-2">Bulan</th>
                                <th class="px-3 py-2">Qty</th>
                                <th class="px-3 py-2">Harga Beli</th>
                                <th class="px-3 py-2">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark/5">
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bulan)
                                <tr>
                                    <td class="px-3 py-1.5 font-medium text-dark/70">{{ $bulan }}</td>
                                    <td class="px-3 py-1.5"><input type="text" name="qty_{{ sprintf('%02d', $i + 1) }}" class="w-full rounded-lg border border-dark/15 px-2 py-1.5"></td>
                                    <td class="px-3 py-1.5"><input type="text" name="beli_{{ sprintf('%02d', $i + 1) }}" class="w-full rounded-lg border border-dark/15 px-2 py-1.5"></td>
                                    <td class="px-3 py-1.5"><input type="text" name="jual_{{ sprintf('%02d', $i + 1) }}" class="w-full rounded-lg border border-dark/15 px-2 py-1.5"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="button" class="rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white transition hover:bg-dark-soft">
                    Simpan Anggaran
                </button>
            </form>
        </div>
    </div>
@endsection

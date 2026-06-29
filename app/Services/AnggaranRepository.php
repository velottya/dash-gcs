<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnggaranRepository
{
    public function kodeWilayah(string $nik): ?string
    {
        return DB::selectOne('SELECT KODE_WILAYAH FROM PEGAWAI_SDM WHERE Nik = ?', [$nik])?->KODE_WILAYAH;
    }

    public function inventoryOptions(string $kodeWilayah): array
    {
        return DB::select(
            "SELECT REFSTOCK, NAMA_BARANG FROM INVENTORY
             WHERE DIVISI = ? AND JENIS = 'Dagang' AND KODE_SEKTOR != ''
             ORDER BY REFSTOCK",
            [$kodeWilayah]
        );
    }

    public function ccOptions(string $kodeWilayah): array
    {
        return DB::select(
            "SELECT B.KODE_CC, B.WILAYAH FROM AKUN_ACCOUNT_HCC A
             INNER JOIN AKUN_ACCOUNT_CC B ON A.GRP_A = B.GRP_A
             WHERE A.DIVISI = ? ORDER BY B.KODE_CC",
            [$kodeWilayah]
        );
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DemografiRepository
{
    public function totalKaryawan(): int
    {
        return (int) DB::selectOne(
            "SELECT COUNT(JENIS_PEGAWAI) TOTAL FROM PEGAWAI_SDM WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI IN ('Tetap', 'Kontrak')"
        )->TOTAL;
    }

    public function totalTetap(): int
    {
        return (int) DB::selectOne(
            "SELECT COUNT(JENIS_PEGAWAI) TOTAL FROM PEGAWAI_SDM WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI = 'Tetap'"
        )->TOTAL;
    }

    public function totalKontrak(): int
    {
        return (int) DB::selectOne(
            "SELECT COUNT(JENIS_PEGAWAI) TOTAL FROM PEGAWAI_SDM WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI = 'Kontrak'"
        )->TOTAL;
    }

    public function statusKaryawan(): array
    {
        return DB::select(
            "SELECT JENIS_PEGAWAI1 JENIS_PEGAWAI, COUNT(JENIS_PEGAWAI1) TOTAL
             FROM PEGAWAI_SDM
             WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI IN ('Tetap', 'Kontrak')
             GROUP BY JENIS_PEGAWAI1
             ORDER BY JENIS_PEGAWAI1 DESC"
        );
    }

    public function byWilayah(): array
    {
        return DB::select(
            "SELECT JAB_WILAYAH WILAYAH,
                 (SELECT COUNT(JAB_WILAYAH) FROM PEGAWAI_SDM WHERE JAB_WILAYAH = X.JAB_WILAYAH AND JENIS_PEGAWAI = 'Tetap') TETAP,
                 (SELECT COUNT(JAB_WILAYAH) FROM PEGAWAI_SDM WHERE JAB_WILAYAH = X.JAB_WILAYAH AND JENIS_PEGAWAI = 'Kontrak') KONTRAK
             FROM PEGAWAI_SDM X
             WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI IN ('KONTRAK', 'TETAP')
             GROUP BY JAB_WILAYAH"
        );
    }

    public function byKelamin(): array
    {
        return DB::select(
            "SELECT JENIS_KELAMIN,
                 (SELECT COUNT(JENIS_KELAMIN) FROM PEGAWAI_SDM WHERE JENIS_KELAMIN = X.JENIS_KELAMIN AND JENIS_PEGAWAI = 'Tetap') TETAP,
                 (SELECT COUNT(JENIS_KELAMIN) FROM PEGAWAI_SDM WHERE JENIS_KELAMIN = X.JENIS_KELAMIN AND JENIS_PEGAWAI = 'Kontrak') KONTRAK
             FROM PEGAWAI_SDM X
             WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI IN ('KONTRAK', 'TETAP')
             GROUP BY JENIS_KELAMIN"
        );
    }

    public function byPendidikan(): array
    {
        return DB::select(
            "SELECT KODE_PENDIDIKAN, RTRIM(NM_PENDIDIKAN) NM_PENDIDIKAN,
                 (SELECT COUNT(NM_PENDIDIKAN) FROM PEGAWAI_SDM WHERE NM_PENDIDIKAN = X.NM_PENDIDIKAN AND JENIS_PEGAWAI = 'Tetap') TETAP
             FROM PEGAWAI_SDM X
             WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI IN ('KONTRAK', 'TETAP')
             GROUP BY NM_PENDIDIKAN, KODE_PENDIDIKAN
             ORDER BY KODE_PENDIDIKAN DESC"
        );
    }

    public function byJabatan(): array
    {
        return DB::select(
            "SELECT ORD_GRUP, RTRIM(GRUP_JABATAN) NM_JABATAN,
                 (SELECT COUNT(GRUP_JABATAN) FROM PEGAWAI_SDM WHERE GRUP_JABATAN = X.GRUP_JABATAN AND JENIS_PEGAWAI = 'Tetap') TETAP
             FROM PEGAWAI_SDM X
             WHERE NIK <> 'DEMO' AND JENIS_PEGAWAI = 'Tetap'
             GROUP BY GRUP_JABATAN, ORD_GRUP
             ORDER BY ORD_GRUP"
        );
    }

    public function byUsia(): array
    {
        return DB::select(
            "SELECT KATEGORI_USIA, USIA1, USIA2,
                 (SELECT COUNT(AGE) FROM PEGAWAI_USIA WHERE AGE >= X.USIA1 AND AGE <= X.USIA2 AND JENIS_PEGAWAI = 'Tetap') TETAP,
                 (SELECT COUNT(AGE) FROM PEGAWAI_USIA WHERE AGE >= X.USIA1 AND AGE <= X.USIA2 AND JENIS_PEGAWAI = 'Kontrak') KONTRAK
             FROM DASH.MST_SDM_USIA X
             ORDER BY ID_KATEGORI"
        );
    }
}

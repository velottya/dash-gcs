<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PenjualanRepository
{
    public function monthlyAmountVsHpp(int $tahun): array
    {
        return DB::select(
            "SELECT MONTH(Y.TANGGAL) BULAN,
                 ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT WHERE B.Report_NR = 'LR01' AND YEAR(TANGGAL) = ? AND MONTH(A.TANGGAL) = MONTH(Y.TANGGAL)), 0) AMOUNT,
                 ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT WHERE B.Report_NR IN ('LR02', 'LR03') AND YEAR(TANGGAL) = ? AND MONTH(A.TANGGAL) = MONTH(Y.TANGGAL)), 0) HPP
             FROM AKUN_JURNAL_DETAIL Y
             WHERE YEAR(Y.TANGGAL) = ?
             GROUP BY MONTH(Y.TANGGAL)
             ORDER BY MONTH(Y.TANGGAL)",
            [$tahun, $tahun, $tahun]
        );
    }

    public function rkapYtd(int $tahun): array
    {
        return DB::select(
            "SELECT Y.PERIODE,
                 ISNULL((SELECT NILAI FROM DASH.MST_RKAP WHERE SUB_LAPORAN = 'PENJUALAN' AND PERIODE = Y.PERIODE AND LAPORAN = Y.LAPORAN), 0) NILAI,
                 ISNULL((SELECT NILAI FROM DASH.MST_RKAP WHERE SUB_LAPORAN = 'Beban Pokok Penjualan' AND PERIODE = Y.PERIODE AND LAPORAN = Y.LAPORAN), 0) HPP_RKAP
             FROM DASH.MST_RKAP Y
             WHERE TAHUN = ? AND LAPORAN = 'LABARUGI' AND ID_REPORT = 'X01'
             ORDER BY Y.PERIODE",
            [$tahun]
        );
    }

    public function sektorChart(int $tahun, int $bulan): array
    {
        return DB::select(
            "SELECT MONTH(TANGGAL) BULAN, (SUM(AMOUNT) * -1) AMOUNT, B.KODE_SEKTOR SUB_GROUP
             FROM AKUN_JURNAL_DETAIL A INNER JOIN INVENTORY B ON A.OBJECTID = B.REFSTOCK
             WHERE ACCOUNT = '250100' AND YEAR(TANGGAL) = ? AND MONTH(TANGGAL) = ? AND B.KODE_SEKTOR != ''
             GROUP BY MONTH(TANGGAL), B.KODE_SEKTOR
             ORDER BY MONTH(TANGGAL)",
            [$tahun, $bulan]
        );
    }

    public function sektorChartByBulan(int $bulan): array
    {
        return DB::select(
            "SELECT ISNULL((SUM(AMOUNT) * -1), 0) AMOUNT, B.KODE_SEKTOR SUB_GROUP
             FROM AKUN_JURNAL_DETAIL A INNER JOIN INVENTORY B ON A.OBJECTID = B.REFSTOCK
             WHERE ACCOUNT = '250100' AND YEAR(TANGGAL) = YEAR(GETDATE()) AND MONTH(TANGGAL) = ? AND B.KODE_SEKTOR != ''
             GROUP BY B.KODE_SEKTOR",
            [$bulan]
        );
    }

    public function wilayahChart(int $tahun, int $bulan): array
    {
        return DB::select(
            "SELECT (SUM(AMOUNT) * -1) AMOUNT, C.SUBWIL SUB_GROUP
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT_CC B ON A.KODE_CC = B.KODE_CC
                 INNER JOIN AKUN_ACCOUNT_HCC C ON C.GRP_A = B.GRP_A
             WHERE ACCOUNT = '250100' AND YEAR(TANGGAL) = ? AND MONTH(TANGGAL) = ?
             GROUP BY C.SUBWIL
             ORDER BY AMOUNT DESC",
            [$tahun, $bulan]
        );
    }

    public function wilayahChartByBulan(int $bulan): array
    {
        return DB::select(
            "SELECT ISNULL((SUM(AMOUNT) * -1), 0) AMOUNT, C.SUBWIL SUB_GROUP
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT_CC B ON A.KODE_CC = B.KODE_CC
                 INNER JOIN AKUN_ACCOUNT_HCC C ON C.GRP_A = B.GRP_A
             WHERE ACCOUNT = '250100' AND YEAR(TANGGAL) = YEAR(GETDATE()) AND MONTH(TANGGAL) = ?
             GROUP BY C.SUBWIL
             ORDER BY AMOUNT DESC",
            [$bulan]
        );
    }

    public function chartDetail(string $opt, int $tahun): array
    {
        $idReportFilter = $opt === 'labkor' ? "('X01', 'X02')" : "('X01')";
        $label = $opt === 'labkor' ? 'Laba Kotor' : 'Penjualan';

        $rows = DB::select(
            "SELECT TAHUN, PERIODE, '{$label}' LABEL1, SUM(RKAP) RKAP, SUM(AMOUNT) AMOUNT
             FROM (
                 SELECT TAHUN, PERIODE, ID_REPORT, NILAI RKAP,
                     ISNULL(
                         (SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                          WHERE A.periode = DASH.MST_RKAP.PERIODE AND B.ID_REPORT = DASH.MST_RKAP.ID_REPORT), 0) AMOUNT
                 FROM DASH.MST_RKAP
                 WHERE TAHUN = ? AND ID_REPORT IN {$idReportFilter}
             ) A
             GROUP BY TAHUN, PERIODE
             ORDER BY PERIODE",
            [$tahun]
        );

        return array_map(fn ($rec) => [
            'AMOUNT' => $rec->AMOUNT,
            'RKAP' => $rec->RKAP,
            'LABEL1' => trim($rec->LABEL1),
        ], $rows);
    }

    public function detailSektorBarang(int $bulan): array
    {
        $periode = now()->year.sprintf('%02d', $bulan);

        $rows = DB::select(
            "SELECT B.KODE_SEKTOR, B.NAMA_BARANG, SUM(AMOUNT * -1) AMOUNT, ISNULL(SUM(QTY), 0) QTY, A.SATUAN
             FROM AKUN_JURNAL_DETAIL A LEFT OUTER JOIN INVENTORY B ON A.OBJECTID = B.REFSTOCK
             WHERE A.PERIODE = ? AND A.ACCOUNT = '250100'
             GROUP BY B.KODE_SEKTOR, B.NAMA_BARANG, A.SATUAN
             ORDER BY B.KODE_SEKTOR DESC, SUM(AMOUNT * -1) DESC",
            [$periode]
        );

        return array_map(fn ($rec) => [
            'JUMLAH' => trim((string) $rec->KODE_SEKTOR),
            'KODE_SEKTOR' => trim((string) $rec->KODE_SEKTOR),
            'NAMA_BARANG' => ucwords(strtolower((string) $rec->NAMA_BARANG)),
            'SATUAN' => ucwords(strtolower((string) $rec->SATUAN)),
            'AMOUNT' => $rec->AMOUNT,
            'QTY' => $rec->QTY,
        ], $rows);
    }

    public function detailWilayahCc(int $bulan): array
    {
        $periode = now()->year.sprintf('%02d', $bulan);

        $rows = DB::select(
            "SELECT C.SUBWIL, B.KODE_CC, B.WILAYAH, ISNULL(SUM(A.AMOUNT * -1), 0) NILAI
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT_CC B ON A.KODE_CC = B.KODE_CC
                 INNER JOIN Akun_Account_HCC C ON C.GRP_A = B.GRP_A
             WHERE A.ACCOUNT = '250100' AND A.periode = ?
             GROUP BY C.SUBWIL, B.WILAYAH, B.KODE_CC
             ORDER BY C.SUBWIL, SUM(A.AMOUNT)",
            [$periode]
        );

        return array_map(fn ($rec) => [
            'SUBWIL' => trim((string) $rec->SUBWIL),
            'KODE_CC' => trim($rec->KODE_CC),
            'WILAYAH' => ucwords(strtolower($rec->WILAYAH)),
            'NILAI' => $rec->NILAI,
        ], $rows);
    }
}

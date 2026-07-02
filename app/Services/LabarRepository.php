<?php

namespace App\Services;

use App\Support\FormatHelper;
use Illuminate\Support\Facades\DB;

class LabarRepository
{
    public function laporanLabaRugi(string $periode, string $periodeSbl): array
    {
        return DB::select(
            "SELECT KETERANGAN, GRUP_LAPORAN,
                 ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                  WHERE B.ID_REPORT = DASH.MST_LABA_RUGI.ID_REPORT AND PERIODE = ?), 0) REALISASI,
                 ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                  WHERE B.ID_REPORT = DASH.MST_LABA_RUGI.ID_REPORT AND PERIODE = ?), 0) THNSBL,
                 (SELECT SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE ID_REPORT = DASH.MST_LABA_RUGI.ID_REPORT AND PERIODE = ?) RKAP
             FROM DASH.MST_LABA_RUGI
             ORDER BY URUT",
            [$periode, $periodeSbl, $periode]
        );
    }

    public function laporanLabaRugiDetail(string $periode): array
    {
        $tahunSbl = ((int) substr($periode, 0, 4)) - 1;
        $bulanSbl = substr($periode, 4, 2);
        $periodeSbl = $tahunSbl.$bulanSbl;

        $rows = DB::select(
            "SELECT KETERANGAN, GRUP_LAPORAN,
                 ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                  WHERE B.ID_REPORT = DASH.MST_LABA_RUGI.ID_REPORT AND PERIODE = ?), 0) REALISASI,
                 ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                  WHERE B.ID_REPORT = DASH.MST_LABA_RUGI.ID_REPORT AND PERIODE = ?), 0) THNSBL,
                 (SELECT SUM(NILAI) FROM DASH.MST_RKAP WHERE ID_REPORT = DASH.MST_LABA_RUGI.ID_REPORT AND PERIODE = ? AND LAPORAN = 'LABARUGI') RKAP
             FROM DASH.MST_LABA_RUGI
             ORDER BY URUT",
            [$periode, $periodeSbl, $periode]
        );

        return array_map(fn ($rec) => [
            'KETERANGAN' => trim($rec->KETERANGAN),
            'GRUP_LAPORAN' => trim($rec->GRUP_LAPORAN),
            'REALISASI' => FormatHelper::redenominasi($rec->REALISASI),
            'RKAP' => $rec->RKAP,
            'CAP_RKAP' => FormatHelper::targetPersen($rec->RKAP, FormatHelper::redenominasi($rec->REALISASI)),
            'THNSBL' => FormatHelper::redenominasi($rec->THNSBL),
            'CAP_THNSBL' => FormatHelper::targetPersen($rec->THNSBL, $rec->REALISASI),
        ], $rows);
    }

    public function historisPenjualanLaba(int $jumlahTahun = 7): array
    {
        // Includes the current (still-running) year as the latest point, so
        // the window is always "N tahun terakhir termasuk tahun ini" and
        // rolls forward automatically as the year changes.
        return DB::select(
            "SELECT XY.TAHUN, XY.PENJUALAN,
                 (SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                  WHERE B.LAPORAN = 'LABARUGI' AND YEAR(A.TANGGAL) = XY.TAHUN) LABA
             FROM (
                 SELECT TOP(?) YEAR(X.tanggal) TAHUN, SUM(X.amount) * -1 PENJUALAN
                 FROM akun_jurnal_detail X
                 WHERE X.account = '250100'
                 GROUP BY YEAR(X.tanggal)
                 ORDER BY YEAR(X.tanggal) DESC
             ) XY ORDER BY TAHUN",
            [$jumlahTahun]
        );
    }

    public function ratioAnalysis(string $periode): array
    {
        $amountByReportNr = fn (string $reportNr) => (float) (DB::selectOne(
            "SELECT ISNULL(SUM(AMOUNT), 0) AMOUNT FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT
             WHERE PERIODE = ? AND REPORT_NR = ?",
            [$periode, $reportNr]
        )?->AMOUNT ?? 0);

        $amountByReportNrIn = fn (array $reportNrs) => (float) (DB::selectOne(
            'SELECT ISNULL(SUM(AMOUNT), 0) AMOUNT FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT
             WHERE PERIODE = ? AND REPORT_NR IN ('.implode(',', array_fill(0, count($reportNrs), '?')).')',
            [$periode, ...$reportNrs]
        )?->AMOUNT ?? 0);

        $rkapBySubLaporan = fn (string $subLaporan) => (float) (DB::selectOne(
            'SELECT NILAI FROM DASH.MST_RKAP WHERE PERIODE = ? AND SUB_LAPORAN = ?',
            [$periode, $subLaporan]
        )?->NILAI ?? 0);

        $rkapBySubLaporanIn = fn (array $subLaporans) => (float) (DB::selectOne(
            'SELECT SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE PERIODE = ? AND SUB_LAPORAN IN ('.implode(',', array_fill(0, count($subLaporans), '?')).')',
            [$periode, ...$subLaporans]
        )?->NILAI ?? 0);

        $penjualanReal = $amountByReportNr('LR01') * -1;
        $penjualanRkap = $rkapBySubLaporan('PENJUALAN');

        $hppReal = $amountByReportNrIn(['LR02', 'LR02A', 'LR03']) * -1;
        $hppRkap = $rkapBySubLaporan('BEBAN POKOK PENJUALAN');

        $gpm = ($penjualanReal + $hppReal) / $penjualanReal * 100;
        $labkorReal = $penjualanReal + $hppReal;
        $labkorRkap = $penjualanRkap + $hppRkap;

        $buaReal = $amountByReportNr('LR05') * -1;
        $buaRkap = $rkapBySubLaporan('BEBAN UMUM DAN ADMINISTRASI');

        $bpReal = $amountByReportNr('LR06') * -1;
        $bpRkap = $rkapBySubLaporan('BEBAN PEMASARAN');

        $totalBuaReal = $buaReal + $bpReal;
        $totalBuaRkap = $buaRkap + $bpRkap;

        $opm = ($labkorReal + $totalBuaReal) / $penjualanReal * 100;
        $labaUsahaReal = $labkorReal - $totalBuaReal;
        $labaUsahaRkap = $labkorRkap - $totalBuaRkap;

        $pbLainReal = $amountByReportNr('LR09') * -1;
        $pbLainRkap = $rkapBySubLaporanIn(['PENDAPATAN LAIN-LAIN', 'BEBAN LAIN-LAIN']);

        $bebanKeuReal = $amountByReportNr('LR07') * -1;
        $bebanKeuRkap = $rkapBySubLaporan('BEBAN KEUANGAN');

        $jumlahPbReal = $pbLainReal + $bebanKeuReal;
        $jumlahPbRkap = $pbLainRkap + $bebanKeuRkap;

        $labaSblPajakReal = $labaUsahaReal + $jumlahPbReal;
        $labaSblPajakRkap = $labaUsahaRkap + $jumlahPbRkap;

        $bpajakReal = $amountByReportNr('LR10') * -1;
        $bpajakRkap = $rkapBySubLaporan('BEBAN PAJAK');

        $npm = ($labaSblPajakReal + $bpajakReal) / $penjualanReal * 100;
        $labaBersihReal = $labaSblPajakReal + $bpajakReal;
        $labaBersihRkap = $labaSblPajakRkap + $bpajakRkap;

        return [
            'PENJUALAN_REAL' => $penjualanReal,
            'PENJUALAN_RKAP' => $penjualanRkap,
            'PRO_PENJUALAN' => FormatHelper::targetPersen($penjualanRkap, $penjualanReal),
            'HPP_REAL' => $hppReal,
            'HPP_RKAP' => $hppRkap,
            'PRO_HPP' => FormatHelper::targetPersen($hppRkap, $hppReal),
            'LABA_KOTOR_REAL' => $labkorReal,
            'LABA_KOTOR_RKAP' => $labkorRkap,
            'PRO_LABA_KOTOR' => FormatHelper::targetPersen($labkorRkap, $labkorReal),
            'GPM' => $gpm,
            'BEBAN_UMUM_REAL' => $buaReal,
            'BEBAN_UMUM_RKAP' => $buaRkap,
            'PRO_BEBAN_UMUM' => FormatHelper::targetPersen($buaRkap, $buaReal),
            'BEBAN_PEMASARAN_REAL' => $bpReal,
            'BEBAN_PEMASARAN_RKAP' => $bpRkap,
            'PRO_BEBAN_PEMASARAN' => FormatHelper::targetPersen($bpRkap, $bpReal),
            'JML_BUSAHA_REAL' => $totalBuaReal,
            'JML_BUSAHA_RKAP' => $totalBuaRkap,
            'PRO_JML_BUSAHA' => FormatHelper::targetPersen($bpRkap, $bpReal),
            'LABA_USAHA_REAL' => $labaUsahaReal,
            'LABA_USAHA_RKAP' => $labaUsahaRkap,
            'PRO_LABA_USAHA' => FormatHelper::targetPersen($labaUsahaRkap, $labaUsahaReal),
            'OPM' => $opm,
            'PEND_LAIN_REAL' => $pbLainReal,
            'PEND_LAIN_RKAP' => $pbLainRkap,
            'PRO_PEND_LAIN' => FormatHelper::targetPersen($pbLainRkap, $pbLainReal),
            'BEBAN_KEU_REAL' => $bebanKeuReal,
            'BEBAN_KEU_RKAP' => $bebanKeuRkap,
            'PRO_BEBAN_KEU' => FormatHelper::targetPersen($bebanKeuRkap, $bebanKeuReal),
            'JML_LAIN_REAL' => $jumlahPbReal,
            'JML_LAIN_RKAP' => $jumlahPbRkap,
            'PRO_JML_LAIN' => FormatHelper::targetPersen($jumlahPbRkap, $jumlahPbReal),
            'LABA_SBLPAJAK_REAL' => $labaSblPajakReal,
            'LABA_SBLPAJAK_RKAP' => $labaSblPajakRkap,
            'PRO_LABA_SBLPAJAK' => FormatHelper::targetPersen($bebanKeuRkap, $bebanKeuReal),
            'PAJAK_REAL' => $bpajakReal,
            'PAJAK_RKAP' => $bpajakRkap,
            'PRO_PAJAK' => FormatHelper::targetPersen($bpajakRkap, $bpajakReal),
            'LABA_BERSIH_REAL' => $labaBersihReal,
            'LABA_BERSIH_RKAP' => $labaBersihRkap,
            'PRO_LABA_BERSIH' => FormatHelper::targetPersen($labaBersihRkap, $labaBersihReal),
            'NPM' => $npm,
        ];
    }
}

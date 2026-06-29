<?php

namespace App\Services;

use App\Support\FormatHelper;
use Illuminate\Support\Facades\DB;

class Dashboard2Repository
{
    public function kpiTahunan(): array
    {
        $periode = now()->format('Ym');
        $periodeSbl = (string) (((int) $periode) - 1);

        $amountByAccountRange = fn (string $periode, string $from, string $to) => (float) (DB::selectOne(
            "SELECT SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE PERIODE = ? AND ACCOUNT >= ? AND ACCOUNT <= ?",
            [$periode, $from, $to]
        )?->NILAI ?? 0);

        $penjualanSbl = (float) (DB::selectOne(
            "SELECT SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE PERIODE = ? AND ACCOUNT = '250100'",
            [$periodeSbl]
        )?->NILAI ?? 0);
        $penjualan = (float) (DB::selectOne(
            "SELECT SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE PERIODE = ? AND ACCOUNT = '250100'",
            [$periode]
        )?->NILAI ?? 0);
        $penjualanRkap = (float) (DB::selectOne(
            "SELECT NILAI FROM DASH.MST_RKAP WHERE PERIODE = ? AND ID_REPORT = 'X01'",
            [$periode]
        )?->NILAI ?? 0);

        $labkorSbl = $amountByAccountRange($periodeSbl, '250100', '250409');
        $labkor = $amountByAccountRange($periode, '250100', '250409');
        $labkorRkap = (float) (DB::selectOne(
            "SELECT SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE PERIODE = ? AND ID_REPORT IN ('X01', 'X02')",
            [$periode]
        )?->NILAI ?? 0);

        $labopsSbl = $amountByAccountRange($periodeSbl, '250100', '260232');
        $labops = $amountByAccountRange($periode, '250100', '260232');
        $labopsRkap = (float) (DB::selectOne(
            "SELECT SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE PERIODE = ? AND ID_REPORT IN ('X01', 'X02', 'X03', 'X15')",
            [$periode]
        )?->NILAI ?? 0);

        $labaSbl = $amountByAccountRange($periodeSbl, '250100', '280100');
        $laba = $amountByAccountRange($periode, '250100', '280100');
        $labaRkap = (float) (DB::selectOne(
            'SELECT SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE PERIODE = ?',
            [$periode]
        )?->NILAI ?? 0);

        return [
            'penjualan' => [
                'real' => FormatHelper::redenominasi($penjualan),
                'rkap' => $penjualanRkap,
                'capaian' => FormatHelper::targetPersen($penjualanRkap, FormatHelper::redenominasi($penjualan)),
                'naik' => $penjualan >= $penjualanSbl,
                'mom' => $penjualanSbl != 0 ? FormatHelper::mask((($penjualan - $penjualanSbl) / $penjualanSbl) * 100) : 0,
            ],
            'laba_kotor' => [
                'real' => FormatHelper::redenominasi($labkor),
                'rkap' => $labkorRkap,
                'capaian' => FormatHelper::targetPersen($labkorRkap, FormatHelper::redenominasi($labkor)),
                'gpm' => $penjualan != 0 ? FormatHelper::mask(($labkor / $penjualan) * 100) : 0,
            ],
            'laba_operasi' => [
                'real' => FormatHelper::redenominasi($labops),
                'rkap' => $labopsRkap,
                'capaian' => FormatHelper::targetPersen($labopsRkap, FormatHelper::redenominasi($labops)),
                'opm' => $penjualan != 0 ? FormatHelper::mask(($labops / $penjualan) * 100) : 0,
            ],
            'laba_bersih' => [
                'real' => FormatHelper::redenominasi($laba),
                'rkap' => $labaRkap,
                'capaian' => FormatHelper::targetPersen($labaRkap, FormatHelper::redenominasi($laba)),
                'npm' => $penjualan != 0 ? FormatHelper::mask(($laba / $penjualan) * 100) : 0,
            ],
        ];
    }

    public function seriesPenjualanTahunan(): array
    {
        $tahun = now()->format('Y');
        $tahunSbl = now()->subYear()->format('Y');

        $real = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT = '250100' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $rkap = DB::select(
            "SELECT PERIODE, SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE ID_REPORT = 'X01' AND TAHUN = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $sbl = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT = '250100' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahunSbl]
        );

        return [
            'real' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $real),
            'rkap' => array_map(fn ($r) => $r->NILAI, $rkap),
            'sbl' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $sbl),
        ];
    }

    public function seriesLabaTahunan(): array
    {
        $tahun = now()->format('Y');
        $tahunSbl = now()->subYear()->format('Y');

        $real = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT >= '250100' AND ACCOUNT <= '250409' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $rkap = DB::select(
            "SELECT PERIODE, SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE TAHUN = ? AND ID_REPORT IN ('X01', 'X02')
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $sbl = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT >= '250100' AND ACCOUNT <= '250409' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahunSbl]
        );

        return [
            'real' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $real),
            'rkap' => array_map(fn ($r) => $r->NILAI, $rkap),
            'sbl' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $sbl),
        ];
    }

    public function chartTahunanDetail(string $opt): array
    {
        $tahun = now()->format('Y');
        $idReport = $opt === 'labkor' ? "'X01', 'X02'" : "'X01'";
        $label = $opt === 'labkor' ? 'Laba Kotor' : 'Penjualan';

        $rows = DB::select(
            "SELECT TAHUN, PERIODE, ? LABEL1, SUM(RKAP) RKAP, SUM(AMOUNT) AMOUNT
             FROM (
                 SELECT TAHUN, PERIODE, ID_REPORT, NILAI RKAP,
                     ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                      WHERE A.periode = DASH.MST_RKAP.PERIODE AND B.ID_REPORT = DASH.MST_RKAP.ID_REPORT), 0) AMOUNT
                 FROM DASH.MST_RKAP
                 WHERE TAHUN = ? AND ID_REPORT IN ($idReport)
             ) A
             GROUP BY TAHUN, PERIODE
             ORDER BY PERIODE",
            [$label, $tahun]
        );

        return array_map(fn ($rec) => [
            'AMOUNT' => $rec->AMOUNT,
            'RKAP' => $rec->RKAP,
            'LABEL1' => trim($rec->LABEL1),
        ], $rows);
    }
}

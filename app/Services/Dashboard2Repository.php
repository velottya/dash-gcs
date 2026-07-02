<?php

namespace App\Services;

use App\Support\FormatHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard2Repository
{
    public function kpiTahunan(string $periode): array
    {
        $periodeSbl = Carbon::createFromFormat('Ym', $periode)->subMonth()->format('Ym');

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
                'gpm' => $penjualan != 0 ? FormatHelper::percent(($labkor / $penjualan) * 100) : '0,00',
            ],
            'laba_operasi' => [
                'real' => FormatHelper::redenominasi($labops),
                'rkap' => $labopsRkap,
                'capaian' => FormatHelper::targetPersen($labopsRkap, FormatHelper::redenominasi($labops)),
                'opm' => $penjualan != 0 ? FormatHelper::percent(($labops / $penjualan) * 100) : '0,00',
            ],
            'laba_bersih' => [
                'real' => FormatHelper::redenominasi($laba),
                'rkap' => $labaRkap,
                'capaian' => FormatHelper::targetPersen($labaRkap, FormatHelper::redenominasi($laba)),
                'npm' => $penjualan != 0 ? FormatHelper::percent(($laba / $penjualan) * 100) : '0,00',
            ],
        ];
    }

    public function seriesPenjualanTahunan(string $tahun): array
    {
        $tahunSbl = (string) (((int) $tahun) - 1);

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

    public function seriesLabaTahunan(string $tahun): array
    {
        $tahunSbl = (string) (((int) $tahun) - 1);

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

    public function seriesLabaOperasiTahunan(string $tahun): array
    {
        $tahunSbl = (string) (((int) $tahun) - 1);

        $real = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT >= '250100' AND ACCOUNT <= '260232' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $rkap = DB::select(
            "SELECT PERIODE, SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE TAHUN = ? AND ID_REPORT IN ('X01', 'X02', 'X03', 'X15')
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $sbl = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT >= '250100' AND ACCOUNT <= '260232' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahunSbl]
        );

        return [
            'real' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $real),
            'rkap' => array_map(fn ($r) => $r->NILAI, $rkap),
            'sbl' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $sbl),
        ];
    }

    public function seriesLabaBersihTahunan(string $tahun): array
    {
        $tahunSbl = (string) (((int) $tahun) - 1);

        $real = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT >= '250100' AND ACCOUNT <= '280100' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $rkap = DB::select(
            "SELECT PERIODE, SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE TAHUN = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahun]
        );
        $sbl = DB::select(
            "SELECT PERIODE, SUM(AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT >= '250100' AND ACCOUNT <= '280100' AND YEAR(TANGGAL) = ?
             GROUP BY PERIODE ORDER BY PERIODE",
            [$tahunSbl]
        );

        return [
            'real' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $real),
            'rkap' => array_map(fn ($r) => $r->NILAI, $rkap),
            'sbl' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $sbl),
        ];
    }

    public function seriesBebanPemasaranTahunan(string $tahun): array
    {
        return $this->seriesBebanTahunan($tahun, 'LR06', 'BEBAN PEMASARAN');
    }

    public function seriesBebanUmumTahunan(string $tahun): array
    {
        return $this->seriesBebanTahunan($tahun, 'LR05', 'BEBAN UMUM DAN ADMINISTRASI');
    }

    /**
     * Monthly Real/RKAP/Thn Sbl series for an expense category, keyed off
     * AKUN_ACCOUNT.REPORT_NR (actual GL) and DASH.MST_RKAP.SUB_LAPORAN (budget),
     * mirroring LabarRepository::ratioAnalysis()'s code mapping.
     */
    private function seriesBebanTahunan(string $tahun, string $reportNr, string $subLaporan): array
    {
        $tahunSbl = (string) (((int) $tahun) - 1);

        $real = DB::select(
            "SELECT A.PERIODE, SUM(A.AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT
             WHERE B.REPORT_NR = ? AND YEAR(A.TANGGAL) = ?
             GROUP BY A.PERIODE ORDER BY A.PERIODE",
            [$reportNr, $tahun]
        );
        $rkap = DB::select(
            'SELECT PERIODE, SUM(NILAI) NILAI FROM DASH.MST_RKAP WHERE SUB_LAPORAN = ? AND TAHUN = ?
             GROUP BY PERIODE ORDER BY PERIODE',
            [$subLaporan, $tahun]
        );
        $sbl = DB::select(
            "SELECT A.PERIODE, SUM(A.AMOUNT) * -1 NILAI FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT
             WHERE B.REPORT_NR = ? AND YEAR(A.TANGGAL) = ?
             GROUP BY A.PERIODE ORDER BY A.PERIODE",
            [$reportNr, $tahunSbl]
        );

        return [
            'real' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $real),
            'rkap' => array_map(fn ($r) => $r->NILAI, $rkap),
            'sbl' => array_map(fn ($r) => FormatHelper::redenominasiFloat($r->NILAI), $sbl),
        ];
    }

    /**
     * Realisasi penjualan dan beban pemasaran/umum per wilayah untuk periode
     * terpilih. RKAP tidak punya dimensi wilayah yang bisa diandalkan
     * (DASH.MST_RKAP tidak punya kolom wilayah, dan DASH.MST_RKAP_SEKTOR.WILAYAH
     * kosong/NULL untuk periode berjalan), jadi semua nilai di sini murni
     * realisasi tanpa target RKAP yang dikarang. INVENTORY.WILAYAH disimpan
     * UPPERCASE sementara AKUN_ACCOUNT_HCC.SUBWIL disimpan Title Case — match
     * dengan strtoupper() di kedua sisi.
     */
    public function wilayahCards(string $periode): array
    {
        $wilayahList = ['Jawa', 'Lampung', 'Makassar', 'Medan'];

        $real = DB::select(
            "SELECT B.WILAYAH, SUM(A.AMOUNT) * -1 NILAI
             FROM AKUN_JURNAL_DETAIL A INNER JOIN INVENTORY B ON A.OBJECTID = B.REFSTOCK
             WHERE A.ACCOUNT = '250100' AND A.PERIODE = ? AND B.WILAYAH IS NOT NULL
             GROUP BY B.WILAYAH",
            [$periode]
        );
        $bebanPemasaran = DB::select(
            "SELECT C.SUBWIL, SUM(A.AMOUNT) * -1 NILAI
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT
                 INNER JOIN AKUN_ACCOUNT_CC CC ON A.KODE_CC = CC.KODE_CC
                 INNER JOIN AKUN_ACCOUNT_HCC C ON C.GRP_A = CC.GRP_A
             WHERE B.REPORT_NR = 'LR06' AND A.PERIODE = ?
             GROUP BY C.SUBWIL",
            [$periode]
        );
        $bebanUmum = DB::select(
            "SELECT C.SUBWIL, SUM(A.AMOUNT) * -1 NILAI
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT
                 INNER JOIN AKUN_ACCOUNT_CC CC ON A.KODE_CC = CC.KODE_CC
                 INNER JOIN AKUN_ACCOUNT_HCC C ON C.GRP_A = CC.GRP_A
             WHERE B.REPORT_NR = 'LR05' AND A.PERIODE = ?
             GROUP BY C.SUBWIL",
            [$periode]
        );

        $indexBy = fn (array $rows, string $key) => collect($rows)->mapWithKeys(
            fn ($r) => [strtoupper(trim($r->$key)) => (float) $r->NILAI]
        )->all();

        $realByWilayah = $indexBy($real, 'WILAYAH');
        $bpByWilayah = $indexBy($bebanPemasaran, 'SUBWIL');
        $buaByWilayah = $indexBy($bebanUmum, 'SUBWIL');

        return array_map(function (string $wilayah) use ($realByWilayah, $bpByWilayah, $buaByWilayah) {
            $key = strtoupper($wilayah);

            return [
                'wilayah' => $wilayah,
                'real' => FormatHelper::redenominasi($realByWilayah[$key] ?? 0),
                'beban_pemasaran' => FormatHelper::redenominasi($bpByWilayah[$key] ?? 0),
                'beban_umum' => FormatHelper::redenominasi($buaByWilayah[$key] ?? 0),
            ];
        }, $wilayahList);
    }

    /**
     * Total penjualan per sektor untuk seluruh tahun terpilih (bukan bulan
     * berjalan), dipakai oleh tab "Per Sektor" di modal Detail Penjualan.
     */
    public function sektorTahunan(int $tahun): array
    {
        $rows = DB::select(
            "SELECT B.KODE_SEKTOR SUB_GROUP, SUM(A.AMOUNT) * -1 AMOUNT
             FROM AKUN_JURNAL_DETAIL A INNER JOIN INVENTORY B ON A.OBJECTID = B.REFSTOCK
             WHERE A.ACCOUNT = '250100' AND YEAR(A.TANGGAL) = ? AND B.KODE_SEKTOR != ''
             GROUP BY B.KODE_SEKTOR
             ORDER BY AMOUNT DESC",
            [$tahun]
        );

        return array_map(fn ($r) => ['SUB_GROUP' => trim((string) $r->SUB_GROUP), 'AMOUNT' => FormatHelper::redenominasi($r->AMOUNT)], $rows);
    }

    /**
     * Total penjualan per wilayah untuk seluruh tahun terpilih, dipakai oleh
     * tab "Per Wilayah" di modal Detail Penjualan.
     */
    public function wilayahTahunan(int $tahun): array
    {
        $rows = DB::select(
            "SELECT C.SUBWIL SUB_GROUP, SUM(A.AMOUNT) * -1 AMOUNT
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT_CC B ON A.KODE_CC = B.KODE_CC
                 INNER JOIN AKUN_ACCOUNT_HCC C ON C.GRP_A = B.GRP_A
             WHERE A.ACCOUNT = '250100' AND YEAR(A.TANGGAL) = ?
             GROUP BY C.SUBWIL
             ORDER BY AMOUNT DESC",
            [$tahun]
        );

        return array_map(fn ($r) => ['SUB_GROUP' => trim((string) $r->SUB_GROUP), 'AMOUNT' => FormatHelper::redenominasi($r->AMOUNT)], $rows);
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

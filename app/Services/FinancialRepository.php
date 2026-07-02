<?php

namespace App\Services;

use App\Support\FormatHelper;
use Illuminate\Support\Facades\DB;

class FinancialRepository
{
    /**
     * Saldo Neraca (Aset/Liabilitas/Ekuitas) per bulan tahun berjalan, dihitung
     * dari saldo kumulatif AKUN_JURNAL_DETAIL (saldo awal tahun + pergerakan
     * bulanan), dikelompokkan lewat AKUN_ACCOUNT.Report_NR -> dash.MST_REPORT.
     */
    public function neracaSeries(): array
    {
        $tahun = now()->format('Y');
        $awalTahun = $tahun.'01';

        $opening = $this->neracaKategoriMap(DB::select(
            "SELECT R.SUB_LAPORAN, SUM(CASE WHEN A.DK = 'D' THEN J.AMOUNT ELSE J.AMOUNT * -1 END) NILAI
             FROM dbo.AKUN_JURNAL_DETAIL J
             INNER JOIN dbo.AKUN_ACCOUNT A ON A.Account = J.ACCOUNT
             INNER JOIN dash.MST_REPORT R ON R.REPORT_NR = A.Report_NR AND R.LAPORAN = 'NERACA'
             WHERE J.PERIODE < ?
             GROUP BY R.SUB_LAPORAN",
            [$awalTahun]
        ));

        $movements = DB::select(
            "SELECT J.PERIODE, R.SUB_LAPORAN, SUM(CASE WHEN A.DK = 'D' THEN J.AMOUNT ELSE J.AMOUNT * -1 END) NILAI
             FROM dbo.AKUN_JURNAL_DETAIL J
             INNER JOIN dbo.AKUN_ACCOUNT A ON A.Account = J.ACCOUNT
             INNER JOIN dash.MST_REPORT R ON R.REPORT_NR = A.Report_NR AND R.LAPORAN = 'NERACA'
             WHERE J.PERIODE >= ? AND J.PERIODE <= ?
             GROUP BY J.PERIODE, R.SUB_LAPORAN
             ORDER BY J.PERIODE",
            [$awalTahun, $tahun.'12']
        );

        $running = ['assets' => $opening['assets'], 'liability' => $opening['liability'], 'equity' => $opening['equity']];
        $series = ['assets' => [], 'liability' => [], 'equity' => []];

        $byPeriode = [];
        foreach ($movements as $row) {
            $byPeriode[$row->PERIODE][] = $row;
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $periode = $tahun.str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);
            $kategori = $this->neracaKategoriMap($byPeriode[$periode] ?? []);

            foreach (['assets', 'liability', 'equity'] as $key) {
                $running[$key] += $kategori[$key];
                $series[$key][] = FormatHelper::redenominasiFloat($running[$key]);
            }
        }

        return $series;
    }

    /**
     * Komponen Laba Rugi (Pendapatan, HPP, Beban Usaha, Beban Keuangan,
     * Pendapatan/Beban Lain, Beban Pajak) per bulan tahun berjalan, dari
     * AKUN_JURNAL_DETAIL via AKUN_ACCOUNT.ID_REPORT (X01-X15) - pola yang
     * sudah dipakai Dashboard2Repository::kpiTahunan().
     */
    public function labaRugiSeries(): array
    {
        $tahun = now()->format('Y');

        $labels = [
            'X01' => 'pendapatan',
            'X02' => 'hpp',
            'X03' => 'beban_usaha',
            'X15' => 'beban_usaha',
            'X05' => 'beban_keuangan',
            'X06' => 'lain_lain',
            'X08' => 'beban_pajak',
        ];

        $rows = DB::select(
            "SELECT J.PERIODE, A.ID_REPORT, SUM(J.AMOUNT) * -1 NILAI
             FROM dbo.AKUN_JURNAL_DETAIL J
             INNER JOIN dbo.AKUN_ACCOUNT A ON A.Account = J.ACCOUNT
             WHERE J.PERIODE >= ? AND J.PERIODE <= ? AND LTRIM(RTRIM(A.ID_REPORT)) IN ('X01','X02','X03','X05','X06','X08','X15')
             GROUP BY J.PERIODE, A.ID_REPORT
             ORDER BY J.PERIODE",
            [$tahun.'01', $tahun.'12']
        );

        $byPeriode = [];
        foreach ($rows as $row) {
            $idReport = trim($row->ID_REPORT);
            $key = $labels[$idReport] ?? null;
            if ($key === null) {
                continue;
            }
            $byPeriode[$row->PERIODE][$key] = ($byPeriode[$row->PERIODE][$key] ?? 0) + $row->NILAI;
        }

        $series = ['pendapatan' => [], 'hpp' => [], 'beban_usaha' => [], 'beban_keuangan' => [], 'lain_lain' => [], 'beban_pajak' => [], 'laba_bersih' => []];
        $kumulatif = 0;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $periode = $tahun.str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);
            $bulanIni = $byPeriode[$periode] ?? [];

            $totalBulan = 0;
            foreach (['pendapatan', 'hpp', 'beban_usaha', 'beban_keuangan', 'lain_lain', 'beban_pajak'] as $key) {
                $nilai = $bulanIni[$key] ?? 0;
                $series[$key][] = FormatHelper::redenominasiFloat($nilai);
                $totalBulan += $nilai;
            }

            $kumulatif += $totalBulan;
            $series['laba_bersih'][] = FormatHelper::redenominasiFloat($kumulatif);
        }

        return $series;
    }

    /**
     * Arus kas per bulan tahun berjalan (Operasional/Investasi/Pendanaan),
     * dari AKUN_JURNAL_DETAIL via AKUN_ACCOUNT.kelompok_arus.
     */
    public function arusKasSeries(): array
    {
        $tahun = now()->format('Y');

        $labels = [
            'Arus Kas Aktivitas Operasional' => 'operasional',
            'Arus Kas Aktivitas Investasi' => 'investasi',
            'Arus Kas Aktivitas Pendanaan' => 'pendanaan',
        ];

        $rows = DB::select(
            "SELECT J.PERIODE, A.kelompok_arus KELOMPOK, SUM(CASE WHEN A.DK = 'D' THEN J.AMOUNT ELSE J.AMOUNT * -1 END) NILAI
             FROM dbo.AKUN_JURNAL_DETAIL J
             INNER JOIN dbo.AKUN_ACCOUNT A ON A.Account = J.ACCOUNT
             WHERE J.PERIODE >= ? AND J.PERIODE <= ? AND LTRIM(RTRIM(ISNULL(A.kelompok_arus, ''))) <> ''
             GROUP BY J.PERIODE, A.kelompok_arus
             ORDER BY J.PERIODE",
            [$tahun.'01', $tahun.'12']
        );

        $byPeriode = [];
        foreach ($rows as $row) {
            $key = $labels[trim($row->KELOMPOK)] ?? null;
            if ($key === null) {
                continue;
            }
            $byPeriode[$row->PERIODE][$key] = ($byPeriode[$row->PERIODE][$key] ?? 0) + $row->NILAI;
        }

        $series = ['operasional' => [], 'investasi' => [], 'pendanaan' => []];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $periode = $tahun.str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);
            $bulanIni = $byPeriode[$periode] ?? [];

            foreach (array_keys($series) as $key) {
                $series[$key][] = FormatHelper::redenominasiFloat($bulanIni[$key] ?? 0);
            }
        }

        return $series;
    }

    /**
     * @param  array<int, object>  $rows
     * @return array{assets: float, liability: float, equity: float}
     */
    private function neracaKategoriMap(array $rows): array
    {
        $kategori = ['assets' => 0.0, 'liability' => 0.0, 'equity' => 0.0];

        foreach ($rows as $row) {
            $sub = trim($row->SUB_LAPORAN);
            $key = match (true) {
                str_contains($sub, 'LIABILITAS') => 'liability',
                $sub === 'EKUITAS' => 'equity',
                default => 'assets',
            };
            $kategori[$key] += (float) $row->NILAI;
        }

        return $kategori;
    }
}

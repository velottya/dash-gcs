<?php

namespace App\Services;

use App\Support\FormatHelper;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function lastLogin(string $username): ?string
    {
        $row = DB::selectOne('SELECT LAST_LOGIN FROM DASH.MST_USER WHERE USERNAME = ?', [$username]);

        return $row?->LAST_LOGIN;
    }

    public function pegawai(string $nik): ?object
    {
        return DB::selectOne('SELECT NAMA, NIK, NM_JABATAN FROM PEGAWAI_SDM WHERE NIK = ?', [$nik]);
    }

    public function piutangJatuhTempo(): array
    {
        return DB::select(
            "SELECT KODEREKANAN, NAMA, SUM(OPENAMOUNT) NILAI
             FROM VIEW_AGING_PIUTANG WHERE TGL_JTEMPO < CONVERT(DATE, GETDATE())
             GROUP BY NAMA, KODEREKANAN
             ORDER BY SUM(OpenAmount) DESC"
        );
    }

    public function penjualanHariIniTotal(): float
    {
        $periode = FormatHelper::periodeTrans();

        $row = DB::selectOne(
            "SELECT ISNULL(SUM(AMOUNT * -1), 0) AMOUNT FROM AKUN_JURNAL_DETAIL
             WHERE PERIODE = ? AND ACCOUNT = '250100' AND CONVERT(DATE, TGL_POST) = CONVERT(DATE, GETDATE())",
            [$periode]
        );

        return (float) FormatHelper::redenominasiFloat($row->AMOUNT ?? 0);
    }

    /**
     * Headline KPI row: penjualan, HPP, dan laba kotor bulan berjalan vs RKAP.
     */
    public function kpiBulanIni(string $periode): array
    {
        $penjualan = DB::selectOne(
            "SELECT ISNULL(SUM(AMOUNT * -1), 0) AMOUNT FROM AKUN_JURNAL_DETAIL WHERE PERIODE = ? AND ACCOUNT = '250100'",
            [$periode]
        )->AMOUNT;
        $penjualanReal = FormatHelper::redenominasi($penjualan);

        $rkapPenjualan = DB::selectOne(
            "SELECT NILAI FROM DASH.MST_RKAP WHERE PERIODE = ? AND ID_REPORT = 'X01'",
            [$periode]
        )?->NILAI ?? 0;

        $hpp = DB::selectOne(
            "SELECT ISNULL(SUM(AMOUNT * -1), 0) AMOUNT
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.ACCOUNT
                 INNER JOIN DASH.MST_REPORT C ON C.REPORT_NR = B.REPORT_NR
             WHERE A.PERIODE = ? AND C.ID_URAIAN = 'X02'",
            [$periode]
        )->AMOUNT;
        $hppReal = FormatHelper::redenominasi($hpp);

        $rkapHpp = DB::selectOne(
            "SELECT NILAI FROM DASH.MST_RKAP WHERE PERIODE = ? AND ID_REPORT = 'X02'",
            [$periode]
        )?->NILAI ?? 0;

        $labaKotorReal = $penjualanReal + $hppReal;
        $labaKotorRkap = $rkapPenjualan + $rkapHpp;

        return [
            'periode_label' => FormatHelper::getPeriodeTrans($periode),
            'penjualan' => ['real' => $penjualanReal, 'rkap' => $rkapPenjualan, 'capaian' => FormatHelper::targetPersen($rkapPenjualan, $penjualanReal)],
            'hpp' => ['real' => $hppReal, 'rkap' => $rkapHpp, 'capaian' => FormatHelper::targetPersen($rkapHpp, $hppReal)],
            'laba_kotor' => ['real' => $labaKotorReal, 'rkap' => $labaKotorRkap, 'capaian' => FormatHelper::targetPersen($labaKotorRkap, $labaKotorReal)],
        ];
    }

    /**
     * Pencapaian penjualan per sektor pada periode berjalan, dengan tombol
     * drill-down ke sektorDetail() (sama seperti tabel sektor di Dashboard1 lama).
     */
    public function sektorAchievement(string $periode): array
    {
        $rows = DB::select(
            "SELECT ID_SEKTOR, KODE_SEKTOR, SUM(NILAI) RKAP,
                 (SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL A INNER JOIN INVENTORY B ON A.OBJECTID = B.REFSTOCK
                  WHERE ACCOUNT = '250100' AND PERIODE = ? AND B.KODE_SEKTOR = MAIN_SEKTOR.KODE_SEKTOR) NILAI_REAL
             FROM DASH.MST_RKAP_SEKTOR MAIN_SEKTOR
             WHERE PERIODE = ?
             GROUP BY KODE_SEKTOR, ID_SEKTOR
             ORDER BY ID_SEKTOR",
            [$periode, $periode]
        );

        return array_map(function ($row) {
            $nilaiReal = $row->NILAI_REAL ?: 0;
            $capaian = FormatHelper::targetPersen($row->RKAP, FormatHelper::redenominasi($nilaiReal ?: 1));

            return [
                'id_sektor' => trim($row->ID_SEKTOR),
                'kode_sektor' => trim($row->KODE_SEKTOR),
                'rkap' => $row->RKAP,
                'real' => FormatHelper::redenominasi($nilaiReal),
                'capaian' => $capaian,
            ];
        }, $rows);
    }

    public function sektorDetail(string $idSektor, string $periode): array
    {
        $rows = DB::select(
            "SELECT SUBWIL, KODE_SEKTOR, NAMA_BARANG, QTY_REAL, QTY_RKAP, SATUAN, NILAI_REAL, NILAI_RKAP
             FROM (
                 SELECT WILAYAH SUBWIL, KODE_SEKTOR, NAMA_BARANG,
                     ISNULL((SELECT SUM(QTY) FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT = '250100' AND OBJECTID = INVENTORY.REFSTOCK AND PERIODE = ?), 0) QTY_REAL,
                     ISNULL((SELECT QTY FROM DASH.MST_RKAP_SEKTOR WHERE REFSTOCK = INVENTORY.REFSTOCK AND PERIODE = ?), 0) QTY_RKAP,
                     SATUAN,
                     ISNULL((SELECT SUM(AMOUNT) * -1 FROM AKUN_JURNAL_DETAIL WHERE ACCOUNT = '250100' AND OBJECTID = INVENTORY.REFSTOCK AND PERIODE = ?), 0) NILAI_REAL,
                     ISNULL((SELECT NILAI FROM DASH.MST_RKAP_SEKTOR WHERE REFSTOCK = INVENTORY.REFSTOCK AND PERIODE = ?), 0) NILAI_RKAP
                 FROM INVENTORY WHERE WILAYAH IS NOT NULL AND ID_SEKTOR = ?
             ) A WHERE QTY_REAL != 0 OR QTY_RKAP != 0 OR NILAI_REAL != 0 OR NILAI_RKAP != 0
             ORDER BY SUBWIL, NILAI_REAL DESC",
            [$periode, $periode, $periode, $periode, $idSektor]
        );

        return array_map(function ($rec) {
            $capaianQty = FormatHelper::targetPersen($rec->QTY_RKAP, $rec->QTY_REAL);
            $capaianNilai = FormatHelper::targetPersen($rec->NILAI_RKAP, FormatHelper::redenominasi($rec->NILAI_REAL));

            return [
                'JUMLAH' => trim($rec->SUBWIL),
                // Nama/kode dipertukarkan persis seperti controller CI asli (Dashboard1::detail1).
                'NAMA_SEKTOR' => trim($rec->KODE_SEKTOR),
                'KODE_SEKTOR' => trim($rec->SUBWIL),
                'NAMA_BARANG' => ucwords(strtolower($rec->NAMA_BARANG)),
                'SATUAN' => ucwords(strtolower($rec->SATUAN)),
                'QTY_REAL' => $rec->QTY_REAL,
                'QTY_RKAP' => $rec->QTY_RKAP,
                'NILAI_REAL' => $rec->NILAI_REAL / 1000,
                'NILAI_RKAP' => $rec->NILAI_RKAP,
                'CAP_QTY' => $capaianQty,
                'CAP_NILAI' => $capaianNilai,
            ];
        }, $rows);
    }

    public function penjualanHariIni(): array
    {
        $periode = FormatHelper::periodeTrans();

        $rows = DB::select(
            "SELECT B.KODE_SEKTOR, B.NAMA_BARANG, SUM(QTY) QTY, B.SATUAN, SUM(A.AMOUNT) * -1 NILAI
             FROM AKUN_JURNAL_DETAIL A INNER JOIN INVENTORY B ON A.OBJECTID = B.REFSTOCK
             WHERE CONVERT(DATE, TGL_POST) = CONVERT(DATE, GETDATE()) AND ACCOUNT = '250100' AND PERIODE = ?
             GROUP BY B.NAMA_BARANG, B.SATUAN, B.KODE_SEKTOR
             ORDER BY SUM(A.AMOUNT)",
            [$periode]
        );

        return array_map(fn ($rec) => [
            'KODE_SEKTOR' => trim($rec->KODE_SEKTOR),
            'NAMA_BARANG' => ucwords(strtolower($rec->NAMA_BARANG)),
            'QTY' => $rec->QTY,
            'SATUAN' => ucwords(strtolower($rec->SATUAN)),
            'NILAI' => $rec->NILAI / 1000,
        ], $rows);
    }

    public function agingDetailByRekanan(string $koderekanan): array
    {
        $rows = DB::select(
            "SELECT KODEREKANAN, NAMA, NOFAKTUR, NOOK, TANGGAL, TGL_JTEMPO, JTH1_30, JTH31_60, JTH60_90, JTH90_365, JTH365, NOPEMB,
                 (SELECT WILAYAH FROM AKUN_FAKTUR_DETAIL A INNER JOIN AKUN_ACCOUNT_CC B ON A.KODE_CC = B.KODE_CC
                  WHERE A.NOFAKTUR = VIEW_AGING_PIUTANG.NOFAKTUR AND A.URUT = '01') WILAYAH
             FROM VIEW_AGING_PIUTANG
             WHERE KODEREKANAN = ? AND TGL_JTEMPO < CONVERT(DATE, GETDATE())
             ORDER BY TGL_JTEMPO",
            [$koderekanan]
        );

        return array_map(fn ($rec) => [
            'KODEREKANAN' => trim($rec->KODEREKANAN),
            'NAMA' => trim($rec->NAMA),
            'NOPEMB' => trim($rec->NOPEMB),
            'WILAYAH' => trim((string) $rec->WILAYAH),
            'FAKTUR_DETAIL' => trim($rec->NOOK),
            'NOFAKTUR' => trim($rec->NOOK).'/'.trim($rec->NOFAKTUR),
            'TANGGAL' => date('d/m/Y', strtotime($rec->TANGGAL)),
            'TGL_JTEMPO' => date('d/m/Y', strtotime($rec->TGL_JTEMPO)),
            'JTH1_30' => $rec->JTH1_30,
            'JTH31_60' => $rec->JTH31_60,
            'JTH61_90' => $rec->JTH60_90,
            'JTH91_365' => $rec->JTH90_365,
            'JTH365' => $rec->JTH365,
        ], $rows);
    }

    public function fakturDetail(string $nofaktur): array
    {
        $header = DB::selectOne(
            "SELECT NOOK NOMOR_FPB, TANGGAL TGL_FPB, B.NAMA, AMOUNT NILAI_FPB, TOTNILPPN NILAI_PPN_FPB, AMOUNT + TOTNILPPN NILAI_TOTAL,
                 SYARATBAYAR SYARAT_PEMBAYARAN, TRANSPORT, HARI, NOPEMB REF_NO, TGL_JTEMPO, NOFAKPPN, TGLFAKPPN, NOFAKTUR
             FROM AKUN_FAKTUR A INNER JOIN AKUN_REKANAN B ON A.KODEREKANAN = B.KODEREKANAN
             WHERE NOOK = ? AND STATUS NOT IN ('PLANNED', 'CANCEL')",
            [$nofaktur]
        );

        if (! $header) {
            return ['header' => null, 'detail' => []];
        }

        $nopiutang = trim($header->NOFAKTUR);

        $detailRows = DB::select(
            "SELECT URUT, A.KETERANGAN, QTY QTY_JUAL, SATUAN, NILAI HARGA_SATUAN, TOTAL NILAI, B.WILAYAH, NOMOR_DO
             FROM AKUN_FAKTUR_DETAIL A INNER JOIN AKUN_ACCOUNT_CC B ON A.KODE_CC = B.KODE_CC
             WHERE NOFAKTUR = ?",
            [$nopiutang]
        );

        return [
            'header' => [
                'NOMOR_FPB' => trim($header->NOMOR_FPB),
                'TGL_FPB' => date('d/m/Y', strtotime($header->TGL_FPB)),
                'TGL_JTEMPO' => date('d/m/Y', strtotime($header->TGL_JTEMPO)),
                'NAMA' => trim($header->NAMA),
                'NILAI_FPB' => $header->NILAI_FPB,
                'NILAI_PPN_FPB' => $header->NILAI_PPN_FPB,
                'NILAI_TOTAL' => $header->NILAI_TOTAL,
                'SYARAT_PEMBAYARAN' => trim($header->SYARAT_PEMBAYARAN),
                'TRANSPORT' => trim((string) $header->TRANSPORT),
                'HARI' => $header->HARI,
                'REF_NO' => trim($header->REF_NO),
                'NOFAKPPN' => trim((string) $header->NOFAKPPN),
                'TGLFAKPPN' => $header->TGLFAKPPN ? date('d/m/Y', strtotime($header->TGLFAKPPN)) : null,
                'NOFAKTUR' => trim($header->NOFAKTUR),
            ],
            'detail' => array_map(fn ($rec) => [
                'URUT' => trim($rec->URUT),
                'KETERANGAN' => trim($rec->KETERANGAN),
                'QTY_JUAL' => $rec->QTY_JUAL,
                'SATUAN' => trim($rec->SATUAN),
                'HARGA_SATUAN' => $rec->HARGA_SATUAN,
                'NILAI' => $rec->NILAI,
                'WILAYAH' => trim((string) $rec->WILAYAH),
                'NOMOR_DO' => trim((string) $rec->NOMOR_DO),
            ], $detailRows),
        ];
    }
}

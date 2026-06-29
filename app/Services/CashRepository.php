<?php

namespace App\Services;

use App\Support\FormatHelper;
use Illuminate\Support\Facades\DB;

class CashRepository
{
    public function cashInPerWilayah(): array
    {
        $periode = FormatHelper::periodeTrans();
        $tahun = now()->format('Y');

        return DB::select(
            "SELECT B.WILAYAH, B.KODE_CC,
                 (SELECT SUM(AMOUNT) FROM AKUN_JURNAL_DETAIL X INNER JOIN AKUN_ACCOUNT Y ON X.ACCOUNT = Y.Account
                  WHERE X.PERIODE = ? AND X.KODE_CC = B.KODE_CC AND Y.GRP_A = '1102' AND
                      (NODOKUMEN IN (SELECT NOKASBON FROM AKUN_UANGMUKA WHERE STATUS NOT IN ('Planned', 'CANCEL') AND JENIS = 'Penjualan')
                       OR (NODOKUMEN IN (SELECT NOSLIP FROM AKUN_KASBANK WHERE STATUS = 'Posted' AND JNSTRANS IN ('KAS MASUK', 'BANK MASUK'))))
                      AND KODEREKANAN IN (SELECT KODEREKANAN FROM AKUN_REKANAN)
                 ) NILAI,
                 (SELECT SUM(AMOUNT) FROM AKUN_JURNAL_DETAIL X INNER JOIN AKUN_ACCOUNT Y ON X.ACCOUNT = Y.Account
                  WHERE YEAR(X.TANGGAL) = ? AND X.KODE_CC = B.KODE_CC AND Y.GRP_A = '1102' AND
                      (NODOKUMEN IN (SELECT NOKASBON FROM AKUN_UANGMUKA WHERE STATUS NOT IN ('Planned', 'CANCEL') AND JENIS = 'Penjualan')
                       OR (NODOKUMEN IN (SELECT NOSLIP FROM AKUN_KASBANK WHERE STATUS = 'Posted' AND JNSTRANS IN ('KAS MASUK', 'BANK MASUK'))))
                      AND KODEREKANAN IN (SELECT KODEREKANAN FROM AKUN_REKANAN)
                 ) SDBLN
             FROM Akun_Account_CC B WHERE KODE_CC IN ('100000', '200000', '300000', '400000')
             ORDER BY B.KODE_CC",
            [$periode, $tahun]
        );
    }

    public function top5CustomerByJatuhTempo(): array
    {
        $periode = FormatHelper::periodeTrans();

        return DB::select(
            "SELECT TOP(5) A.KODEREKANAN, C.NAMA, SUM(AMOUNT) NILAI,
                 ISNULL((SELECT SUM(X.OpenAmount)
                  FROM AKUN_FAKTUR X INNER JOIN AKUN_TAGIHAN_DETAIL Y ON X.NoFaktur = Y.NOFAKTUR
                  WHERE X.STATUS NOT IN ('CANCEL', 'Planned', 'Paid') AND TGL_JTEMPO <= GETDATE() AND X.KodeRekanan = A.KODEREKANAN), 0) JTEMPO
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                 INNER JOIN AKUN_REKANAN C ON A.KODEREKANAN = C.KODEREKANAN
             WHERE B.GRP_A = '1102' AND A.PERIODE = ? AND
                 (NODOKUMEN IN (SELECT NOKASBON FROM AKUN_UANGMUKA WHERE STATUS NOT IN ('Planned', 'CANCEL') AND JENIS = 'Penjualan')
                  OR (NODOKUMEN IN (SELECT NOSLIP FROM AKUN_KASBANK WHERE STATUS = 'Posted' AND JNSTRANS IN ('BANK MASUK'))))
             GROUP BY A.KODEREKANAN, C.NAMA
             ORDER BY JTEMPO DESC",
            [$periode]
        );
    }

    public function piutangVsCashInPerCustomer(): array
    {
        $periode = FormatHelper::periodeTrans();

        $rows = DB::select(
            "SELECT A.KODEREKANAN, C.NAMA, ISNULL(SUM(AMOUNT), 0) NILAI,
                 ISNULL((SELECT SUM(X.OpenAmount)
                  FROM AKUN_FAKTUR X INNER JOIN AKUN_TAGIHAN_DETAIL Y ON X.NoFaktur = Y.NOFAKTUR
                  WHERE X.STATUS NOT IN ('CANCEL', 'Planned') AND TGL_JTEMPO <= GETDATE() AND X.KodeRekanan = A.KODEREKANAN), 0) JTEMPO,
                 ISNULL((SELECT SUM(OPENAMOUNT) FROM AKUN_FAKTUR WHERE KODEREKANAN = A.KODEREKANAN AND STATUS IN ('Posted', 'Partial')), 0) TOT_PIUTANG
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                 INNER JOIN AKUN_REKANAN C ON A.KODEREKANAN = C.KODEREKANAN
             WHERE B.GRP_A = '1102' AND A.PERIODE = ? AND
                 (NODOKUMEN IN (SELECT NOKASBON FROM AKUN_UANGMUKA WHERE STATUS NOT IN ('Planned', 'CANCEL') AND JENIS = 'Penjualan')
                  OR (NODOKUMEN IN (SELECT NOSLIP FROM AKUN_KASBANK WHERE STATUS = 'Posted' AND JNSTRANS IN ('BANK MASUK'))))
             GROUP BY A.KODEREKANAN, C.NAMA
             ORDER BY JTEMPO DESC",
            [$periode]
        );

        return array_map(fn ($rec) => [
            'KODE' => trim($rec->KODEREKANAN),
            'NAMA' => trim($rec->NAMA),
            'NILAI' => $rec->NILAI,
            'JTEMPO' => $rec->JTEMPO,
            'TOT_PIUTANG' => $rec->TOT_PIUTANG,
        ], $rows);
    }
}

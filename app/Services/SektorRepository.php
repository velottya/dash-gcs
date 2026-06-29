<?php

namespace App\Services;

use App\Support\FormatHelper;
use Illuminate\Support\Facades\DB;

class SektorRepository
{
    public function cashInPerWilayah(): array
    {
        $periode = FormatHelper::periodeTrans();

        return DB::select(
            "SELECT A.KODE_CC, WILAYAH, SUM(AMOUNT) NILAI
             FROM AKUN_JURNAL_DETAIL A INNER JOIN AKUN_ACCOUNT B ON A.ACCOUNT = B.Account
                 LEFT OUTER JOIN AKUN_ACCOUNT_CC C ON C.KODE_CC = A.KODE_CC
             WHERE A.ACCOUNT IN (SELECT ACCOUNT FROM AKUN_ACCOUNT WHERE GRP_A = '1102' AND PERIODE = ?) AND
                 (NODOKUMEN IN (SELECT NOKASBON FROM AKUN_UANGMUKA WHERE STATUS NOT IN ('Planned', 'CANCEL') AND JENIS = 'Penjualan')
                  OR (NODOKUMEN IN (SELECT NOSLIP FROM AKUN_KASBANK WHERE STATUS = 'Posted' AND JNSTRANS IN ('KAS MASUK', 'BANK MASUK'))))
                 AND KODEREKANAN IN (SELECT KODEREKANAN FROM AKUN_REKANAN)
             GROUP BY A.KODE_CC, WILAYAH
             ORDER BY KODE_CC",
            [$periode]
        );
    }

    public function top3CustomerByJatuhTempo(): array
    {
        $periode = FormatHelper::periodeTrans();

        return DB::select(
            "SELECT TOP(3) A.KODEREKANAN, C.NAMA, SUM(AMOUNT) NILAI,
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
            'STATUS' => $rec->NILAI >= $rec->JTEMPO ? 'Cukup' : 'Kurang',
        ], $rows);
    }
}

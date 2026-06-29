<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AgingpiutRepository
{
    public function totalOpenAmount(): float
    {
        return (float) (DB::selectOne('SELECT SUM(OPENAMOUNT) OPENAMOUNT FROM VIEW_AGING_PIUTANG')->OPENAMOUNT ?? 0);
    }

    public function bySektorChart(): array
    {
        return DB::select(
            'SELECT SEKTOR, SUM(BLN_JTEMPO) BLM_JTHTEMPO, SUM(JTH1_30) JTH1_30, SUM(JTH31_60) JTH31_60,
                 SUM(JTH60_90) JTH61_90, SUM(JTH90_365) JTH91_365, SUM(JTH365) JTH365
             FROM VIEW_AGING_PIUTANG
             GROUP BY SEKTOR'
        );
    }

    public function customerSummary(): array
    {
        return DB::select(
            "SELECT RTRIM(KODEREKANAN) KODEREKANAN, NAMA, SUM(OPENAMOUNT) PIUTANG
             FROM VIEW_AGING_PIUTANG
             GROUP BY NAMA, KODEREKANAN
             ORDER BY SUM(OPENAMOUNT) DESC"
        );
    }

    public function agingDetailByRekanan(string $koderekanan): array
    {
        $rows = DB::select(
            "SELECT KODEREKANAN, NOFAKTUR, NOOK, TANGGAL, TGL_JTEMPO, BLN_JTEMPO, JTH1_30, JTH31_60, JTH60_90, JTH90_365, JTH365, NOPEMB,
                 (SELECT WILAYAH FROM AKUN_FAKTUR_DETAIL A INNER JOIN AKUN_ACCOUNT_CC B ON A.KODE_CC = B.KODE_CC
                  WHERE A.NOFAKTUR = VIEW_AGING_PIUTANG.NOFAKTUR AND A.URUT = '01') WILAYAH
             FROM VIEW_AGING_PIUTANG
             WHERE KODEREKANAN = ?
             ORDER BY TGL_JTEMPO",
            [$koderekanan]
        );

        return array_map(fn ($rec) => [
            'KODEREKANAN' => trim($rec->KODEREKANAN),
            'NOPEMB' => trim($rec->NOPEMB),
            'WILAYAH' => trim((string) $rec->WILAYAH),
            'FAKTUR_DETAIL' => trim($rec->NOOK),
            'NOFAKTUR' => trim($rec->NOOK).'/'.trim($rec->NOFAKTUR),
            'TANGGAL' => date('d/m/Y', strtotime($rec->TANGGAL)),
            'TGL_JTEMPO' => date('d/m/Y', strtotime($rec->TGL_JTEMPO)),
            'BLM_JTEMPO' => $rec->BLN_JTEMPO,
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

    private const JATUH_TEMPO_COLUMNS = [
        'bjt' => 'BLN_JTEMPO',
        '1_30' => 'JTH1_30',
        '31_60' => 'JTH31_60',
        '61_90' => 'JTH60_90',
        '91_365' => 'JTH90_365',
        '365' => 'JTH365',
    ];

    public function byJatuhTempoCategory(string $kategori): array
    {
        $column = self::JATUH_TEMPO_COLUMNS[$kategori] ?? null;

        if (! $column) {
            return [];
        }

        $rows = DB::select(
            "SELECT KODEREKANAN, NOPEMB, NOFAKTUR, NOOK, TANGGAL, NAMA, WILAYAH, TGL_JTEMPO, {$column} NILAI
             FROM VIEW_AGING_PIUTANG WHERE {$column} <> 0
             ORDER BY {$column} DESC"
        );

        return $this->mapKategoriRows($rows);
    }

    public function searchByCategory(string $kategori, string $pencarian): array
    {
        if ($kategori === 'wilayah') {
            $rows = DB::select(
                "SELECT NOFAKTUR, NOOK, NOPEMB, WILAYAH, NAMA, TANGGAL, TGL_JTEMPO, BLN_JTEMPO, JTH1_30, JTH31_60, JTH60_90, JTH90_365, JTH365
                 FROM VIEW_AGING_PIUTANG
                 WHERE WILAYAH LIKE ?
                 ORDER BY WILAYAH",
                ['%'.$pencarian.'%']
            );

            return array_map(fn ($rec) => [
                'NOFAKTUR' => trim($rec->NOFAKTUR).'/'.trim($rec->NOOK),
                'FAKTUR_DETAIL' => trim($rec->NOOK),
                'NOPEMB' => trim($rec->NOPEMB),
                'TANGGAL' => date('d/m/Y', strtotime($rec->TANGGAL)),
                'NAMA' => trim($rec->NAMA),
                'TGL_JTEMPO' => date('d/m/Y', strtotime($rec->TGL_JTEMPO)),
                'BLM_JTEMPO' => $rec->BLN_JTEMPO,
                'JTH1_30' => $rec->JTH1_30,
                'JTH31_60' => $rec->JTH31_60,
                'JTH61_90' => $rec->JTH60_90,
                'JTH91_365' => $rec->JTH90_365,
                'JTH365' => $rec->JTH365,
                'WILAYAH' => trim($rec->WILAYAH),
            ], $rows);
        }

        if ($kategori === 'customer') {
            $rows = DB::select(
                "SELECT RTRIM(KODEREKANAN) KODEREKANAN, NAMA, SUM(OPENAMOUNT) PIUTANG
                 FROM VIEW_AGING_PIUTANG
                 WHERE NAMA LIKE ?
                 GROUP BY NAMA, KODEREKANAN
                 ORDER BY SUM(OPENAMOUNT) DESC",
                ['%'.$pencarian.'%']
            );

            return array_map(fn ($rec) => [
                'KODEREKANAN' => trim($rec->KODEREKANAN),
                'NAMA' => trim($rec->NAMA),
                'PIUTANG' => number_format($rec->PIUTANG, 2, ',', '.'),
            ], $rows);
        }

        return [];
    }

    public function bySektor(string $sektor): array
    {
        $rows = DB::select(
            "SELECT NOFAKTUR, NOOK, NOPEMB, WILAYAH, NAMA, TANGGAL, TGL_JTEMPO, BLN_JTEMPO, JTH1_30, JTH31_60, JTH60_90, JTH90_365, JTH365
             FROM VIEW_AGING_PIUTANG
             WHERE SEKTOR = ?
             ORDER BY TGL_JTEMPO",
            [trim($sektor)]
        );

        return $this->mapKategoriRows($rows, true);
    }

    private function mapKategoriRows(array $rows, bool $withJatuhTempo = false): array
    {
        return array_map(function ($rec) use ($withJatuhTempo) {
            $mapped = [
                'NOFAKTUR' => trim($rec->NOFAKTUR).'/'.trim($rec->NOOK),
                'FAKTUR_DETAIL' => trim($rec->NOOK),
                'NOPEMB' => trim($rec->NOPEMB),
                'TANGGAL' => date('d/m/Y', strtotime($rec->TANGGAL)),
                'NAMA' => trim($rec->NAMA),
                'WILAYAH' => trim((string) $rec->WILAYAH),
                'TGL_JTEMPO' => date('d/m/Y', strtotime($rec->TGL_JTEMPO)),
            ];

            if ($withJatuhTempo) {
                $mapped['BLM_JTEMPO'] = $rec->BLN_JTEMPO;
                $mapped['JTH1_30'] = $rec->JTH1_30;
                $mapped['JTH31_60'] = $rec->JTH31_60;
                $mapped['JTH61_90'] = $rec->JTH60_90;
                $mapped['JTH91_365'] = $rec->JTH90_365;
                $mapped['JTH365'] = $rec->JTH365;
            } else {
                $mapped['NILAI'] = $rec->NILAI;
            }

            return $mapped;
        }, $rows);
    }
}

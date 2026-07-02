<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RkapRepository
{
    // ── Agregasi total qty/nilai + jumlah produk, dipakai di semua list ────────
    private const TOTALS_SELECT = "
                ISNULL((SELECT COUNT(DISTINCT STOCKID) FROM DASH.RKAP_DETAIL D WHERE D.RKAP_ID = R.ID), 0) AS JUMLAH_PRODUK,
                ISNULL((SELECT SUM(QTY_TON)             FROM DASH.RKAP_DETAIL D WHERE D.RKAP_ID = R.ID), 0) AS TOTAL_QTY,
                ISNULL((SELECT SUM(NILAI_RUPIAH)        FROM DASH.RKAP_DETAIL D WHERE D.RKAP_ID = R.ID), 0) AS TOTAL_NILAI";

    // ── Manager: CRUD pengajuan ───────────────────────────────────────────────

    public function listByManager(string $nik, ?int $tahun = null): array
    {
        $tahun ??= now()->year;
        return DB::select(
            'SELECT R.*, P.nama AS nama_manager,'.self::TOTALS_SELECT.'
             FROM DASH.RKAP R
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             WHERE R.NIK_MANAGER = ? AND R.TAHUN = ?
             ORDER BY R.DATE_CREATE DESC',
            [$nik, $tahun]
        );
    }

    public function find(int $id): ?object
    {
        return DB::selectOne(
            "SELECT R.*,
                    PM.nama AS nama_manager,
                    PG.nama AS nama_gm,
                    PD.nama AS nama_direksi
             FROM DASH.RKAP R
             LEFT JOIN dbo.PEGAWAI_SDM PM ON PM.Nik = R.NIK_MANAGER
             LEFT JOIN dbo.PEGAWAI_SDM PG ON PG.Nik = R.NIK_GM_VALIDATOR
             LEFT JOIN dbo.PEGAWAI_SDM PD ON PD.Nik = R.NIK_DIREKSI
             WHERE R.ID = ?",
            [$id]
        );
    }

    public function findByManagerAndTahun(string $nik, int $tahun): ?object
    {
        return DB::selectOne(
            'SELECT * FROM DASH.RKAP WHERE NIK_MANAGER = ? AND TAHUN = ?',
            [$nik, $tahun]
        );
    }

    /**
     * Produk (dan detail bulanannya) milik satu RKAP, dikelompokkan per STOCKID.
     *
     * @return array<int, array{stockid: string, produk: string, months: array<int, object>}>
     */
    public function productsGrouped(int $rkapId): array
    {
        $rows = DB::select(
            'SELECT * FROM DASH.RKAP_DETAIL WHERE RKAP_ID = ? ORDER BY STOCKID, BULAN',
            [$rkapId]
        );

        $products = [];
        foreach ($rows as $r) {
            $sid = $r->STOCKID;
            if (! isset($products[$sid])) {
                $products[$sid] = ['stockid' => $r->STOCKID, 'produk' => $r->PRODUK, 'months' => []];
            }
            $products[$sid]['months'][(int) $r->BULAN] = $r;
        }

        return array_values($products);
    }

    public function createHeader(string $nikManager, int $tahun): int
    {
        // SCOPE_IDENTITY() queried via a separate DB::selectOne() call comes
        // back NULL — Laravel's sqlsrv driver runs each call as its own batch,
        // so the "scope" is already gone by the time the follow-up SELECT
        // runs. OUTPUT INSERTED.ID reads the identity back in the same
        // statement, sidestepping the issue entirely.
        return (int) DB::selectOne(
            "INSERT INTO DASH.RKAP (TAHUN, NIK_MANAGER, STATUS, DATE_CREATE, DATE_UPDATE)
             OUTPUT INSERTED.ID
             VALUES (?, ?, 'draft', GETDATE(), GETDATE())",
            [$tahun, $nikManager]
        )->ID;
    }

    public function markDraft(int $id): void
    {
        DB::update(
            "UPDATE DASH.RKAP SET STATUS = 'draft', DATE_UPDATE = GETDATE() WHERE ID = ?",
            [$id]
        );
    }

    /**
     * Simpan seluruh produk (dan 12 bulan tiap produk) milik satu RKAP.
     * Produk yang tidak lagi ada di $products akan dihapus detailnya.
     *
     * @param  array<int, array{stockid: string, produk: string, months: array<int, array{qty: float, nilai: float}>}>  $products
     */
    public function saveProducts(int $rkapId, array $products): void
    {
        $stockids = array_map(fn ($p) => $p['stockid'], $products);

        if ($stockids === []) {
            DB::delete('DELETE FROM DASH.RKAP_DETAIL WHERE RKAP_ID = ?', [$rkapId]);
        } else {
            $placeholders = implode(',', array_fill(0, count($stockids), '?'));
            DB::delete(
                "DELETE FROM DASH.RKAP_DETAIL WHERE RKAP_ID = ? AND STOCKID NOT IN ($placeholders)",
                [$rkapId, ...$stockids]
            );
        }

        foreach ($products as $p) {
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $qty   = (float) ($p['months'][$bulan]['qty']   ?? 0);
                $nilai = (float) ($p['months'][$bulan]['nilai'] ?? 0);

                $exists = DB::selectOne(
                    'SELECT ID FROM DASH.RKAP_DETAIL WHERE RKAP_ID = ? AND STOCKID = ? AND BULAN = ?',
                    [$rkapId, $p['stockid'], $bulan]
                );

                if ($exists) {
                    DB::update(
                        'UPDATE DASH.RKAP_DETAIL SET PRODUK = ?, QTY_TON = ?, NILAI_RUPIAH = ? WHERE ID = ?',
                        [$p['produk'], $qty, $nilai, $exists->ID]
                    );
                } else {
                    DB::insert(
                        'INSERT INTO DASH.RKAP_DETAIL (RKAP_ID, STOCKID, PRODUK, BULAN, QTY_TON, NILAI_RUPIAH) VALUES (?, ?, ?, ?, ?, ?)',
                        [$rkapId, $p['stockid'], $p['produk'], $bulan, $qty, $nilai]
                    );
                }
            }
        }
    }

    public function submit(int $id): void
    {
        DB::update(
            "UPDATE DASH.RKAP SET STATUS = 'submitted', TGL_SUBMIT = GETDATE(), DATE_UPDATE = GETDATE() WHERE ID = ?",
            [$id]
        );
    }

    public function delete(int $id): void
    {
        DB::delete('DELETE FROM DASH.RKAP WHERE ID = ?', [$id]);
    }

    // ── Produk (dbo.INVENTORY): pencarian untuk combobox ─────────────────────

    public function searchProduk(string $keyword): array
    {
        return DB::select(
            "SELECT DISTINCT TOP 50 RTRIM(STOCKID) AS STOCKID, RTRIM(NAMA_BARANG) AS NAMA_BARANG
             FROM dbo.INVENTORY
             WHERE (STOCKID LIKE ? OR NAMA_BARANG LIKE ?)
               AND NAMA_BARANG IS NOT NULL AND NAMA_BARANG != ''
             ORDER BY NAMA_BARANG",
            ["%{$keyword}%", "%{$keyword}%"]
        );
    }

    // ── GM: list untuk validasi ───────────────────────────────────────────────

    public function listForGm(string $nikGm, ?int $tahun = null): array
    {
        $tahun ??= now()->year;
        return DB::select(
            'SELECT R.*, P.nama AS nama_manager,'.self::TOTALS_SELECT.'
             FROM DASH.RKAP R
             JOIN DASH.GM_MANAGER GM ON GM.NIK_MANAGER = R.NIK_MANAGER AND GM.NIK_GM = ?
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             WHERE R.TAHUN = ? AND R.STATUS IN (\'submitted\',\'gm_rejected\',\'gm_approved\',\'approved\',\'rejected\')
             ORDER BY R.TGL_SUBMIT DESC',
            [$nikGm, $tahun]
        );
    }

    public function gmValidate(int $id, string $nikGm, string $action, string $catatan): void
    {
        $status = $action === 'approve' ? 'gm_approved' : 'gm_rejected';
        DB::update(
            "UPDATE DASH.RKAP
             SET STATUS = ?, CATATAN_GM = ?, NIK_GM_VALIDATOR = ?, TGL_VALIDASI_GM = GETDATE(), DATE_UPDATE = GETDATE()
             WHERE ID = ?",
            [$status, $catatan, $nikGm, $id]
        );
    }

    // ── Direksi: list untuk pengesahan ────────────────────────────────────────

    public function listForDireksi(?int $tahun = null): array
    {
        $tahun ??= now()->year;
        return DB::select(
            'SELECT R.*, P.nama AS nama_manager, PG.nama AS nama_gm,'.self::TOTALS_SELECT.'
             FROM DASH.RKAP R
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             LEFT JOIN dbo.PEGAWAI_SDM PG ON PG.Nik = R.NIK_GM_VALIDATOR
             WHERE R.TAHUN = ? AND R.STATUS IN (\'gm_approved\',\'approved\',\'rejected\')
             ORDER BY R.TGL_VALIDASI_GM DESC',
            [$tahun]
        );
    }

    public function direksiValidate(int $id, string $nikDireksi, string $action, string $catatan): void
    {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        DB::update(
            "UPDATE DASH.RKAP
             SET STATUS = ?, CATATAN_DIREKSI = ?, NIK_DIREKSI = ?, TGL_VALIDASI_DIREKSI = GETDATE(), DATE_UPDATE = GETDATE()
             WHERE ID = ?",
            [$status, $catatan, $nikDireksi, $id]
        );
    }

    // ── Superadmin: semua data ────────────────────────────────────────────────

    public function listAll(?int $tahun = null): array
    {
        $tahun ??= now()->year;
        return DB::select(
            'SELECT R.*, P.nama AS nama_manager, PG.nama AS nama_gm,'.self::TOTALS_SELECT.'
             FROM DASH.RKAP R
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             LEFT JOIN dbo.PEGAWAI_SDM PG ON PG.Nik = R.NIK_GM_VALIDATOR
             WHERE R.TAHUN = ?
             ORDER BY R.STATUS, R.DATE_CREATE DESC',
            [$tahun]
        );
    }

    // ── Export data ───────────────────────────────────────────────────────────

    public function exportData(int $id): array
    {
        $rkap     = $this->find($id);
        $products = $this->productsGrouped($id);
        return compact('rkap', 'products');
    }
}

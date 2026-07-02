<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RkapRepository
{
    // ── Manager: CRUD pengajuan ───────────────────────────────────────────────

    public function listByManager(string $nik, ?int $tahun = null): array
    {
        $tahun ??= now()->year;
        return DB::select(
            "SELECT R.*, P.nama AS nama_manager
             FROM DASH.RKAP R
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             WHERE R.NIK_MANAGER = ? AND R.TAHUN = ?
             ORDER BY R.DATE_CREATE DESC",
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

    public function details(int $rkapId): array
    {
        return DB::select(
            'SELECT * FROM DASH.RKAP_DETAIL WHERE RKAP_ID = ? ORDER BY BULAN',
            [$rkapId]
        );
    }

    public function detailsByMonth(int $rkapId): array
    {
        $rows = $this->details($rkapId);
        $map  = [];
        foreach ($rows as $r) {
            $map[(int) $r->BULAN] = $r;
        }
        return $map;
    }

    public function create(string $nikManager, int $tahun, string $produk, array $months): int
    {
        DB::insert(
            "INSERT INTO DASH.RKAP (TAHUN, NIK_MANAGER, PRODUK, STATUS, DATE_CREATE, DATE_UPDATE)
             VALUES (?, ?, ?, 'draft', GETDATE(), GETDATE())",
            [$tahun, $nikManager, $produk]
        );

        $id = (int) DB::selectOne('SELECT SCOPE_IDENTITY() AS id')->id;
        $this->upsertDetails($id, $months);
        return $id;
    }

    public function update(int $id, string $produk, array $months): void
    {
        DB::update(
            "UPDATE DASH.RKAP SET PRODUK = ?, STATUS = 'draft', DATE_UPDATE = GETDATE() WHERE ID = ?",
            [$produk, $id]
        );
        $this->upsertDetails($id, $months);
    }

    private function upsertDetails(int $rkapId, array $months): void
    {
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $qty   = (float) ($months[$bulan]['qty']   ?? 0);
            $nilai = (float) ($months[$bulan]['nilai'] ?? 0);

            $exists = DB::selectOne(
                'SELECT ID FROM DASH.RKAP_DETAIL WHERE RKAP_ID = ? AND BULAN = ?',
                [$rkapId, $bulan]
            );

            if ($exists) {
                DB::update(
                    'UPDATE DASH.RKAP_DETAIL SET QTY_TON = ?, NILAI_RUPIAH = ? WHERE RKAP_ID = ? AND BULAN = ?',
                    [$qty, $nilai, $rkapId, $bulan]
                );
            } else {
                DB::insert(
                    'INSERT INTO DASH.RKAP_DETAIL (RKAP_ID, BULAN, QTY_TON, NILAI_RUPIAH) VALUES (?, ?, ?, ?)',
                    [$rkapId, $bulan, $qty, $nilai]
                );
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

    // ── GM: list untuk validasi ───────────────────────────────────────────────

    public function listForGm(string $nikGm, ?int $tahun = null): array
    {
        $tahun ??= now()->year;
        return DB::select(
            "SELECT R.*, P.nama AS nama_manager
             FROM DASH.RKAP R
             JOIN DASH.GM_MANAGER GM ON GM.NIK_MANAGER = R.NIK_MANAGER AND GM.NIK_GM = ?
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             WHERE R.TAHUN = ? AND R.STATUS IN ('submitted','gm_rejected','gm_approved','approved','rejected')
             ORDER BY R.TGL_SUBMIT DESC",
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
            "SELECT R.*, P.nama AS nama_manager, PG.nama AS nama_gm
             FROM DASH.RKAP R
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             LEFT JOIN dbo.PEGAWAI_SDM PG ON PG.Nik = R.NIK_GM_VALIDATOR
             WHERE R.TAHUN = ? AND R.STATUS IN ('gm_approved','approved','rejected')
             ORDER BY R.TGL_VALIDASI_GM DESC",
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
            "SELECT R.*, P.nama AS nama_manager, PG.nama AS nama_gm
             FROM DASH.RKAP R
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = R.NIK_MANAGER
             LEFT JOIN dbo.PEGAWAI_SDM PG ON PG.Nik = R.NIK_GM_VALIDATOR
             WHERE R.TAHUN = ?
             ORDER BY R.STATUS, R.DATE_CREATE DESC",
            [$tahun]
        );
    }

    // ── Export data ───────────────────────────────────────────────────────────

    public function exportData(int $id): array
    {
        $rkap    = $this->find($id);
        $details = $this->detailsByMonth($id);
        return compact('rkap', 'details');
    }
}

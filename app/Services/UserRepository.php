<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    // ── PEGAWAI_SDM ──────────────────────────────────────────────────────────

    public function searchPegawai(string $keyword): array
    {
        // Hanya tampilkan pegawai yang BELUM punya akun di MST_USER
        return DB::select(
            "SELECT TOP 50 P.Nik, P.nama, P.jabatan, P.BAGIAN
             FROM dbo.PEGAWAI_SDM P
             WHERE (P.Nik LIKE ? OR P.nama LIKE ?)
               AND P.data_aktif = 'Aktif'
               AND NOT EXISTS (SELECT 1 FROM DASH.MST_USER U WHERE U.NIK = P.Nik)
             ORDER BY P.nama",
            ["%{$keyword}%", "%{$keyword}%"]
        );
    }

    public function findPegawai(string $nik): ?object
    {
        return DB::selectOne(
            "SELECT Nik, nama, jabatan, BAGIAN, DIVISI, UNIT_KERJA, WILAYAH, GOL,
                    TEMPAT_LAHIR, TGL_LAHIR, STATUS_NIKAH, JUMLAH_ANAK, NM_PENDIDIKAN,
                    jenis_kelamin, jenis_pegawai, data_aktif
             FROM dbo.PEGAWAI_SDM WHERE Nik = ?",
            [$nik]
        );
    }

    // ── MST_USER: list & search (untuk dropdown "akun yang ada") ─────────────

    public function searchMstUser(string $keyword): array
    {
        return DB::select(
            "SELECT TOP 50 U.NIK, U.USERNAME, U.ID_LEVEL, U.STATUS_AKUN,
                    P.nama, P.jabatan, P.BAGIAN
             FROM DASH.MST_USER U
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = U.NIK
             WHERE (U.NIK LIKE ? OR U.USERNAME LIKE ? OR P.nama LIKE ?)
             ORDER BY P.nama",
            ["%{$keyword}%", "%{$keyword}%", "%{$keyword}%"]
        );
    }

    // ── MST_USER: daftar untuk manajemen ────────────────────────────────────

    public function listUsers(): array
    {
        return DB::select(
            "SELECT U.NIK, U.USERNAME, U.ID_LEVEL, U.STATUS_AKUN AS data_aktif, U.DATE_CREATE, U.LAST_LOGIN,
                    P.nama, P.jabatan, P.BAGIAN, P.DIVISI
             FROM DASH.MST_USER U
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = U.NIK
             WHERE U.ID_LEVEL IN (2,3,4)
             ORDER BY U.ID_LEVEL, P.nama"
        );
    }

    public function listManagersOfGm(string $nikGm): array
    {
        return DB::select(
            "SELECT U.NIK, U.USERNAME, U.ID_LEVEL, U.STATUS_AKUN AS data_aktif, U.DATE_CREATE, U.LAST_LOGIN,
                    P.nama, P.jabatan, P.BAGIAN, P.DIVISI
             FROM DASH.GM_MANAGER GM
             JOIN DASH.MST_USER U ON U.NIK = GM.NIK_MANAGER
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = GM.NIK_MANAGER
             WHERE GM.NIK_GM = ?
             ORDER BY P.nama",
            [$nikGm]
        );
    }

    public function findUser(string $nik): ?object
    {
        return DB::selectOne(
            "SELECT U.NIK, U.USERNAME, U.ID_LEVEL, U.STATUS_AKUN AS data_aktif,
                    U.DATE_CREATE, U.LAST_LOGIN, U.IMG,
                    P.nama, P.jabatan, P.BAGIAN, P.DIVISI, P.UNIT_KERJA, P.WILAYAH,
                    P.GOL, P.TEMPAT_LAHIR, P.TGL_LAHIR, P.STATUS_NIKAH, P.JUMLAH_ANAK,
                    P.NM_PENDIDIKAN, P.jenis_kelamin, P.jenis_pegawai, P.data_aktif AS sdm_aktif,
                    P.kelompok_divisi, P.KODE_WILAYAH,
                    E.PHONE, E.EMAIL
             FROM DASH.MST_USER U
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = U.NIK
             LEFT JOIN DASH.USER_EXTRA E ON E.NIK = U.NIK
             WHERE U.NIK = ?",
            [$nik]
        );
    }

    public function existsByNik(string $nik): bool
    {
        return DB::selectOne('SELECT 1 AS found FROM DASH.MST_USER WHERE NIK = ?', [$nik]) !== null;
    }

    public function existsByUsername(string $username, ?string $excludeNik = null): bool
    {
        $sql      = 'SELECT 1 AS found FROM DASH.MST_USER WHERE USERNAME = ?';
        $bindings = [$username];
        if ($excludeNik !== null) {
            $sql      .= ' AND NIK != ?';
            $bindings[] = $excludeNik;
        }
        return DB::selectOne($sql, $bindings) !== null;
    }

    public function createUser(string $nik, string $username, string $password, int $level): void
    {
        DB::insert(
            "INSERT INTO DASH.MST_USER (NIK, USERNAME, PASSWORD, ID_LEVEL, STATUS_AKUN, DATE_CREATE)
             VALUES (?, ?, ?, ?, 'Aktif', GETDATE())",
            [$nik, $username, Hash::make($password), $level]
        );
    }

    public function updateUser(string $nik, string $username, int $level, string $status): void
    {
        DB::update(
            'UPDATE DASH.MST_USER SET USERNAME = ?, ID_LEVEL = ?, STATUS_AKUN = ? WHERE NIK = ?',
            [$username, $level, $status, $nik]
        );
    }

    public function resetPassword(string $nik, string $newPassword): void
    {
        DB::update(
            'UPDATE DASH.MST_USER SET PASSWORD = ? WHERE NIK = ?',
            [Hash::make($newPassword), $nik]
        );
    }

    public function deleteUser(string $nik): void
    {
        DB::delete('DELETE FROM DASH.MST_USER WHERE NIK = ?', [$nik]);
        DB::delete('DELETE FROM DASH.GM_MANAGER WHERE NIK_GM = ? OR NIK_MANAGER = ?', [$nik, $nik]);
    }

    // ── GM–Manager mapping ───────────────────────────────────────────────────

    public function getGmOfManager(string $nikManager): ?object
    {
        return DB::selectOne(
            "SELECT GM.NIK_GM, P.nama FROM DASH.GM_MANAGER GM
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = GM.NIK_GM
             WHERE GM.NIK_MANAGER = ?",
            [$nikManager]
        );
    }

    public function getManagersOfGm(string $nikGm): array
    {
        return DB::select(
            "SELECT GM.NIK_MANAGER, U.USERNAME, U.STATUS_AKUN AS data_aktif, P.nama, P.jabatan
             FROM DASH.GM_MANAGER GM
             JOIN DASH.MST_USER U ON U.NIK = GM.NIK_MANAGER
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = GM.NIK_MANAGER
             WHERE GM.NIK_GM = ?
             ORDER BY P.nama",
            [$nikGm]
        );
    }

    public function listGms(): array
    {
        return DB::select(
            "SELECT U.NIK, P.nama FROM DASH.MST_USER U
             LEFT JOIN dbo.PEGAWAI_SDM P ON P.Nik = U.NIK
             WHERE U.ID_LEVEL = 2 AND U.STATUS_AKUN = 'Aktif'
             ORDER BY P.nama"
        );
    }

    public function setGmForManager(string $nikGm, string $nikManager): void
    {
        DB::delete('DELETE FROM DASH.GM_MANAGER WHERE NIK_MANAGER = ?', [$nikManager]);
        if ($nikGm !== '') {
            DB::insert(
                'INSERT INTO DASH.GM_MANAGER (NIK_GM, NIK_MANAGER) VALUES (?, ?)',
                [$nikGm, $nikManager]
            );
        }
    }
}

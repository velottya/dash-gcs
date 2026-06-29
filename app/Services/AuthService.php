<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuthService
{
    public function findByUsername(string $username): ?object
    {
        $rows = DB::select(
            "SELECT PersonId, nama, data_aktif, jabatan, B.NIK, B.DATE_CREATE, B.ID_LEVEL, B.USERNAME, B.PASSWORD, B.IMG
             FROM PEGAWAI_SDM A INNER JOIN DASH.MST_USER B ON A.Nik = B.NIK
             WHERE B.USERNAME = ?",
            [$username]
        );

        return $rows[0] ?? null;
    }

    public function recordLogin(string $nik, string $username): void
    {
        DB::update('UPDATE DASH.MST_USER SET LAST_LOGIN = GETDATE() WHERE NIK = ?', [$nik]);

        DB::insert(
            'INSERT INTO DASH.LOG_LOGIN (LOGIN_DATE, USERNAME, DEVICE_INFO) VALUES (GETDATE(), ?, ?)',
            [$username, request()->userAgent() ?? '']
        );
    }

    public function recordLogout(string $username): void
    {
        DB::update('UPDATE DASH.MST_USER SET LAST_LOGIN = GETDATE() WHERE USERNAME = ?', [$username]);
    }
}

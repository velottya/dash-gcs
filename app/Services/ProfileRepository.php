<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProfileRepository
{
    public function pegawai(string $nik): ?object
    {
        return DB::table('SDM_MST_PEGAWAI')->where('Nik', $nik)->first();
    }
}

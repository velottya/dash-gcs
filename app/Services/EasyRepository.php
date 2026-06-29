<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EasyRepository
{
    /**
     * Run the legacy DATADASHBOARD stored procedure for a given date.
     */
    public function dailyDashboard(string $tanggalDmy): array
    {
        return DB::select('EXEC DATADASHBOARD ?', [$tanggalDmy]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\DemografiRepository;
use Illuminate\View\View;

class DemografiController extends Controller
{
    public function __construct(private DemografiRepository $repository) {}

    public function index(): View
    {
        $total = $this->repository->totalKaryawan();
        $statusKaryawan = $this->repository->statusKaryawan();
        $wilayah = $this->repository->byWilayah();
        $kelamin = $this->repository->byKelamin();
        $pendidikan = $this->repository->byPendidikan();
        $jabatan = $this->repository->byJabatan();
        $usia = $this->repository->byUsia();

        return view('demografi.index', [
            'total' => $total,
            'totTetap' => $this->repository->totalTetap(),
            'totKontrak' => $this->repository->totalKontrak(),
            'statusKaryawan' => $statusKaryawan,
            'wilayah' => $wilayah,
            'kelamin' => $kelamin,
            'pendidikan' => $pendidikan,
            'jabatan' => $jabatan,
            'usia' => $usia,
            'insightStatus' => $this->dominantInsight($statusKaryawan, 'JENIS_PEGAWAI', ['TOTAL'], $total, 'Status'),
            'insightUsia' => $this->dominantInsight($usia, 'KATEGORI_USIA', ['TETAP', 'KONTRAK'], $total, 'Kelompok usia'),
            'insightKelamin' => $this->dominantInsight($kelamin, 'JENIS_KELAMIN', ['TETAP', 'KONTRAK'], $total, 'Kelamin'),
            'insightWilayah' => $this->dominantInsight($wilayah, 'WILAYAH', ['TETAP', 'KONTRAK'], $total, 'Wilayah'),
            'insightPendidikan' => $this->dominantInsight($pendidikan, 'NM_PENDIDIKAN', ['TETAP'], $total, 'Pendidikan'),
            'insightJabatan' => $this->dominantInsight($jabatan, 'NM_JABATAN', ['TETAP'], $total, 'Jabatan'),
        ]);
    }

    /**
     * Finds the row with the highest combined value across $valueKeys and
     * phrases it as a one-line "X terbanyak: ..." insight.
     *
     * @param  array<int, object>  $rows
     * @param  array<int, string>  $valueKeys
     * @return array<int, string>
     */
    private function dominantInsight(array $rows, string $labelKey, array $valueKeys, int $total, string $subject): array
    {
        if ($rows === [] || $total <= 0) {
            return [];
        }

        $values = array_map(
            fn ($row) => array_sum(array_map(fn ($key) => (float) ($row->$key ?? 0), $valueKeys)),
            $rows
        );
        $maxIdx = array_keys($values, max($values))[0];
        $pct = round(($values[$maxIdx] / $total) * 100, 1);

        return [$subject.' terbanyak: '.trim((string) $rows[$maxIdx]->$labelKey).' ('.$pct.'% dari total).'];
    }
}

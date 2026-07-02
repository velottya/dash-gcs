<?php

namespace App\Http\Controllers;

use App\Services\PenjualanRepository;
use App\Support\FormatHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function __construct(private PenjualanRepository $repository) {}

    public function index(Request $request): View
    {
        $tahunSekarang = (int) now()->year;
        $bulan = (int) now()->month;

        $tahunOptions = range($tahunSekarang, $tahunSekarang - 6);
        $tahun = (int) $request->query('tahun', $tahunSekarang);
        if (! in_array($tahun, $tahunOptions, true)) {
            $tahun = $tahunSekarang;
        }

        $chart1 = $this->repository->monthlyAmountVsHpp($tahun);
        $chart1Sbl = $this->repository->monthlyAmountVsHpp($tahun - 1);
        $rkap = $this->repository->rkapYtd($tahun);
        $chart2 = $this->repository->sektorChart($tahunSekarang, $bulan);
        $chart3 = $this->repository->wilayahChart($tahunSekarang, $bulan);

        return view('penjualan.index', [
            'chart1' => $chart1,
            'chart1Sbl' => $chart1Sbl,
            'rkap' => $rkap,
            'chart2' => $chart2,
            'chart3' => $chart3,
            'maxBulan' => $bulan,
            'tahun' => $tahun,
            'tahunOptions' => $tahunOptions,
            'insightChart1' => $this->insightChart1($chart1, $rkap),
            'insightChart2' => $this->insightTopShare($chart2, 'sektor'),
            'insightChart3' => $this->insightTopShare($chart3, 'wilayah'),
        ]);
    }

    /**
     * @param  array<int, object>  $chart1
     * @param  array<int, object>  $rkap
     * @return array<int, string>
     */
    private function insightChart1(array $chart1, array $rkap): array
    {
        $sumAmount = array_sum(array_map(fn ($r) => (float) $r->AMOUNT, $chart1));
        $sumRkap = array_sum(array_map(fn ($r) => (float) $r->NILAI, $rkap));
        $capaian = FormatHelper::targetPersen($sumRkap, $sumAmount);

        return [
            'Penjualan YTD Rp '.FormatHelper::maskRp($sumAmount).' dari target RKAP Rp '.FormatHelper::maskRp($sumRkap).'.',
            'Capaian '.$capaian.'% ('.FormatHelper::achievementLabel($capaian).').',
        ];
    }

    /**
     * @param  array<int, object>  $rows  objects with SUB_GROUP and AMOUNT
     * @return array<int, string>
     */
    private function insightTopShare(array $rows, string $label): array
    {
        if ($rows === []) {
            return [];
        }

        $total = array_sum(array_map(fn ($r) => (float) $r->AMOUNT, $rows));
        $top = collect($rows)->sortByDesc(fn ($r) => (float) $r->AMOUNT)->first();
        $share = $total != 0 ? round(((float) $top->AMOUNT / $total) * 100, 1) : 0;

        return [
            ucfirst($label).' tertinggi: '.trim((string) $top->SUB_GROUP).' (Rp '.FormatHelper::maskRp($top->AMOUNT).', '.$share.'% dari total bulan ini).',
        ];
    }

    public function chart2(Request $request): JsonResponse
    {
        return response()->json($this->repository->sektorChartByBulan((int) $request->input('option')));
    }

    public function chart3(Request $request): JsonResponse
    {
        return response()->json($this->repository->wilayahChartByBulan((int) $request->input('option')));
    }

    public function chartDetail(Request $request): JsonResponse
    {
        $tahun = (int) $request->input('tahun', now()->year);

        return response()->json($this->repository->chartDetail((string) $request->input('option'), $tahun));
    }

    public function breakdown(Request $request): JsonResponse
    {
        $tahun = (int) $request->input('tahun', now()->year);

        return response()->json([
            'sektor' => $this->repository->sektorChartTahunan($tahun),
            'wilayah' => $this->repository->wilayahChartTahunan($tahun),
        ]);
    }

    public function detail1(Request $request): JsonResponse
    {
        return response()->json($this->repository->detailSektorBarang((int) $request->input('option')));
    }

    public function detail2(Request $request): JsonResponse
    {
        return response()->json($this->repository->detailWilayahCc((int) $request->input('option')));
    }
}

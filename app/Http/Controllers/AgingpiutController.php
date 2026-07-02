<?php

namespace App\Http\Controllers;

use App\Services\AgingpiutRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgingpiutController extends Controller
{
    public function __construct(private AgingpiutRepository $repository) {}

    public function index(): View
    {
        $totalOpenAmount = $this->repository->totalOpenAmount();
        $sektorChart = $this->repository->bySektorChart();

        return view('agingpiut.index', [
            'totalOpenAmount' => $totalOpenAmount,
            'sektorChart' => $sektorChart,
            'customers' => $this->repository->customerSummary(),
            'insightSektor' => $this->insightSektor($totalOpenAmount, $sektorChart),
        ]);
    }

    /**
     * @param  array<int, object>  $sektorChart
     * @return array<int, string>
     */
    private function insightSektor(float $totalOpenAmount, array $sektorChart): array
    {
        if ($sektorChart === [] || $totalOpenAmount <= 0) {
            return [];
        }

        $overdue90 = array_sum(array_map(fn ($r) => (float) $r->JTH91_365 + (float) $r->JTH365, $sektorChart));
        $pctOverdue90 = round(($overdue90 / $totalOpenAmount) * 100, 1);

        $totalBySektor = array_map(fn ($r) => (float) $r->BLM_JTHTEMPO + (float) $r->JTH1_30 + (float) $r->JTH31_60 + (float) $r->JTH61_90 + (float) $r->JTH91_365 + (float) $r->JTH365, $sektorChart);
        $maxIdx = array_keys($totalBySektor, max($totalBySektor))[0];
        $topSektor = trim((string) $sektorChart[$maxIdx]->SEKTOR);
        $topShare = round(($totalBySektor[$maxIdx] / $totalOpenAmount) * 100, 1);

        return [
            $pctOverdue90.'% dari piutang aktif sudah jatuh tempo lebih dari 90 hari.',
            'Sektor '.$topSektor.' menyumbang konsentrasi piutang terbesar ('.$topShare.'% dari total).',
        ];
    }

    public function detailAging(Request $request): JsonResponse
    {
        return response()->json($this->repository->agingDetailByRekanan((string) $request->input('korek')));
    }

    public function detail2(Request $request): JsonResponse
    {
        return response()->json($this->repository->fakturDetail((string) $request->input('nofaktur')));
    }

    public function detail3(Request $request): JsonResponse
    {
        return response()->json($this->repository->byJatuhTempoCategory((string) $request->input('xval')));
    }

    public function detail4(Request $request): JsonResponse
    {
        return response()->json($this->repository->searchByCategory(
            (string) $request->input('kategori'),
            (string) $request->input('pencarian')
        ));
    }

    public function detail5(Request $request): JsonResponse
    {
        return response()->json($this->repository->bySektor((string) $request->input('sektor')));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\LabarRepository;
use App\Support\FormatHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabarController extends Controller
{
    public function __construct(private LabarRepository $repository) {}

    public function index(Request $request): View
    {
        $periode = now()->format('Ym');
        $periodeSbl = (now()->year - 1).now()->format('m');

        $historisOptions = [7, 8, 9, 10];
        $historisTahun = (int) $request->query('historis', 10);
        if (! in_array($historisTahun, $historisOptions, true)) {
            $historisTahun = 10;
        }

        $datax = $this->repository->historisPenjualanLaba($historisTahun);

        return view('labar.index', [
            'data' => $this->repository->laporanLabaRugi($periode, $periodeSbl),
            'datax' => $datax,
            'historisTahun' => $historisTahun,
            'historisOptions' => $historisOptions,
            'insightHistoris' => $this->insightHistoris($datax),
        ]);
    }

    /**
     * @param  array<int, object>  $datax  rows with TAHUN, PENJUALAN, LABA
     * @return array<int, string>
     */
    private function insightHistoris(array $datax): array
    {
        if (count($datax) < 2) {
            return [];
        }

        $first = $datax[0];
        $last = $datax[count($datax) - 1];
        $npm = fn ($row) => $row->PENJUALAN != 0 ? round(($row->LABA / $row->PENJUALAN) * 100, 1) : 0;
        $npmFirst = $npm($first);
        $npmLast = $npm($last);

        return [
            'Penjualan '.$last->TAHUN.' Rp '.FormatHelper::maskRp($last->PENJUALAN).' ('.FormatHelper::trendLabel($last->PENJUALAN, $first->PENJUALAN).' dibanding '.$first->TAHUN.').',
            'Net Profit Margin '.$last->TAHUN.' '.$npmLast.'% ('.FormatHelper::trendLabel($npmLast, $npmFirst).' dibanding '.$first->TAHUN.').',
        ];
    }

    public function detail1(Request $request): JsonResponse
    {
        return response()->json($this->repository->laporanLabaRugiDetail($request->input('periodeLabar')));
    }

    public function detail2(Request $request): JsonResponse
    {
        return response()->json($this->repository->ratioAnalysis($request->input('periodeLabar')));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\PenjualanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function __construct(private PenjualanRepository $repository) {}

    public function index(): View
    {
        $tahun = (int) now()->year;
        $bulan = (int) now()->month;

        return view('penjualan.index', [
            'chart1' => $this->repository->monthlyAmountVsHpp($tahun),
            'rkap' => $this->repository->rkapYtd($tahun),
            'chart2' => $this->repository->sektorChart($tahun, $bulan),
            'chart3' => $this->repository->wilayahChart($tahun, $bulan),
            'maxBulan' => $bulan,
        ]);
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
        return response()->json($this->repository->chartDetail((string) $request->input('option'), (int) now()->year));
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

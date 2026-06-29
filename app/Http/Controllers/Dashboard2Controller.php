<?php

namespace App\Http\Controllers;

use App\Services\Dashboard2Repository;
use App\Services\DashboardRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Dashboard2Controller extends Controller
{
    public function __construct(
        private Dashboard2Repository $repository,
        private DashboardRepository $dashboardRepository,
    ) {}

    public function index(): View
    {
        return view('dashboard2.index', [
            'kpi' => $this->repository->kpiTahunan(),
            'penjualanTahunan' => $this->repository->seriesPenjualanTahunan(),
            'labaTahunan' => $this->repository->seriesLabaTahunan(),
        ]);
    }

    public function sektorDetail(Request $request): JsonResponse
    {
        $idSektor = (string) $request->input('idSektor1');

        return response()->json($this->dashboardRepository->sektorDetail($idSektor));
    }

    public function penjualanHariIni(): JsonResponse
    {
        return response()->json($this->dashboardRepository->penjualanHariIni());
    }

    public function agingDetail(Request $request): JsonResponse
    {
        $korek = (string) $request->input('korek');

        return response()->json($this->dashboardRepository->agingDetailByRekanan($korek));
    }

    public function fakturDetail(Request $request): JsonResponse
    {
        $nofaktur = (string) $request->input('nofaktur');

        return response()->json($this->dashboardRepository->fakturDetail($nofaktur));
    }

    public function chartDetail(Request $request): JsonResponse
    {
        $opt = (string) $request->input('option');

        return response()->json($this->repository->chartTahunanDetail($opt));
    }
}

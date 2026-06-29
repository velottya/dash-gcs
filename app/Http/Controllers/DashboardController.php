<?php

namespace App\Http\Controllers;

use App\Services\DashboardRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardRepository $repository) {}

    public function index(Request $request): View
    {
        $username = $request->session()->get('username');
        $nik = $request->session()->get('nik');

        return view('dashboard.index', [
            'lastLogin' => $this->repository->lastLogin($username),
            'pegawai' => $this->repository->pegawai($nik),
            'piutangJatuhTempo' => $this->repository->piutangJatuhTempo(),
            'penjualanHariIniTotal' => $this->repository->penjualanHariIniTotal(),
            'kpi' => $this->repository->kpiBulanIni(),
            'sektorAchievement' => $this->repository->sektorAchievement(),
        ]);
    }

    public function sektorDetail(Request $request): JsonResponse
    {
        $idSektor = (string) $request->input('idSektor1');

        return response()->json($this->repository->sektorDetail($idSektor));
    }

    public function penjualanHariIni(): JsonResponse
    {
        return response()->json($this->repository->penjualanHariIni());
    }

    public function agingDetail(Request $request): JsonResponse
    {
        $korek = (string) $request->input('korek');

        return response()->json($this->repository->agingDetailByRekanan($korek));
    }

    public function fakturDetail(Request $request): JsonResponse
    {
        $nofaktur = (string) $request->input('nofaktur');

        return response()->json($this->repository->fakturDetail($nofaktur));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\LabarRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabarController extends Controller
{
    public function __construct(private LabarRepository $repository) {}

    public function index(): View
    {
        $periode = now()->format('Ym');
        $periodeSbl = (now()->year - 1).now()->format('m');

        return view('labar.index', [
            'data' => $this->repository->laporanLabaRugi($periode, $periodeSbl),
            'datax' => $this->repository->historisPenjualanLaba(),
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Services\SektorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SektorController extends Controller
{
    public function __construct(private SektorRepository $repository) {}

    public function index(): View
    {
        return view('sektor.index', [
            'cashWilayah' => $this->repository->cashInPerWilayah(),
            'cashCustomer' => $this->repository->top3CustomerByJatuhTempo(),
        ]);
    }

    public function detail1(): JsonResponse
    {
        return response()->json($this->repository->piutangVsCashInPerCustomer());
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\CashRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CashController extends Controller
{
    public function __construct(private CashRepository $repository) {}

    public function index(): View
    {
        return view('cash.index', [
            'cashWilayah' => $this->repository->cashInPerWilayah(),
            'cashCustomer' => $this->repository->top5CustomerByJatuhTempo(),
        ]);
    }

    public function detail1(): JsonResponse
    {
        return response()->json($this->repository->piutangVsCashInPerCustomer());
    }
}

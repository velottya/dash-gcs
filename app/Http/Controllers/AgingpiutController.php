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
        return view('agingpiut.index', [
            'totalOpenAmount' => $this->repository->totalOpenAmount(),
            'sektorChart' => $this->repository->bySektorChart(),
            'customers' => $this->repository->customerSummary(),
        ]);
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

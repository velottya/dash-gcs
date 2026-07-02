<?php

namespace App\Http\Controllers;

use App\Services\CashRepository;
use App\Support\FormatHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CashController extends Controller
{
    public function __construct(private CashRepository $repository) {}

    public function index(): View
    {
        $cashWilayah = $this->repository->cashInPerWilayah();
        $cashCustomer = $this->repository->top5CustomerByJatuhTempo();

        return view('cash.index', [
            'cashWilayah' => $cashWilayah,
            'cashCustomer' => $cashCustomer,
            'insightCash' => $this->buildInsight($cashWilayah, $cashCustomer),
        ]);
    }

    /**
     * @param  array<int, object>  $cashWilayah
     * @param  array<int, object>  $cashCustomer
     * @return array<int, string>
     */
    private function buildInsight(array $cashWilayah, array $cashCustomer): array
    {
        $insights = [];

        if ($cashWilayah !== []) {
            $totalCashWilayah = array_sum(array_map(fn ($r) => (float) $r->NILAI, $cashWilayah));
            $topWilayah = collect($cashWilayah)->sortByDesc(fn ($r) => (float) $r->NILAI)->first();
            $insights[] = 'Total Cash In bulan ini Rp '.FormatHelper::mask($totalCashWilayah).', terbesar dari wilayah '.trim((string) $topWilayah->WILAYAH).'.';
        }

        if ($cashCustomer !== []) {
            $totalJatuhTempo = array_sum(array_map(fn ($r) => (float) $r->JTEMPO, $cashCustomer));
            $totalCashIn = array_sum(array_map(fn ($r) => (float) $r->NILAI, $cashCustomer));
            if ($totalJatuhTempo > 0) {
                $ratio = round(($totalCashIn / $totalJatuhTempo) * 100, 1);
                $insights[] = 'Cash In dari top 5 customer piutang jatuh tempo setara '.$ratio.'% dari piutang jatuh tempo mereka bulan ini.';
            }
        }

        return $insights;
    }

    public function detail1(): JsonResponse
    {
        return response()->json($this->repository->piutangVsCashInPerCustomer());
    }
}

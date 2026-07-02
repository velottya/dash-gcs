<?php

namespace App\Http\Controllers;

use App\Services\FinancialRepository;
use App\Support\FormatHelper;
use Illuminate\View\View;

class FinancialController extends Controller
{
    public function __construct(private FinancialRepository $repository) {}

    public function balanceSheet(): View
    {
        $series = $this->repository->neracaSeries();
        $lastIdx = count($series['assets']) - 1;
        $assetsEnd = (float) $series['assets'][$lastIdx];
        $assetsStart = (float) $series['assets'][0];
        $liabilityEnd = (float) $series['liability'][$lastIdx];
        $equityEnd = (float) $series['equity'][$lastIdx];
        $liabilityRatio = $assetsEnd != 0 ? round(($liabilityEnd / $assetsEnd) * 100, 1) : 0;

        return view('financial.report', [
            'title' => 'Laporan Neraca',
            'subtitle' => 'Dalam Ribu · '.now()->format('Y'),
            'chartId' => 'balance-sheet',
            'series' => $series,
            'legend' => [
                ['key' => 'assets', 'label' => 'Aset', 'color' => '#2F6C3F'],
                ['key' => 'liability', 'label' => 'Liabilitas', 'color' => '#ef4444'],
                ['key' => 'equity', 'label' => 'Ekuitas', 'color' => '#DAA628'],
            ],
            'insights' => [
                'Total Aset per akhir periode Rp '.FormatHelper::maskRp($assetsEnd).' ('.FormatHelper::trendLabel($assetsEnd, $assetsStart).' sejak awal tahun).',
                'Liabilitas menyumbang '.$liabilityRatio.'% dari total Aset; Ekuitas Rp '.FormatHelper::maskRp($equityEnd).'.',
            ],
        ]);
    }

    public function incomeStatement(): View
    {
        $series = $this->repository->labaRugiSeries();
        $lastIdx = count($series['laba_bersih']) - 1;
        $labaBersihEnd = (float) $series['laba_bersih'][$lastIdx];
        $pendapatanTotal = array_sum(array_map('floatval', $series['pendapatan']));
        $margin = $pendapatanTotal != 0 ? round(($labaBersihEnd / $pendapatanTotal) * 100, 1) : 0;

        return view('financial.report', [
            'title' => 'Laporan Laba/Rugi',
            'subtitle' => 'Dalam Ribu · '.now()->format('Y'),
            'chartId' => 'income-statement',
            'series' => $series,
            'legend' => [
                ['key' => 'pendapatan', 'label' => 'Pendapatan', 'color' => '#2F6C3F'],
                ['key' => 'hpp', 'label' => 'Harga Pokok Penjualan', 'color' => '#ef4444'],
                ['key' => 'beban_usaha', 'label' => 'Beban Usaha', 'color' => '#f97316'],
                ['key' => 'beban_keuangan', 'label' => 'Beban Keuangan', 'color' => '#a855f7'],
                ['key' => 'lain_lain', 'label' => 'Pendapatan/Beban Lain', 'color' => '#0ea5e9'],
                ['key' => 'beban_pajak', 'label' => 'Beban Pajak', 'color' => '#64748b'],
                ['key' => 'laba_bersih', 'label' => 'Laba Bersih (Kumulatif)', 'color' => '#DAA628', 'type' => 'line'],
            ],
            'insights' => [
                'Pendapatan kumulatif tahun ini Rp '.FormatHelper::maskRp($pendapatanTotal).'.',
                'Laba bersih kumulatif Rp '.FormatHelper::maskRp($labaBersihEnd).' ('.$margin.'% margin terhadap pendapatan).',
            ],
        ]);
    }

    public function cashFlow(): View
    {
        $series = $this->repository->arusKasSeries();
        $opTotal = array_sum(array_map('floatval', $series['operasional']));
        $invTotal = array_sum(array_map('floatval', $series['investasi']));
        $finTotal = array_sum(array_map('floatval', $series['pendanaan']));
        $netTotal = $opTotal + $invTotal + $finTotal;

        return view('financial.report', [
            'title' => 'Laporan Arus Kas',
            'subtitle' => 'Dalam Ribu · '.now()->format('Y'),
            'chartId' => 'cash-flow',
            'series' => $series,
            'legend' => [
                ['key' => 'operasional', 'label' => 'Operasional', 'color' => '#2F6C3F'],
                ['key' => 'investasi', 'label' => 'Investasi', 'color' => '#DAA628'],
                ['key' => 'pendanaan', 'label' => 'Pendanaan', 'color' => '#ef4444'],
            ],
            'insights' => [
                'Arus kas operasional Rp '.FormatHelper::maskRp($opTotal).', investasi Rp '.FormatHelper::maskRp($invTotal).', pendanaan Rp '.FormatHelper::maskRp($finTotal).'.',
                'Arus kas bersih tahun ini Rp '.FormatHelper::maskRp($netTotal).' ('.($netTotal >= 0 ? 'kas masuk bersih' : 'kas keluar bersih').').',
            ],
        ]);
    }
}

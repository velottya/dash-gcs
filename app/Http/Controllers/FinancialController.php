<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FinancialController extends Controller
{
    public function balanceSheet(): View
    {
        return view('financial.report', [
            'title' => 'Laporan Neraca',
            'subtitle' => 'Dalam Ribu',
            'chartId' => 'balance-sheet',
            'legend' => [
                ['label' => 'Assets', 'color' => 'bg-green'],
                ['label' => 'Liability', 'color' => 'bg-red-500'],
                ['label' => 'Equity', 'color' => 'bg-gold'],
            ],
        ]);
    }

    public function incomeStatement(): View
    {
        return view('financial.report', [
            'title' => 'Laporan Laba/Rugi',
            'subtitle' => 'Dalam Ribu',
            'chartId' => 'income-statement',
            'legend' => [
                ['label' => 'Revenue', 'color' => 'bg-green'],
                ['label' => 'Net Income', 'color' => 'bg-gold'],
            ],
        ]);
    }

    public function cashFlow(): View
    {
        return view('financial.report', [
            'title' => 'Laporan Arus Kas',
            'subtitle' => 'Dalam Ribu',
            'chartId' => 'cash-flow',
            'legend' => [
                ['label' => 'Operating', 'color' => 'bg-green'],
                ['label' => 'Investing', 'color' => 'bg-gold'],
                ['label' => 'Financing', 'color' => 'bg-red-500'],
            ],
        ]);
    }

    public function salesStock(): View
    {
        return view('financial.report', [
            'title' => 'Sales Report',
            'subtitle' => 'Subsidi (Dalam Juta)',
            'chartId' => 'sales-stock',
            'legend' => [
                ['label' => 'Sales', 'color' => 'bg-green'],
                ['label' => 'RKAP', 'color' => 'bg-gold'],
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SalesController extends Controller
{
    public function subsidi(): View
    {
        return $this->category('Pupuk Subsidi', 'subsidi');
    }

    public function nonsub(): View
    {
        return $this->category('Pupuk Non Subsidi', 'nonsub');
    }

    public function kimia(): View
    {
        return $this->category('Bahan Kimia', 'kimia');
    }

    public function angkutan(): View
    {
        return $this->category('Jasa Angkutan', 'angkutan');
    }

    private function category(string $title, string $chartId): View
    {
        return view('sales.category', ['title' => $title, 'chartId' => $chartId]);
    }
}

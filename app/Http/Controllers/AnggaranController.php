<?php

namespace App\Http\Controllers;

use App\Services\AnggaranRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnggaranController extends Controller
{
    public function __construct(private AnggaranRepository $repository) {}

    public function index(Request $request): View
    {
        $kodeWilayah = $this->repository->kodeWilayah($request->session()->get('nik')) ?? '';

        return view('anggaran.index', [
            'kodeWilayah' => $kodeWilayah,
            'inventoryOptions' => $kodeWilayah ? $this->repository->inventoryOptions($kodeWilayah) : [],
            'ccOptions' => $kodeWilayah ? $this->repository->ccOptions($kodeWilayah) : [],
        ]);
    }
}

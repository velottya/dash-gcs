<?php

namespace App\Http\Controllers;

use App\Services\EasyRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EasyController extends Controller
{
    public function __construct(private EasyRepository $repository) {}

    public function index(Request $request): View
    {
        $tanggal = $request->filled('tanggal')
            ? \Illuminate\Support\Carbon::parse($request->input('tanggal'))
            : now();

        return view('easy.index', [
            'tanggalLabel' => $tanggal->translatedFormat('d F Y'),
            'tanggalInput' => $tanggal->format('Y-m-d'),
            'rows' => $this->repository->dailyDashboard($tanggal->format('d-m-Y')),
        ]);
    }
}

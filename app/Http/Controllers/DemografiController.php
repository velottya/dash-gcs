<?php

namespace App\Http\Controllers;

use App\Services\DemografiRepository;
use Illuminate\View\View;

class DemografiController extends Controller
{
    public function __construct(private DemografiRepository $repository) {}

    public function index(): View
    {
        return view('demografi.index', [
            'total' => $this->repository->totalKaryawan(),
            'totTetap' => $this->repository->totalTetap(),
            'totKontrak' => $this->repository->totalKontrak(),
            'statusKaryawan' => $this->repository->statusKaryawan(),
            'wilayah' => $this->repository->byWilayah(),
            'kelamin' => $this->repository->byKelamin(),
            'pendidikan' => $this->repository->byPendidikan(),
            'jabatan' => $this->repository->byJabatan(),
            'usia' => $this->repository->byUsia(),
        ]);
    }
}

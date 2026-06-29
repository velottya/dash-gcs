<?php

namespace App\Http\Controllers;

use App\Services\ProfileRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private ProfileRepository $repository) {}

    public function company(): View
    {
        return view('profile.company');
    }

    public function user(Request $request): View
    {
        return view('profile.user', [
            'pegawai' => $this->repository->pegawai($request->session()->get('nik')),
        ]);
    }
}

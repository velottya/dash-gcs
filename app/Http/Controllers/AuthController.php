<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function showLogin(): View|RedirectResponse
    {
        if (session('login_status')) {
            return redirect()->route('dashboard1');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->auth->findByUsername($request->string('username')->trim()->toString());

        if (! $user) {
            return back()->withInput()->with('message', 'User tidak ditemukan!');
        }

        if ($user->data_aktif !== 'Aktif') {
            return back()->withInput()->with('message', 'Username Anda Tidak Aktif!');
        }

        if (! Hash::check($request->input('password'), $user->PASSWORD)) {
            return back()->withInput()->with('message', 'Password anda salah!');
        }

        $request->session()->put([
            'nik' => $user->NIK,
            'username' => $user->USERNAME,
            'level' => $user->ID_LEVEL,
            'img' => trim((string) $user->IMG),
            'nama' => $user->nama,
            'jabatan' => $user->jabatan,
            'login_status' => true,
        ]);
        $request->session()->regenerate();

        $this->auth->recordLogin($user->NIK, $user->USERNAME);

        return redirect()->route('dashboard1');
    }

    public function logout(Request $request): RedirectResponse
    {
        $username = $request->session()->get('username');

        if ($username) {
            $this->auth->recordLogout($username);
        }

        $request->session()->forget(['nik', 'username', 'level', 'img', 'nama', 'jabatan', 'login_status']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

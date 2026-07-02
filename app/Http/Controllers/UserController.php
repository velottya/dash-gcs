<?php

namespace App\Http\Controllers;

use App\Services\UserRepository;
use App\Support\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserRepository $repo) {}

    public function index(): View
    {
        $isGmView = Role::isGm();

        $users = $isGmView
            ? $this->repo->listManagersOfGm((string) session('nik'))
            : $this->repo->listUsers();

        return view('users.index', [
            'users'     => $users,
            'gms'       => $this->repo->listGms(),
            'isGmView'  => $isGmView,
        ]);
    }

    public function searchPegawai(Request $request): JsonResponse
    {
        $keyword = $request->string('q')->trim()->toString();
        return response()->json($this->repo->searchPegawai($keyword));
    }

    public function searchMstUser(Request $request): JsonResponse
    {
        $keyword = $request->string('q')->trim()->toString();
        return response()->json($this->repo->searchMstUser($keyword));
    }

    public function detail(string $nik): JsonResponse
    {
        $user = $this->repo->findUser($nik);
        if (! $user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $gm = null;
        if ((int) $user->ID_LEVEL === Role::MANAGER) {
            $gm = $this->repo->getGmOfManager($nik);
        }

        $managers = [];
        if ((int) $user->ID_LEVEL === Role::GM) {
            $managers = $this->repo->getManagersOfGm($nik);
        }

        return response()->json([
            'user'     => $user,
            'gm'       => $gm,
            'managers' => $managers,
            'role_label' => Role::label((int) $user->ID_LEVEL),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nik'      => ['required', 'string'],
            'username' => ['required', 'string', 'min:3'],
            'password' => ['required', 'string', 'min:6'],
            'level'    => ['required', 'in:2,3,4'],
        ]);

        $nik      = $request->string('nik')->trim()->toString();
        $username = $request->string('username')->trim()->toString();
        $level    = (int) $request->input('level');

        if (! $this->repo->findPegawai($nik)) {
            return back()->withInput()->with('error', 'NIK tidak ditemukan di data pegawai.');
        }

        if ($this->repo->existsByNik($nik)) {
            return back()->withInput()->with('error', 'Pegawai ini sudah memiliki akun.');
        }

        if ($this->repo->existsByUsername($username)) {
            return back()->withInput()->with('error', 'Username sudah digunakan.');
        }

        $this->repo->createUser($nik, $username, $request->input('password'), $level);

        if ($level === Role::MANAGER && $request->filled('nik_gm')) {
            $this->repo->setGmForManager($request->string('nik_gm')->trim()->toString(), $nik);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, string $nik): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3'],
            'level'    => ['required', 'in:2,3,4'],
            'status'   => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $username = $request->string('username')->trim()->toString();
        $level    = (int) $request->input('level');

        if ($this->repo->existsByUsername($username, $nik)) {
            return back()->withInput()->with('error', 'Username sudah digunakan.');
        }

        $this->repo->updateUser($nik, $username, $level, $request->input('status'));

        if ($level === Role::MANAGER) {
            $nikGm = $request->string('nik_gm')->trim()->toString();
            $this->repo->setGmForManager($nikGm, $nik);
        } else {
            // Jika bukan manager, hapus mapping GM jika ada
            $this->repo->setGmForManager('', $nik);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function resetPassword(Request $request, string $nik): RedirectResponse
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $this->repo->resetPassword($nik, $request->input('new_password'));

        return redirect()->route('users.index')->with('success', 'Password berhasil direset.');
    }

    public function destroy(string $nik): RedirectResponse
    {
        // Jangan bisa hapus superadmin
        $user = $this->repo->findUser($nik);
        if ($user && (int) $user->ID_LEVEL === Role::SUPERADMIN) {
            return redirect()->route('users.index')->with('error', 'Akun superadmin tidak dapat dihapus.');
        }

        $this->repo->deleteUser($nik);
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}

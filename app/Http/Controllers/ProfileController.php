<?php

namespace App\Http\Controllers;

use App\Services\ProfileRepository;
use App\Support\FormatHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private ProfileRepository $repository) {}

    public function user(Request $request): View
    {
        $nik = (string) $request->session()->get('nik', '');

        return view('profile.user', [
            'pegawai'      => $this->repository->pegawai($nik),
            'extra'        => $this->repository->getUserExtra($nik),
            'lastLogin'    => $this->repository->lastLogin($nik),
            'fallbackNama' => $request->session()->get('nama'),
            'fallbackJabatan' => $request->session()->get('jabatan'),
            'photoUrl'     => FormatHelper::profilePhotoUrl($request->session()->get('img')),
        ]);
    }

    public function updateUser(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $nik  = (string) $request->session()->get('nik', '');
        $data = $request->only(['phone', 'email']);
        $this->repository->saveUserExtra($nik, array_map('trim', $data));

        if ($request->hasFile('foto')) {
            $old      = $this->repository->getImg($nik);
            $filename = $nik.'_'.now()->timestamp.'.'.$request->file('foto')->extension();
            $request->file('foto')->storeAs('profile_photos', $filename, 'public');
            $this->repository->updateImg($nik, $filename);
            $request->session()->put('img', $filename);

            if ($old && Storage::disk('public')->exists('profile_photos/'.$old)) {
                Storage::disk('public')->delete('profile_photos/'.$old);
            }
        }

        return redirect()->route('profile.user')->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'      => ['required', 'string'],
            'new_password'          => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $nik  = (string) $request->session()->get('nik', '');
        $hash = $this->repository->getPassword($nik);

        if (! $hash || ! Hash::check($request->input('current_password'), $hash)) {
            return back()->with('error', 'Password lama tidak sesuai.');
        }

        $this->repository->updatePassword($nik, Hash::make($request->input('new_password')));

        return redirect()->route('profile.user')->with('success', 'Password berhasil diubah.');
    }

    public function company(): View
    {
        return view('profile.company', [
            'company' => $this->repository->getCompany(),
        ]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $existing = $this->repository->getCompany();

        $existing['background'] = trim((string) $request->input('background', $existing['background']));
        $existing['alamat']     = trim((string) $request->input('alamat', $existing['alamat']));
        $existing['phone']      = trim((string) $request->input('phone', $existing['phone']));
        $existing['fax']        = trim((string) $request->input('fax', $existing['fax']));
        $existing['email']      = trim((string) $request->input('email', $existing['email']));

        $komisarisJabatan = $request->input('komisaris_jabatan', []);
        $komisarisNama    = $request->input('komisaris_nama', []);
        $existing['komisaris'] = [];
        foreach ($komisarisJabatan as $i => $jabatan) {
            $nama = $komisarisNama[$i] ?? '';
            if (trim($jabatan) !== '' || trim($nama) !== '') {
                $existing['komisaris'][] = ['jabatan' => trim($jabatan), 'nama' => trim($nama)];
            }
        }

        $direksiJabatan = $request->input('direksi_jabatan', []);
        $direksiNama    = $request->input('direksi_nama', []);
        $existing['direksi'] = [];
        foreach ($direksiJabatan as $i => $jabatan) {
            $nama = $direksiNama[$i] ?? '';
            if (trim($jabatan) !== '' || trim($nama) !== '') {
                $existing['direksi'][] = ['jabatan' => trim($jabatan), 'nama' => trim($nama)];
            }
        }

        $sahamNama = $request->input('saham_nama', []);
        $sahamPct  = $request->input('saham_pct', []);
        $existing['pemegang_saham'] = [];
        foreach ($sahamNama as $i => $nama) {
            $pct = $sahamPct[$i] ?? '';
            if (trim($nama) !== '') {
                $existing['pemegang_saham'][] = ['nama' => trim($nama), 'persentase' => trim($pct)];
            }
        }

        $this->repository->saveCompany($existing);

        return redirect()->route('profile.company')->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}

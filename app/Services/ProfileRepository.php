<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProfileRepository
{
    public function pegawai(string $nik): ?object
    {
        return DB::selectOne(
            "SELECT Nik, nama, jabatan, BAGIAN, DIVISI, UNIT_KERJA, WILAYAH, data_aktif
             FROM dbo.PEGAWAI_SDM WHERE Nik = ?",
            [$nik]
        );
    }

    public function lastLogin(string $nik): ?string
    {
        $row = DB::selectOne('SELECT LAST_LOGIN FROM DASH.MST_USER WHERE NIK = ?', [$nik]);
        return $row?->LAST_LOGIN
            ? \Carbon\Carbon::parse($row->LAST_LOGIN)->format('d/m/Y H:i')
            : null;
    }

    public function getPassword(string $nik): ?string
    {
        $row = DB::selectOne('SELECT PASSWORD FROM DASH.MST_USER WHERE NIK = ?', [$nik]);
        return $row?->PASSWORD;
    }

    public function updatePassword(string $nik, string $hashed): void
    {
        DB::update('UPDATE DASH.MST_USER SET PASSWORD = ? WHERE NIK = ?', [$hashed, $nik]);
    }

    public function getImg(string $nik): ?string
    {
        return DB::selectOne('SELECT IMG FROM DASH.MST_USER WHERE NIK = ?', [$nik])?->IMG;
    }

    public function updateImg(string $nik, string $filename): void
    {
        DB::update('UPDATE DASH.MST_USER SET IMG = ? WHERE NIK = ?', [$filename, $nik]);
    }

    // ── User extra (phone, email — stored in DASH.USER_EXTRA) ───────────────

    public function getUserExtra(string $nik): array
    {
        $row = DB::selectOne('SELECT PHONE, EMAIL FROM DASH.USER_EXTRA WHERE NIK = ?', [$nik]);
        if (! $row) return [];
        return ['phone' => $row->PHONE ?? '', 'email' => $row->EMAIL ?? ''];
    }

    public function saveUserExtra(string $nik, array $data): void
    {
        $exists = DB::selectOne('SELECT 1 AS found FROM DASH.USER_EXTRA WHERE NIK = ?', [$nik]);
        if ($exists) {
            DB::update(
                'UPDATE DASH.USER_EXTRA SET PHONE = ?, EMAIL = ?, DATE_UPDATE = GETDATE() WHERE NIK = ?',
                [$data['phone'] ?? null, $data['email'] ?? null, $nik]
            );
        } else {
            DB::insert(
                'INSERT INTO DASH.USER_EXTRA (NIK, PHONE, EMAIL) VALUES (?, ?, ?)',
                [$nik, $data['phone'] ?? null, $data['email'] ?? null]
            );
        }
    }

    // ── Company profile (JSON file, editable through UI) ────────────────────

    private function companyPath(): string
    {
        return storage_path('app/company_profile.json');
    }

    public function getCompany(): array
    {
        if (file_exists($this->companyPath())) {
            $data = json_decode(file_get_contents($this->companyPath()), true);
            if (is_array($data)) {
                return $data;
            }
        }

        return $this->defaultCompany();
    }

    public function saveCompany(array $data): void
    {
        file_put_contents($this->companyPath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function defaultCompany(): array
    {
        return [
            'background' => 'PT Gresik Cipta Sejahtera didirikan pada tanggal 15 Juni 1972, merupakan salah satu perusahaan di lingkungan Petrokimia Gresik Group yang bergerak di bidang perdagangan, logistik dan produsen pupuk.',
            'komisaris' => [
                ['jabatan' => 'Komisaris Utama', 'nama' => 'Eko Suroso'],
                ['jabatan' => 'Komisaris', 'nama' => 'Bambang Ariwibowo'],
            ],
            'direksi' => [
                ['jabatan' => 'Direktur Utama', 'nama' => ''],
                ['jabatan' => 'Direktur Komersial', 'nama' => 'Mohammad Armi Kurnia'],
                ['jabatan' => 'Direktur Keuangan', 'nama' => 'Nugroho Iman Prakosa'],
            ],
            'pemegang_saham' => [
                ['nama' => 'Yayasan Petrokimia Gresik', 'persentase' => '98,92%'],
                ['nama' => 'Koperasi Konsumen Karyawan Keluarga Besar Petrokimia Gresik', 'persentase' => '1,08%'],
            ],
            'alamat' => 'Jl. KIG Raya Selatan Blok A5 Gresik, Jawa Timur, Indonesia',
            'phone' => '(031) 398 5543, 398 4822, 395 1894',
            'fax' => '(031) 398 1082, 398 2641',
            'email' => 'kantorpusat@gcs-gresik.com',
        ];
    }
}

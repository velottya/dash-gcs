@extends('layouts.app')

@section('title', 'Profil Perusahaan')

@section('content')
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="font-heading mb-3 flex items-center gap-2 text-base font-extrabold text-dark">
                <i class="fas fa-money-check text-green"></i> Latar Belakang Perusahaan
            </h2>
            <p class="text-sm leading-relaxed text-dark/70">
                PT Gresik Cipta Sejahtera didirikan pada tanggal 15 Juni 1972, merupakan salah satu perusahaan di lingkungan
                Petrokimia Gresik Group yang bergerak di bidang perdagangan, logistik dan produsen pupuk.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="font-heading mb-3 flex items-center gap-2 text-sm font-extrabold text-dark">
                        <i class="fas fa-users text-green"></i> Dewan Komisaris
                    </h3>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-dark/5">
                            <tr><td class="py-2 text-dark/60">Komisaris Utama</td><td class="py-2 font-medium text-dark">Eko Suroso</td></tr>
                            <tr><td class="py-2 text-dark/60">Komisaris</td><td class="py-2 font-medium text-dark">Bambang Ariwibowo</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="font-heading mb-3 flex items-center gap-2 text-sm font-extrabold text-dark">
                        <i class="fas fa-users text-green"></i> Dewan Direksi
                    </h3>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-dark/5">
                            <tr><td class="py-2 text-dark/60">Direktur Utama</td><td class="py-2 font-medium text-dark">Awang Djohan Bachtiar</td></tr>
                            <tr><td class="py-2 text-dark/60">Direktur Komersial</td><td class="py-2 font-medium text-dark">Mohammad Armi Kurnia</td></tr>
                            <tr><td class="py-2 text-dark/60">Direktur Keuangan</td><td class="py-2 font-medium text-dark">Nugroho Iman Prakosa</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-dark/10 px-6 py-4">
                    <h3 class="font-heading flex items-center gap-2 text-sm font-extrabold text-dark">
                        <i class="fas fa-share text-green"></i> Pemegang Saham
                    </h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-dark/5 text-left text-xs uppercase text-dark/50">
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark/5">
                        <tr>
                            <td class="px-6 py-3">Yayasan Petrokimia Gresik</td>
                            <td class="px-6 py-3"><span class="rounded-full bg-green/10 px-2 py-0.5 text-xs font-semibold text-green">98,92%</span></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3">Koperasi Konsumen Karyawan Keluarga Besar Petrokimia Gresik</td>
                            <td class="px-6 py-3"><span class="rounded-full bg-green/10 px-2 py-0.5 text-xs font-semibold text-green">1,08%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="font-heading mb-3 flex items-center gap-2 text-sm font-extrabold text-dark">
                    <i class="fas fa-map-marker-alt text-green"></i> Alamat Kantor Pusat
                </h3>
                <div class="space-y-1.5 text-sm text-dark/70">
                    <p>Jl. KIG Raya Selatan Blok A5 Gresik, Jawa Timur, Indonesia</p>
                    <p>Phone : (031) 398 5543, 398 4822, 395 1894</p>
                    <p>Fax : (031) 398 1082, 398 2641</p>
                    <p>Email : kantorpusat@gcs-gresik.com</p>
                </div>
            </div>
        </div>
    </div>
@endsection

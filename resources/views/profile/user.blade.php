@extends('layouts.app')

@section('title', 'Profil User')

@section('content')
    <div class="max-w-lg">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="px-6 pb-2 pt-6 text-xs uppercase tracking-wide text-dark/40">{{ $pegawai->BAGIAN ?? '-' }}</div>
            <div class="flex items-center gap-4 px-6 pb-6">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gold text-2xl font-black text-dark">
                    {{ strtoupper(substr($pegawai->nama ?? '?', 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-heading text-lg font-extrabold text-dark">{{ ucwords(strtolower($pegawai->nama ?? '-')) }}</h2>
                    <ul class="mt-1 space-y-1 text-sm text-dark/60">
                        <li><i class="fas fa-building mr-2 text-dark/30"></i>Gresik, Jawa Timur, Indonesia</li>
                        <li><i class="fas fa-phone mr-2 text-dark/30"></i>0000 0000 0000</li>
                    </ul>
                </div>
            </div>
            <div class="flex justify-end border-t border-dark/10 px-6 py-4">
                <button type="button" class="rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white transition hover:bg-dark-soft">
                    <i class="fas fa-user mr-1"></i> Ubah Profil
                </button>
            </div>
        </div>
    </div>
@endsection

@extends('settings.layout')

@php
    $settingsTitle = 'Pengaturan Jam Sekolah — MySkanic';
    $settingsPageTitle = 'Jam Sekolah';
    $settingsPageSubtitle = 'Konfigurasi batas waktu presensi masuk dan waktu kepulangan peserta didik';
@endphp

@section('settings-content')
<div class="space-y-6">

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
        <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-xl font-bold">
                <i class='bx bx-time-five'></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Konfigurasi Jam Presensi</h3>
                <p class="text-xs text-slate-400">Atur jam batas keterlambatan dan jam buka presensi kepulangan</p>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-5 max-w-2xl">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="jam_masuk" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Batas Jam Masuk (Terlambat setelah ini) <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_masuk" id="jam_masuk" required value="{{ old('jam_masuk', $jamMasuk) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    @error('jam_masuk')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                    <span class="text-[11px] text-slate-400 mt-1 block">Siswa yang presensi setelah jam ini akan ditandai terlambat.</span>
                </div>

                <div>
                    <label for="jam_pulang" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Jam Buka Absen Pulang <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="jam_pulang" id="jam_pulang" required value="{{ old('jam_pulang', $jamPulang) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                    @error('jam_pulang')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                    <span class="text-[11px] text-slate-400 mt-1 block">Presensi kepulangan baru dapat dilakukan setelah jam ini.</span>
                </div>
            </div>

            <div>
                <label for="nama_sekolah" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Nama Resmi Sekolah
                </label>
                <input type="text" name="nama_sekolah" id="nama_sekolah" maxlength="150" value="{{ old('nama_sekolah', $namaSekolah) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003EA7]/20 focus:border-[#003EA7]">
                <span class="text-[11px] text-slate-400 mt-1 block">Digunakan pada header laporan, dashboard dan export dokumen.</span>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit"
                    class="px-6 py-2.5 bg-[#003EA7] hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-900/20 transition inline-flex items-center gap-2">
                    <i class='bx bx-save text-base'></i>
                    <span>Simpan Pengaturan Jam</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

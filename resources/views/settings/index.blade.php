@extends('layouts.app')

@section('title', 'Pengaturan Sekolah')
@section('page-title', 'Pengaturan Sekolah')
@section('page-subtitle', 'Jam masuk, jam pulang, dan nama sekolah')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Pengaturan Sekolah</li>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="glass-card rounded-2xl border border-dark-200/50 p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center text-primary-600">
                    <i class='bx bx-time-five text-xl'></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-dark-800">Jam Absensi</h3>
                    <p class="text-xs text-dark-400">Batas keterlambatan masuk & waktu buka absen pulang</p>
                </div>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jam_masuk" class="block text-sm font-medium text-dark-700 mb-1.5">Batas Jam Masuk (terlambat setelah ini)</label>
                        <input type="time" name="jam_masuk" id="jam_masuk" required value="{{ old('jam_masuk', $jamMasuk) }}"
                            class="block w-full px-3.5 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                        @error('jam_masuk')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="jam_pulang" class="block text-sm font-medium text-dark-700 mb-1.5">Jam Buka Absen Pulang</label>
                        <input type="time" name="jam_pulang" id="jam_pulang" required value="{{ old('jam_pulang', $jamPulang) }}"
                            class="block w-full px-3.5 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                        @error('jam_pulang')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="nama_sekolah" class="block text-sm font-medium text-dark-700 mb-1.5">Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" id="nama_sekolah" maxlength="150" value="{{ old('nama_sekolah', $namaSekolah) }}"
                        class="block w-full px-3.5 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                </div>
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class='bx bx-save'></i> Simpan Pengaturan
                </button>
            </form>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class='bx bx-info-circle text-xl'></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-dark-800">Informasi</h3>
                    <p class="text-xs text-dark-400">Bagaimana jam ini dipakai</p>
                </div>
            </div>
            <ul class="space-y-3 text-sm text-dark-600">
                <li class="flex gap-2"><i class='bx bx-check-circle text-emerald-500 mt-0.5'></i> Absen <b>masuk</b> setelah batas jam masuk dihitung <b>terlambat</b> (menit & durasi).</li>
                <li class="flex gap-2"><i class='bx bx-check-circle text-emerald-500 mt-0.5'></i> Absen <b>pulang</b> baru bisa dilakukan mulai dari jam pulang yang diatur.</li>
                <li class="flex gap-2"><i class='bx bx-check-circle text-emerald-500 mt-0.5'></i> Nilai ini otomatis dipakai halaman absensi siswa — tidak perlu ubah kode.</li>
            </ul>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page-title', 'Pengaturan Sistem')
@section('page-subtitle', 'Konfigurasi parameter global sistem absensi')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Pengaturan</li>
@endsection

@section('content')
    <form action="{{ route('setting.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Kuota Alpha & SP --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 sm:p-8">
                <div class="border-b border-dark-100 pb-4 mb-6">
                    <h3 class="text-lg font-bold text-dark-800 flex items-center gap-2">
                        <i class='bx bx-shield-quarter text-primary-500 text-xl'></i> Kuota Alpha & Surat Peringatan
                    </h3>
                    <p class="text-sm text-dark-500 mt-1">Atur ambang batas Alpha untuk penerbitan SP otomatis.</p>
                </div>

                <div class="space-y-5">
                    {{-- SP1 --}}
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-2">
                            Kuota Alpha untuk <span class="text-amber-600 font-bold">SP1</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-dark-400 text-sm font-bold">SP1</span>
                            </div>
                            <input type="number" name="kuota_alpha_SP1" value="{{ $settings['kuota_alpha_SP1'] }}" min="1" max="50"
                                class="block w-full pl-14 pr-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        </div>
                        <p class="text-[10px] text-dark-400 mt-1 italic">* Siswa yang Alpha sebanyak ini akan otomatis mendapat SP1 dan wajib lampiran saat izin.</p>
                    </div>

                    {{-- SP2 --}}
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-2">
                            Kuota Alpha untuk <span class="text-orange-600 font-bold">SP2</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-dark-400 text-sm font-bold">SP2</span>
                            </div>
                            <input type="number" name="kuota_alpha_SP2" value="{{ $settings['kuota_alpha_SP2'] }}" min="1" max="50"
                                class="block w-full pl-14 pr-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        </div>
                    </div>

                    {{-- SP3 --}}
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-2">
                            Kuota Alpha untuk <span class="text-rose-600 font-bold">SP3</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-dark-400 text-sm font-bold">SP3</span>
                            </div>
                            <input type="number" name="kuota_alpha_SP3" value="{{ $settings['kuota_alpha_SP3'] }}" min="1" max="50"
                                class="block w-full pl-14 pr-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200">
                        <div class="flex items-start gap-3">
                            <i class='bx bx-info-circle text-xl text-amber-500 mt-0.5'></i>
                            <div class="text-sm text-amber-800">
                                <p class="font-bold mb-1">Cara Kerja SP Otomatis:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs text-amber-700">
                                    <li>Guru menandai siswa <b>Alfa</b> lewat Absensi Harian.</li>
                                    <li>Saat total Alfa mencapai <b>kuota</b>, sistem otomatis terbitkan SP.</li>
                                    <li>Izin dari orangtua akan <b>wajib lampiran</b> setelah kuota SP1.</li>
                                    <li>Guru/Admin juga bisa menerbitkan SP secara manual.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Identitas Sekolah --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 sm:p-8">
                <div class="border-b border-dark-100 pb-4 mb-6">
                    <h3 class="text-lg font-bold text-dark-800 flex items-center gap-2">
                        <i class='bx bxs-school text-primary-500 text-xl'></i> Identitas Sekolah
                    </h3>
                    <p class="text-sm text-dark-500 mt-1">Digunakan untuk kop surat SP yang dicetak.</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-2">Nama Sekolah</label>
                        <input type="text" name="nama_sekolah" value="{{ $settings['nama_sekolah'] }}"
                            class="block w-full px-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-2">Nama Kepala Sekolah</label>
                        <input type="text" name="kepala_sekolah" value="{{ $settings['kepala_sekolah'] }}" placeholder="Nama lengkap..."
                            class="block w-full px-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-2">NIP Kepala Sekolah</label>
                        <input type="text" name="nip_kepsek" value="{{ $settings['nip_kepsek'] }}" placeholder="NIP..."
                            class="block w-full px-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-700 mb-2">Batas Jam Masuk (Terlambat)</label>
                        <input type="time" name="late_threshold" value="{{ $settings['late_threshold'] }}" step="1"
                            class="block w-full px-4 py-3 border border-dark-200 rounded-xl bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        <p class="text-[10px] text-dark-400 mt-1 italic">* Contoh: 07:30:00. Jika lewat jam ini, siswa dianggap terlambat.</p>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="mt-8 pt-4 border-t border-dark-100 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all duration-200">
                        <i class='bx bx-save text-xl'></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </div>

        </div>
    </form>
@endsection

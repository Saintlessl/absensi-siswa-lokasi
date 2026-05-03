@extends('layouts.app')

@section('title', 'Riwayat Kehadiran')
@section('page-title', 'Riwayat Kehadiran')
@section('page-subtitle', 'Rekam jejak absensi harian Anda')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Riwayat Kehadiran</li>
@endsection

@section('content')
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
        {{-- Header Data --}}
        <div class="px-6 py-5 border-b border-dark-100 flex items-center justify-between bg-dark-50/30">
            <div>
                <h3 class="text-lg font-bold text-dark-800">Daftar Riwayat</h3>
                <p class="text-sm text-dark-500 mt-1">Daftar kehadiran, izin, dan alpa Anda tercatat di bawah ini.</p>
            </div>
            <div
                class="hidden sm:flex px-4 py-2 bg-white border border-dark-200 rounded-xl shadow-sm text-sm font-medium text-dark-600 items-center gap-2">
                <i class='bx bx-abacus text-primary-500'></i>
                Total: {{ count($riwayat) }} Catatan
            </div>
        </div>{{-- Rekap Statistik Bulan Ini --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            {{-- Hadir --}}
            <div class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class='bx bx-check-shield text-xl'></i>
                    </div>
                    <div>
                        <p class="text-xs text-dark-500 font-medium uppercase tracking-wider">Hadir</p>
                        <h4 class="text-xl font-bold text-dark-800">{{ $rekap['hadir'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- Izin --}}
            <div class="bg-white p-4 rounded-2xl border border-primary-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600">
                        <i class='bx bx-envelope text-xl'></i>
                    </div>
                    <div>
                        <p class="text-xs text-dark-500 font-medium uppercase tracking-wider">Izin</p>
                        <h4 class="text-xl font-bold text-dark-800">{{ $rekap['izin'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- Sakit --}}
            <div class="bg-white p-4 rounded-2xl border border-amber-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <i class='bx bx-plus-medical text-lg'></i>
                    </div>
                    <div>
                        <p class="text-xs text-dark-500 font-medium uppercase tracking-wider">Sakit</p>
                        <h4 class="text-xl font-bold text-dark-800">{{ $rekap['sakit'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- Alpa --}}
            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                        <i class='bx bx-x-circle text-xl'></i>
                    </div>
                    <div>
                        <p class="text-xs text-dark-500 font-medium uppercase tracking-wider">Alpa</p>
                        <h4 class="text-xl font-bold text-dark-800">{{ $rekap['alpa'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Informasi --}}
        <div class="mb-4 flex items-center gap-2 text-sm text-dark-500 bg-dark-50/50 p-3 rounded-xl border border-dark-100">
            <i class='bx bx-info-circle text-primary-500 text-base'></i>
            Statistik di atas menjumlahkan kehadiran Anda pada bulan
            <strong>{{ \Carbon\Carbon::now()->isoFormat('MMMM Y') }}</strong>.
        </div>


        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-50/50 border-b border-dark-200/50">
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-16 text-center">
                            No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Hari</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">
                            Lokasi Geo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($riwayat as $log)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $loop->iteration }}</td>

                            {{-- Tanggal --}}
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                {{ \Carbon\Carbon::parse($log->tanggal)->isoFormat('dddd, D MMMM Y') }}
                            </td>
                            {{-- Hari --}}
                            <td class="px-6 py-4 text-sm font-medium text-dark-800">
                                {{ \Carbon\Carbon::parse($log->tanggal)->isoFormat('dddd') }}
                            </td>
                            {{-- Jam Masuk --}}
                            <td class="px-6 py-4 text-sm text-dark-600">
                                @if ($log->jam_masuk)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-dark-100 text-dark-700">
                                        <i class='bx bx-time'></i>{{ date('H:i', strtotime($log->jam_masuk)) }} WIB
                                    </span>
                                @else
                                    <span class="text-dark-400 italic text-xs">-</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-sm">
                                @if (strtolower($log->status) == 'hadir')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class='bx bx-check-shield text-sm'></i> Hadir
                                    </span>
                                @elseif(strtolower($log->status) == 'izin')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                        <i class='bx bx-envelope text-sm'></i> Izin
                                    </span>
                                @elseif(strtolower($log->status) == 'sakit')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class='bx bx-plus-medical text-sm'></i> Sakit
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class='bx bx-x-circle text-sm'></i> Alfa
                                    </span>
                                @endif
                            </td>

                            {{-- Keterangan --}}
                            <td class="px-6 py-4 text-sm text-dark-600 max-w-[200px] truncate"
                                title="{{ $log->keterangan }}">
                                {{ $log->keterangan ?? '-' }}
                            </td>

                            {{-- Geolocation Pin --}}
                            <td class="px-6 py-4 text-sm text-center">
                                @if ($log->latitude && $log->longitude)
                                    <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}"
                                        target="_blank"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-primary-600 bg-primary-50 hover:bg-primary-100 transition-colors duration-200"
                                        title="Buka Maps">
                                        <i class='bx bx-map-pin text-lg'></i>
                                    </a>
                                @else
                                    <span class="text-dark-300"><i class='bx bx-minus'></i></span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                        <i class='bx bx-history text-3xl text-dark-400'></i>
                                    </div>
                                    <h3 class="text-sm font-medium text-dark-900">Belum ada riwayat kehadiran</h3>
                                    <p class="mt-1 text-sm text-dark-500">Anda belum pernah melakukan perekaman data
                                        absensi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

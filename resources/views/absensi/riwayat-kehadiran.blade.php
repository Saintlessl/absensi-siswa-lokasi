@extends('layouts.app')

@section('title', 'Riwayat Kehadiran')
@section('page-title', 'Riwayat Kehadiran')
@section('page-subtitle', 'Rekam jejak absensi harian Anda')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Riwayat Kehadiran</li>
@endsection

@section('content')
    <div class="flex flex-col gap-6">
        {{-- Rekap Statistik Bulan Ini --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Hadir --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class='bx bx-check-shield text-xl'></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Hadir</p>
                        <h4 class="text-xl font-bold text-slate-800">{{ $rekap['hadir'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- Izin --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-[#003FA9]">
                        <i class='bx bx-envelope text-xl'></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Izin</p>
                        <h4 class="text-xl font-bold text-slate-800">{{ $rekap['izin'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- Sakit --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <i class='bx bx-plus-medical text-lg'></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Sakit</p>
                        <h4 class="text-xl font-bold text-slate-800">{{ $rekap['sakit'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- Alpa --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                        <i class='bx bx-x-circle text-xl'></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Alpa</p>
                        <h4 class="text-xl font-bold text-slate-800">{{ $rekap['alpa'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
            {{-- Header Data --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-5 mb-5 border-b border-slate-100 gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Daftar Riwayat Kehadiran</h3>
                    <p class="text-xs text-slate-400 mt-1">Daftar kehadiran, izin, dan alpa Anda tercatat pada periode {{ \Carbon\Carbon::now()->isoFormat('MMMM Y') }}.</p>
                </div>
                <div class="px-3.5 py-1.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-semibold text-slate-600 flex items-center gap-2">
                    <i class='bx bx-history text-[#003FA9]'></i>
                    Total: {{ count($riwayat) }} Catatan
                </div>
            </div>

            {{-- Table Container --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-16 text-center">No</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hari</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jam Masuk</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keterangan</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Lokasi Geo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($riwayat as $log)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 text-slate-400 text-center text-xs">{{ $loop->iteration }}</td>

                                {{-- Tanggal --}}
                                <td class="py-4 px-4 font-semibold text-slate-800">
                                    {{ \Carbon\Carbon::parse($log->tanggal)->isoFormat('D MMMM Y') }}
                                </td>

                                {{-- Hari --}}
                                <td class="py-4 px-4 text-slate-600">
                                    {{ \Carbon\Carbon::parse($log->tanggal)->isoFormat('dddd') }}
                                </td>

                                {{-- Jam Masuk --}}
                                <td class="py-4 px-4 text-slate-600">
                                    @if ($log->jam_masuk)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                            <i class='bx bx-time'></i>{{ date('H:i', strtotime($log->jam_masuk)) }} WIB
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-xs">-</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-4">
                                    @if (strtolower($log->status) == 'hadir')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">
                                            Hadir
                                        </span>
                                    @elseif(strtolower($log->status) == 'izin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">
                                            Izin
                                        </span>
                                    @elseif(strtolower($log->status) == 'sakit')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">
                                            Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-600 text-white">
                                            Alfa
                                        </span>
                                    @endif
                                </td>

                                {{-- Keterangan --}}
                                <td class="py-4 px-4 text-slate-600 max-w-[200px] truncate" title="{{ $log->keterangan }}">
                                    {{ $log->keterangan ?? '-' }}
                                </td>

                                {{-- Geolocation Pin --}}
                                <td class="py-4 px-4 text-center">
                                    @if ($log->latitude && $log->longitude)
                                        <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center justify-center"
                                            title="Buka Maps">
                                            <i class='bx bx-map-pin text-lg'></i>
                                        </a>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                            <i class='bx bx-history text-2xl text-slate-400'></i>
                                        </div>
                                        <p class="text-sm font-medium text-slate-600">Belum ada riwayat kehadiran</p>
                                        <p class="text-xs text-slate-400 mt-1">Anda belum pernah melakukan perekaman data absensi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

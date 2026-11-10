@extends('layouts.app')

@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi')
@section('page-subtitle', 'Rekapitulasi Kehadiran Seluruh Siswa Per Bulan')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Rekap Absensi</li>
@endsection

@section('content')
    <div class="flex flex-col gap-6">

        {{-- Filter Bulan & Tahun --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-6">
            <form action="{{ route('rekapabsensi') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-dark-700 mb-1.5">Bulan</label>
                    <select name="bulan"
                        class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                {{ $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $i, 1)->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-dark-700 mb-1.5">Tahun</label>
                    <select name="tahun"
                        class="block w-full px-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200">
                        @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit"
                    class="px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
                    <i class='bx bx-filter-alt text-lg'></i>
                    Tampilkan
                </button>
            </form>
        </div>

        {{-- Info Bar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-dark-600">
                <i class='bx bx-calendar text-primary-500 text-lg'></i>
                Periode: <strong class="text-dark-800">{{ $namaBulan }}</strong>
                <span class="text-dark-400">|</span>
                Hari Efektif: <strong class="text-dark-800">{{ $hariEfektif }} hari</strong> (Sabtu masuk, Minggu libur)
            </div>
            <div
                class="flex items-center gap-2 px-4 py-2 bg-white border border-dark-200 rounded-xl text-sm font-medium text-dark-600 shadow-sm">
                <i class='bx bx-group text-primary-500'></i>
                {{ $totalSiswa }} Siswa
            </div>
        </div>

        {{-- Tabel Rekap --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA SISWA</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">KELAS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">HADIR</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">IZIN</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">SAKIT</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">ALPA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($rekap as $r)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 text-xs font-medium text-slate-400 text-center">{{ $rekap->firstItem() + $loop->index }}</td>
                                <td class="py-4 px-4 font-mono font-bold text-slate-900 text-xs sm:text-sm">{{ $r['siswa']->nis }}</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center font-bold text-xs uppercase">
                                            {{ substr($r['siswa']->nama, 0, 1) }}
                                        </div>
                                        <span class="font-bold text-slate-900 text-xs sm:text-sm">{{ $r['siswa']->nama }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-900 font-mono text-xs sm:text-sm">
                                    {{ $r['siswa']->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-bold {{ $r['hadir'] > 0 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $r['hadir'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-bold {{ $r['izin'] > 0 ? 'bg-[#003FA9] text-white' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $r['izin'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-bold {{ $r['sakit'] > 0 ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $r['sakit'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-bold {{ $r['alpa'] > 0 ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $r['alpa'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <i class='bx bx-bar-chart-alt-2 text-4xl mb-2 text-slate-300'></i>
                                    <h3 class="font-semibold text-sm text-slate-600">Belum ada data rekap siswa</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan data absensi untuk filter yang dipilih.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($rekap->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $rekap->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

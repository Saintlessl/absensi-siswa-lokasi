@extends('layouts.app')

@section('title', 'Rekap Absensi Per Kelas')
@section('page-title', 'Rekap Per Kelas')
@section('page-subtitle', 'Rekapitulasi kehadiran harian per kelas')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Rekap Per Kelas</li>
@endsection

@section('content')
    <div class="flex flex-col gap-6">

        {{-- Filter Bulan & Tahun --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <form action="{{ route('rekapkelas') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Bulan</label>
                    <select name="bulan"
                        class="block w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] text-sm transition">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                {{ $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $i, 1)->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tahun</label>
                    <select name="tahun"
                        class="block w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 focus:border-[#003FA9] text-sm transition">
                        @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit"
                    class="px-5 py-2.5 bg-[#003FA9] hover:bg-blue-700 text-white font-medium rounded-xl text-sm shadow-sm transition flex items-center gap-2 whitespace-nowrap">
                    <i class='bx bx-filter-alt text-base'></i>
                    Tampilkan
                </button>
            </form>
        </div>

        {{-- Info Bar --}}
        <div class="flex items-center gap-2 text-sm text-slate-500 px-1">
            <i class='bx bx-calendar text-[#003FA9] text-lg'></i>
            <span>Periode: <strong class="text-slate-800 font-semibold">{{ $namaBulan }}</strong></span>
            <span class="text-slate-300">|</span>
            <span>Total Baris: <strong class="text-slate-800 font-semibold">{{ $totalBaris }}</strong></span>
        </div>

        {{-- Tabel Rekap --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hari</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Kelas</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Total Siswa</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Hadir</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Izin</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Sakit</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Alpa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @php $prevTanggal = null; @endphp
                        @forelse ($rekap as $r)
                            @php
                                $isNewDate = $prevTanggal !== $r['tanggal'];
                                $prevTanggal = $r['tanggal'];
                                $carbonDate = \Carbon\Carbon::parse($r['tanggal']);
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors {{ $isNewDate ? 'border-t-2 border-slate-100' : '' }}">
                                <td class="py-4 px-4 text-slate-400 text-center text-xs">{{ $rekap->firstItem() + $loop->index }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-800">
                                    {{ $carbonDate->isoFormat('D MMMM Y') }}
                                </td>
                                <td class="py-4 px-4 text-slate-600">
                                    {{ $carbonDate->isoFormat('dddd') }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $r['kelas'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-700 text-center">{{ $r['total_siswa'] }}</td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-semibold {{ $r['hadir'] > 0 ? 'bg-emerald-600 text-white' : 'text-slate-400' }}">
                                        {{ $r['hadir'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-semibold {{ $r['izin'] > 0 ? 'bg-[#003FA9] text-white' : 'text-slate-400' }}">
                                        {{ $r['izin'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-semibold {{ $r['sakit'] > 0 ? 'bg-amber-500 text-white' : 'text-slate-400' }}">
                                        {{ $r['sakit'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-semibold {{ $r['alpa'] > 0 ? 'bg-rose-600 text-white' : 'text-slate-400' }}">
                                        {{ $r['alpa'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                            <i class='bx bx-building-house text-2xl text-slate-400'></i>
                                        </div>
                                        <p class="text-sm font-medium text-slate-600">Belum ada data</p>
                                        <p class="text-xs text-slate-400 mt-1">Tidak ada data absensi pada periode ini.</p>
                                    </div>
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

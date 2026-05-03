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
        <div class="glass-card rounded-2xl border border-dark-200/50 p-6">
            <form action="{{ route('rekapkelas') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
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
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
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
        <div class="flex items-center gap-2 text-sm text-dark-600">
            <i class='bx bx-calendar text-primary-500 text-lg'></i>
            Periode: <strong class="text-dark-800">{{ $namaBulan }}</strong>
            <span class="text-dark-400">|</span>
            Total Baris: <strong class="text-dark-800">{{ $totalBaris }}</strong>
        </div>

        {{-- Tabel Rekap --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-dark-50/50 border-b border-dark-200/50">
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Hari</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Kelas</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Total Siswa</th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center bg-emerald-50/50">
                                <span class="inline-flex items-center gap-1 text-emerald-700"><i class='bx bx-check-shield'></i> Hadir</span>
                            </th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center bg-blue-50/50">
                                <span class="inline-flex items-center gap-1 text-blue-700"><i class='bx bx-envelope'></i> Izin</span>
                            </th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center bg-amber-50/50">
                                <span class="inline-flex items-center gap-1 text-amber-700"><i class='bx bx-plus-medical'></i> Sakit</span>
                            </th>
                            <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center bg-rose-50/50">
                                <span class="inline-flex items-center gap-1 text-rose-700"><i class='bx bx-x-circle'></i> Alpa</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-200/50">
                        @php $prevTanggal = null; @endphp
                        @forelse ($rekap as $r)
                            @php
                                $isNewDate = $prevTanggal !== $r['tanggal'];
                                $prevTanggal = $r['tanggal'];
                                $carbonDate = \Carbon\Carbon::parse($r['tanggal']);
                            @endphp
                            <tr class="hover:bg-dark-50/50 transition-colors duration-200 {{ $isNewDate ? 'border-t-2 border-dark-200' : '' }}">
                                <td class="px-6 py-3.5 text-sm text-dark-600 text-center">{{ $rekap->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-3.5 text-sm font-medium text-dark-800">
                                    {{ $carbonDate->isoFormat('D MMMM Y') }}
                                </td>
                                <td class="px-6 py-3.5 text-sm text-dark-600">
                                    {{ $carbonDate->isoFormat('dddd') }}
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                        {{ $r['kelas'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-sm font-bold text-dark-700 text-center">{{ $r['total_siswa'] }}</td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-lg text-xs font-bold {{ $r['hadir'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-dark-50 text-dark-400' }}">
                                        {{ $r['hadir'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-lg text-xs font-bold {{ $r['izin'] > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-dark-50 text-dark-400' }}">
                                        {{ $r['izin'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-lg text-xs font-bold {{ $r['sakit'] > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-dark-50 text-dark-400' }}">
                                        {{ $r['sakit'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-lg text-xs font-bold {{ $r['alpa'] > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-dark-50 text-dark-400' }}">
                                        {{ $r['alpa'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 mb-4 rounded-full bg-dark-100 flex items-center justify-center">
                                            <i class='bx bx-building-house text-3xl text-dark-400'></i>
                                        </div>
                                        <h3 class="text-sm font-medium text-dark-900">Belum ada data</h3>
                                        <p class="mt-1 text-sm text-dark-500">Tidak ada data absensi pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($rekap->hasPages())
                <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                    {{ $rekap->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

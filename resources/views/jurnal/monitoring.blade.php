@extends('layouts.app')

@section('page-title', 'Monitoring Jurnal Pembelajaran')
@section('page-subtitle', 'Pantau keterlaksanaan agenda mengajar dan presensi harian seluruh guru')

@section('content')
<div class="space-y-6">

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50">
        <form method="GET" action="{{ route('jurnal.monitoring') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            {{-- Guru Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Guru Pengampu</label>
                <select name="guru_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
                    <option value="">Semua Guru</option>
                    @foreach($gurus as $g)
                        <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kelas Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Rombongan Belajar</label>
                <select name="kelas_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('kelas_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs flex items-center justify-center gap-1.5">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                <a href="{{ route('jurnal.monitoring') }}"
                    class="py-2 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Journal List Container --}}
    <div class="bg-white rounded-3xl shadow-sm border border-blue-50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Jurnal Pembelajaran Guru</h3>
            <span class="text-xs text-slate-400">Total {{ $journals->total() }} Sesi Terlaksana</span>
        </div>

        @if($journals->isEmpty())
            <div class="py-14 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-3">
                    <i class='bx bx-book-reader text-2xl'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Belum Ada Jurnal Ditemukan</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Jurnal yang diisi oleh guru sesuai jadwal pelajaran akan ditampilkan di sini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL & JAM</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">GURU PENGAMPU</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KELAS & JURUSAN</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MATA PELAJARAN</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MATERI POKOK</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KEHADIRAN SESI</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-20">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($journals as $j)
                            @php
                                $hadir = $j->attendances->where('status', 'hadir')->count();
                                $total = $j->attendances->count();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 whitespace-nowrap text-slate-900 font-bold text-xs sm:text-sm">
                                    <div>{{ $j->tanggal->translatedFormat('d M Y') }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono font-normal">{{ $j->jam_ke ?: '-' }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $j->guru->nama }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIP: {{ $j->guru->nip ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-900 font-mono text-xs sm:text-sm">
                                    {{ $j->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-800 text-xs sm:text-sm">
                                    {{ $j->nama_mapel }}
                                </td>
                                <td class="py-4 px-4 text-slate-700 max-w-xs truncate" title="{{ $j->materi_pokok }}">
                                    <span class="text-xs sm:text-sm font-medium text-slate-800">{{ $j->materi_pokok }}</span>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($total > 0)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                            <i class='bx bx-user-check text-base'></i>
                                            {{ $hadir }}/{{ $total }} Hadir
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <a href="{{ route('jurnal.show', $j->id) }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center gap-1"
                                       title="Lihat Detail Jurnal">
                                        <i class='bx bx-info-circle text-lg'></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($journals->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $journals->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

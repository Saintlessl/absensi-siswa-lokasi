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
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 uppercase text-[10px] tracking-wider font-semibold">
                            <th class="py-3 px-4">Tanggal & Jam</th>
                            <th class="py-3 px-4">Guru Pengampu</th>
                            <th class="py-3 px-4">Kelas & Jurusan</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Materi Pokok</th>
                            <th class="py-3 px-4">Kehadiran Sesi</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($journals as $j)
                            @php
                                $hadir = $j->attendances->where('status', 'hadir')->count();
                                $total = $j->attendances->count();
                            @endphp
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                                    <div class="font-bold text-slate-800">{{ $j->tanggal->translatedFormat('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $j->jam_ke ?: '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $j->guru->nama }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">NIP: {{ $j->guru->nip ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-[#0052CC]">
                                        {{ $j->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-semibold">
                                    {{ $j->nama_mapel }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 max-w-xs truncate" title="{{ $j->materi_pokok }}">
                                    {{ $j->materi_pokok }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($total > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $hadir }}/{{ $total }} Hadir
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('jurnal.show', $j->id) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-[#0052CC] hover:bg-blue-100 transition-colors">
                                        <i class='bx bx-show'></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($journals->hasPages())
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $journals->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

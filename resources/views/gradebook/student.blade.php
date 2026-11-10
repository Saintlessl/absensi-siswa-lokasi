@extends('layouts.app')

@section('page-title', 'Nilai Akademik Siswa')
@section('page-subtitle', 'Transkrip nilai tugas, kuis, praktik bengkel, dan ujian yang telah dipublikasikan guru')

@section('content')
<div class="space-y-6">

    {{-- Student Summary Banner --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#003FA9] text-white flex items-center justify-center text-3xl font-bold shadow-sm">
                    <i class='bx bx-award'></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $siswa->nama }}</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        NIS: <span class="font-mono font-bold text-slate-700">{{ $siswa->nis }}</span> |
                        Kelas: <span class="font-bold text-[#003FA9]">{{ $siswa->kelas->nama_kelas ?? '-' }}</span> |
                        Jurusan: <span class="font-semibold text-slate-700">{{ $siswa->kelas->jurusan->nama_jurusan ?? ($siswa->kelas->jurusan->kode_jurusan ?? '-') }}</span>
                    </p>
                </div>
            </div>

            {{-- GPA / Average Badge --}}
            <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-4 text-center min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rata-Rata Nilai</p>
                <div class="text-3xl font-black text-[#003FA9] font-mono mt-0.5">
                    {{ !is_null($average) ? number_format($average, 1) : '-' }}
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $scores->total() }} Asesmen Terbit</p>
            </div>
        </div>
    </div>

    {{-- Grades List Container --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        <div class="pb-4 mb-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Nilai Asesmen Terpublikasi</h3>
            <span class="text-xs text-slate-400">Total {{ $scores->total() }} Nilai</span>
        </div>

        @if($scores->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                        <i class='bx bx-book-open text-2xl text-slate-400'></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600">Belum Ada Nilai yang Dipublikasikan</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Nilai tugas dan ujian yang telah selesai dinilai dan dipublikasikan oleh guru pengampu akan muncul di sini.
                    </p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Judul Penilaian</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kategori</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Nilai Siswa</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Catatan Guru</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Guru Pengampu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($scores as $sc)
                            @php
                                $asm = $sc->assessment;
                                $val = $sc->nilai;
                                $maxVal = $asm->skor_maksimal;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 whitespace-nowrap text-slate-500 font-mono text-xs">
                                    {{ $asm->tanggal->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800">
                                    {{ $asm->nama_mapel }}
                                </td>
                                <td class="py-4 px-4 text-slate-700">
                                    <div class="font-semibold text-slate-800">{{ $asm->judul }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">Tahun: {{ $asm->tahun_ajaran }} ({{ ucfirst($asm->semester) }})</div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 uppercase">
                                        {{ $asm->category->nama ?? 'Tugas' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if(!is_null($val))
                                        <div class="inline-flex flex-col items-center">
                                            <span class="text-base font-black font-mono {{ $val >= 75 ? 'text-emerald-600' : ($val >= 60 ? 'text-amber-600' : 'text-rose-600') }}">
                                                {{ number_format($val, 1) }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono">/ {{ number_format($maxVal, 0) }}</span>
                                        </div>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-500">
                                            Belum Dinilai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-600 max-w-xs text-xs">
                                    {{ $sc->catatan ?: '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-700 font-medium">
                                    {{ $asm->guru->nama ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($scores->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $scores->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

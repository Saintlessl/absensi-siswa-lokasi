@extends('layouts.app')

@section('page-title', 'Nilai Akademik Siswa')
@section('page-subtitle', 'Transkrip nilai tugas, kuis, praktik bengkel, dan ujian yang telah dipublikasikan guru')

@section('content')
<div class="space-y-6">

    {{-- Student Summary Banner --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-blue-50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#0052CC] text-white flex items-center justify-center text-3xl font-bold shadow-md shadow-blue-500/20">
                    <i class='bx bx-award'></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $siswa->nama }}</h2>
                    <p class="text-xs text-slate-500">
                        NIS: <span class="font-mono font-bold text-slate-700">{{ $siswa->nis }}</span> |
                        Kelas: <span class="font-bold text-[#0052CC]">{{ $siswa->kelas->nama_kelas ?? '-' }}</span> |
                        Jurusan: <span class="font-semibold text-slate-700">{{ $siswa->kelas->jurusan->nama_jurusan ?? ($siswa->kelas->jurusan->kode_jurusan ?? '-') }}</span>
                    </p>
                </div>
            </div>

            {{-- GPA / Average Badge --}}
            <div class="bg-blue-50/80 border border-blue-100 rounded-2xl p-4 text-center min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rata-Rata Nilai</p>
                <div class="text-3xl font-black text-[#0052CC] font-mono mt-0.5">
                    {{ !is_null($average) ? number_format($average, 1) : '-' }}
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $scores->total() }} Asesmen Terbit</p>
            </div>
        </div>
    </div>

    {{-- Grades List Container --}}
    <div class="bg-white rounded-3xl shadow-sm border border-blue-50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Nilai Asesmen Terpublikasi</h3>
            <span class="text-xs text-slate-400">Total {{ $scores->total() }} Nilai</span>
        </div>

        @if($scores->isEmpty())
            <div class="py-14 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class='bx bx-book-open'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Belum Ada Nilai yang Dipublikasikan</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Nilai tugas dan ujian yang telah selesai dinilai dan dipublikasikan oleh guru pengampu akan muncul di sini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 uppercase text-[10px] tracking-wider font-semibold">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Judul Penilaian</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-center">Nilai Siswa</th>
                            <th class="py-3 px-4">Catatan Guru</th>
                            <th class="py-3 px-4">Guru Pengampu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($scores as $sc)
                            @php
                                $asm = $sc->assessment;
                                $val = $sc->nilai;
                                $maxVal = $asm->skor_maksimal;
                                $pct = !is_null($val) && $maxVal > 0 ? ($val / $maxVal) * 100 : null;
                            @endphp
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap text-slate-500 font-mono">
                                    {{ $asm->tanggal->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $asm->nama_mapel }}
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    <div class="font-semibold text-slate-900">{{ $asm->judul }}</div>
                                    <div class="text-[10px] text-slate-400">Tahun: {{ $asm->tahun_ajaran }} ({{ ucfirst($asm->semester) }})</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-purple-50 text-purple-700 border border-purple-100">
                                        {{ $asm->category->nama ?? 'Tugas' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if(!is_null($val))
                                        <div class="inline-flex flex-col items-center">
                                            <span class="text-base font-black font-mono {{ $val >= 75 ? 'text-emerald-600' : ($val >= 60 ? 'text-amber-600' : 'text-rose-600') }}">
                                                {{ number_format($val, 1) }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono">/ {{ number_format($maxVal, 0) }}</span>
                                        </div>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-500">
                                            Belum Dinilai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs">
                                    {{ $sc->catatan ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-semibold">
                                    {{ $asm->guru->nama ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($scores->hasPages())
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $scores->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

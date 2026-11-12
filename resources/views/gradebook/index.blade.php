@extends('layouts.app')

@section('page-title', 'Buku Nilai Akademik')
@section('page-subtitle', 'Kelola komponen penilaian, kuis, praktik bengkel, dan ujian siswa per rombel')

@section('content')
<div class="space-y-6">

    {{-- Top Action & Filter Bar --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Daftar Komponen Asesmen & Ujian</h3>
            <p class="text-xs text-slate-400 mt-0.5">Kelola input nilai per tugas, praktik, kuis, dan ujian akhir semester</p>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0">
            <button type="button" onclick="openModal('modal-addassessment')"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#003FA9] text-white text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                <i class='bx bx-plus text-base'></i>
                <span>Buat Asesmen Baru</span>
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <form method="GET" action="{{ route('gradebook.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            {{-- Kelas Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Rombel / Kelas</label>
                <select name="kelas_id" class="w-full text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 px-3.5 py-2 transition">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('kelas_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kategori Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kategori Penilaian</label>
                <select name="category_id" class="w-full text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 px-3.5 py-2 transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nama }} ({{ $cat->kode }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status Publikasi</label>
                <select name="status" class="w-full text-sm rounded-xl border border-slate-200 focus:border-[#003FA9] focus:ring-2 focus:ring-[#003FA9]/20 bg-slate-50/50 focus:bg-white text-slate-800 px-3.5 py-2 transition">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Finalized (Belum Rilis)</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published (Sudah Terbit)</option>
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3.5 rounded-xl bg-[#003FA9] text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm flex items-center justify-center gap-1.5 h-[38px]">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                <a href="{{ route('gradebook.index') }}"
                    class="py-2 px-3.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition h-[38px] flex items-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Assessments Table Container --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 overflow-hidden">
        @if($assessments->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                        <i class='bx bx-edit text-2xl text-slate-400'></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600">Belum Ada Komponen Penilaian</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Buat asesmen tugas atau ujian baru untuk mulai menginput nilai siswa per rombel.
                    </p>
                    <div class="mt-4">
                        <button type="button" onclick="openModal('modal-addassessment')"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#003FA9] text-white text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                            <i class='bx bx-plus'></i> Buat Asesmen Pertama
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kelas</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Judul Asesmen</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kategori</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Progres Dinilai</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($assessments as $asm)
                            @php
                                $totalSiswa = $asm->scores->count();
                                $gradedSiswa = $asm->scores->whereNotNull('nilai')->count();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 whitespace-nowrap text-slate-500 font-mono text-xs">
                                    {{ $asm->tanggal->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800">
                                    {{ $asm->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700">
                                    {{ $asm->nama_mapel }}
                                </td>
                                <td class="py-4 px-4 max-w-xs">
                                    <div class="font-bold text-slate-800 truncate" title="{{ $asm->judul }}">{{ $asm->judul }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">Skor Maks: {{ number_format($asm->skor_maksimal, 0) }} | Bobot: {{ $asm->bobot }}x</div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 uppercase">
                                        {{ $asm->category->nama ?? 'Tugas' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-semibold text-xs {{ $gradedSiswa === $totalSiswa && $totalSiswa > 0 ? 'text-emerald-600' : 'text-slate-600' }}">
                                        {{ $gradedSiswa }}/{{ $totalSiswa }} Siswa
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    @if($asm->status === 'published')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">Dipublikasikan</span>
                                    @elseif($asm->status === 'finalized')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">Final (Belum Terbit)</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">Draft</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-1">
                                        <a href="{{ route('gradebook.input', $asm->id) }}"
                                           class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center gap-1 text-xs font-semibold"
                                           title="Input Nilai">
                                            <i class='bx bx-edit text-base'></i> Nilai
                                        </a>

                                        @if($asm->status !== 'published')
                                            <form action="{{ route('gradebook.publish', $asm->id) }}" method="POST" class="inline"
                                                data-confirm="Publikasikan nilai ke siswa dan orang tua?"
                                                data-confirm-title="Publikasi Nilai">
                                                @csrf
                                                <button type="submit"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition"
                                                    title="Publikasikan">
                                                    <i class='bx bx-upload text-base'></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('gradebook.destroy', $asm->id) }}" method="POST" class="inline"
                                            data-confirm="Hapus asesmen ini beserta nilai seluruh siswa?"
                                            data-confirm-title="Hapus Asesmen"
                                            data-confirm-danger="true">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="delete-confirm p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="Hapus">
                                                <i class='bx bx-trash text-base'></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($assessments->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $assessments->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

@endsection

@push('modals')
    @include('gradebook.modal-wizard')
@endpush

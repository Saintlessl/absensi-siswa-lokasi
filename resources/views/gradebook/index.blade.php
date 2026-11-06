@extends('layouts.app')

@section('page-title', 'Buku Nilai Akademik')
@section('page-subtitle', 'Kelola komponen penilaian, kuis, praktik bengkel, dan ujian siswa per rombel')

@section('content')
<div class="space-y-6">

    {{-- Top Action & Filter Bar --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Daftar Komponen Asesmen & Ujian</h3>
            <p class="text-xs text-slate-400 mt-0.5">Kelola input nilai per tugas, praktik, kuis, dan ujian akhir semester</p>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0">
            <button type="button" onclick="openModal('modal-addassessment')"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs">
                <i class='bx bx-plus text-base'></i>
                <span>Buat Asesmen Baru</span>
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-blue-50">
        <form method="GET" action="{{ route('gradebook.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            {{-- Kelas Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Rombel / Kelas</label>
                <select name="kelas_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
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
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Penilaian</label>
                <select name="category_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
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
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Publikasi</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Finalized (Belum Rilis)</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published (Sudah Terbit)</option>
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs flex items-center justify-center gap-1.5">
                    <i class='bx bx-filter-alt'></i> Filter
                </button>
                <a href="{{ route('gradebook.index') }}"
                    class="py-2 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Assessments Table Container --}}
    <div class="bg-white rounded-3xl shadow-sm border border-blue-50 overflow-hidden">
        @if($assessments->isEmpty())
            <div class="py-14 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class='bx bx-edit'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Belum Ada Komponen Penilaian</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Buat asesmen tugas atau ujian baru untuk mulai menginput nilai siswa per rombel.
                </p>
                <div class="mt-4">
                    <button type="button" onclick="openModal('modal-addassessment')"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs">
                        <i class='bx bx-plus'></i> Buat Asesmen Pertama
                    </button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 uppercase text-[10px] tracking-wider font-semibold">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Judul Asesmen</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Progres Dinilai</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($assessments as $asm)
                            @php
                                $totalSiswa = $asm->scores->count();
                                $gradedSiswa = $asm->scores->whereNotNull('nilai')->count();
                                $statusBadge = match($asm->status) {
                                    'published' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Dipublikasikan'],
                                    'finalized' => ['bg' => 'bg-blue-50 text-[#0052CC] border-blue-200', 'label' => 'Final (Belum Terbit)'],
                                    default     => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Draft'],
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap text-slate-500 font-mono">
                                    {{ $asm->tanggal->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $asm->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    {{ $asm->nama_mapel }}
                                </td>
                                <td class="py-3 px-4 max-w-xs">
                                    <div class="font-bold text-slate-900 truncate" title="{{ $asm->judul }}">{{ $asm->judul }}</div>
                                    <div class="text-[10px] text-slate-400">Skor Maks: {{ number_format($asm->skor_maksimal, 0) }} | Bobot: {{ $asm->bobot }}x</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-purple-50 text-purple-700 border border-purple-100">
                                        {{ $asm->category->nama ?? 'Tugas' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-semibold {{ $gradedSiswa === $totalSiswa && $totalSiswa > 0 ? 'text-emerald-600' : 'text-slate-600' }}">
                                        {{ $gradedSiswa }}/{{ $totalSiswa }} Siswa
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['bg'] }}">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('gradebook.input', $asm->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-[#0052CC] hover:bg-blue-100 transition-colors shadow-xs">
                                            <i class='bx bx-edit-alt'></i> Input Nilai
                                        </a>

                                        @if($asm->status !== 'published')
                                            <form action="{{ route('gradebook.publish', $asm->id) }}" method="POST" class="inline" onsubmit="return confirm('Publikasikan nilai ke siswa dan orang tua?')">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors"
                                                    title="Publikasikan">
                                                    <i class='bx bx-upload'></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('gradebook.destroy', $asm->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus asesmen ini beserta nilai seluruh siswa?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                title="Hapus">
                                                <i class='bx bx-trash text-sm'></i>
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
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
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

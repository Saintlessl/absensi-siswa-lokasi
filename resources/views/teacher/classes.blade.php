@extends('layouts.app')

@section('title', 'Kelas yang Diajar')
@section('page-title', 'Kelas yang Diajar')
@section('page-subtitle', 'Daftar rombongan belajar dan mata pelajaran yang Anda ampu')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><span class="text-dark-500">Pembelajaran</span></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Kelas yang Diajar</li>
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Daftar Rombongan Belajar Anda</h2>
            <p class="text-xs text-dark-400 mt-0.5">Kelola informasi peserta didik pada rombel yang berada di bawah penugasan mengajar Anda.</p>
        </div>
        <a href="{{ route('teacher.schedule') }}"
            class="inline-flex items-center gap-2 px-3.5 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-xs font-semibold rounded-xl shadow-sm transition">
            <i class='bx bx-calendar text-base text-primary-600'></i>
            <span>Lihat Jadwal Mengajar</span>
        </a>
    </div>

    {{-- Classes Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($classes as $c)
            <div class="glass-card rounded-2xl border border-slate-100 bg-white p-5 hover-lift shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-primary-50 text-primary-700 border border-primary-200">
                            {{ $c->jurusan->singkatan ?? $c->jurusan->nama_jurusan ?? 'Kejuruan' }}
                        </span>
                        <span class="text-xs font-semibold text-dark-400 flex items-center gap-1">
                            <i class='bx bx-user text-sm'></i> {{ $c->siswa_count }} Siswa
                        </span>
                    </div>

                    <h3 class="text-xl font-black text-dark-900 mb-1">{{ $c->nama_kelas }}</h3>
                    <p class="text-xs text-dark-500 mb-3">{{ $c->jurusan->nama_jurusan ?? '' }} &bull; Tingkat {{ $c->tingkat }}</p>

                    <div class="border-t border-dark-100/70 pt-3 mb-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400 mb-2">Mata Pelajaran yang Anda Ampu:</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($c->my_subjects as $sName)
                                <span class="px-2.5 py-1 bg-[#EFF5FF] text-[#003EA7] rounded-lg text-xs font-semibold flex items-center gap-1">
                                    <i class='bx bx-book-bookmark text-xs'></i> {{ $sName }}
                                </span>
                            @empty
                                <span class="text-xs text-dark-400 italic">Sesuai penugasan jadwal</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-dark-100/70 flex items-center justify-between">
                    <span class="text-[11px] text-dark-400">
                        Wali: <strong>{{ $c->wali_kelas ?? 'Belum ditentukan' }}</strong>
                    </span>
                    <a href="{{ route('teacher.class.students', $c->id) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold transition shadow-sm">
                        <span>Lihat Siswa</span>
                        <i class='bx bx-right-arrow-alt text-base'></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-dark-400 glass-card rounded-2xl border border-slate-100 bg-white">
                <i class='bx bx-chalkboard text-4xl mb-2 text-dark-300'></i>
                <p class="text-sm font-semibold text-dark-600">Belum ada kelas yang ditugaskan kepada Anda.</p>
                <p class="text-xs text-dark-400 mt-1">Hubungi Administrator Kurikulum untuk menambahkan penugasan mata pelajaran atau jadwal.</p>
            </div>
        @endforelse
    </div>
@endsection

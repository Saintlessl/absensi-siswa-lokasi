@extends('layouts.app')

@section('title', 'Struktur Akademik')
@section('page-title', 'Struktur Akademik')
@section('page-subtitle', 'Hierarki terpadu Jurusan, Tingkat Kelas, Rombel, serta Riwayat Angkatan Alumni')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Struktur Akademik</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Struktur Akademik & Program Keahlian</h2>
            <p class="text-sm text-dark-400 mt-1">
                Pilih Jurusan untuk menelusuri Tingkat Kelas (10, 11, 12), Rombongan Belajar, atau telusuri arsip Angkatan Alumni.
            </p>
        </div>
        @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
            <div class="flex items-center gap-2">
                <a href="{{ route('angkatan.index') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-dark-200 rounded-xl text-dark-700 bg-white hover:bg-dark-50 text-xs font-semibold shadow-sm transition-all">
                    <i class='bx bxs-graduation text-base text-primary-600'></i>
                    <span>Master Angkatan</span>
                </a>
                <a href="{{ route('jurusan') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-dark-200 rounded-xl text-dark-700 bg-white hover:bg-dark-50 text-xs font-semibold shadow-sm transition-all">
                    <i class='bx bx-cog text-base'></i>
                    <span>Master Jurusan</span>
                </a>
                <a href="{{ route('kelas') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                    <i class='bx bx-plus-circle text-base'></i>
                    <span>Tambah Rombel</span>
                </a>
            </div>
        @endif
    </div>
@endsection

@section('content')
    {{-- Search Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 mb-6">
        <form action="{{ route('academic.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-search text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                    placeholder="Cari kode atau nama jurusan...">
            </div>
            <button type="submit"
                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class='bx bx-filter-alt'></i> Cari
            </button>
            @if ($search)
                <a href="{{ route('academic.index') }}"
                    class="px-4 py-2.5 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- SECTION 1: PROGRAM KEAHLIAN / JURUSAN --}}
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-dark-800">Program Keahlian (Jurusan)</h3>
            <p class="text-xs text-dark-400 mt-0.5">Pilih program keahlian untuk menelusuri tingkatan rombel dan siswa aktif.</p>
        </div>
        <span class="text-xs font-semibold text-dark-500">{{ $jurusans->count() }} Program Keahlian</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        @forelse ($jurusans as $jurusan)
            @php
                $palette = match(strtoupper($jurusan->kode_jurusan)) {
                    'PPLG' => ['bg' => 'bg-indigo-50/70', 'border' => 'border-indigo-200', 'badge' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'accent' => 'text-indigo-600'],
                    'ANIMASI' => ['bg' => 'bg-purple-50/70', 'border' => 'border-purple-200', 'badge' => 'bg-purple-100 text-purple-700 border-purple-200', 'accent' => 'text-purple-600'],
                    'TPFL' => ['bg' => 'bg-amber-50/70', 'border' => 'border-amber-200', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200', 'accent' => 'text-amber-600'],
                    'TO' => ['bg' => 'bg-rose-50/70', 'border' => 'border-rose-200', 'badge' => 'bg-rose-100 text-rose-700 border-rose-200', 'accent' => 'text-rose-600'],
                    'BCF' => ['bg' => 'bg-emerald-50/70', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'accent' => 'text-emerald-600'],
                    default => ['bg' => 'bg-blue-50/70', 'border' => 'border-blue-200', 'badge' => 'bg-blue-100 text-blue-700 border-blue-200', 'accent' => 'text-blue-600'],
                };
            @endphp
            <div class="glass-card rounded-2xl border border-dark-200/50 bg-white shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col overflow-hidden group hover:-translate-y-1">
                {{-- Card Header --}}
                <div class="p-6 border-b border-dark-100 {{ $palette['bg'] }} transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-dark-200/80 shadow-sm flex items-center justify-center overflow-hidden flex-shrink-0 group-hover:scale-105 transition-transform">
                            @if ($jurusan->logo_url)
                                <img src="{{ $jurusan->logo_url }}" alt="{{ $jurusan->kode_jurusan }}" class="w-full h-full object-cover">
                            @else
                                <span class="font-extrabold text-lg {{ $palette['accent'] }}">
                                    {{ substr($jurusan->kode_jurusan, 0, 3) }}
                                </span>
                            @endif
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold border font-mono {{ $palette['badge'] }}">
                            {{ $jurusan->kode_jurusan }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-dark-900 mt-4 leading-tight group-hover:text-primary-600 transition-colors">
                        {{ $jurusan->nama_jurusan }}
                    </h3>
                    <p class="text-xs text-dark-500 mt-1 line-clamp-2" title="{{ $jurusan->deskripsi }}">
                        {{ $jurusan->deskripsi ?: 'Program keahlian terintegrasi kurikulum merdeka SMK.' }}
                    </p>
                </div>

                {{-- Metrics Counter --}}
                <div class="px-6 py-4 grid grid-cols-3 gap-2 border-b border-dark-100 bg-dark-50/30 text-center">
                    <div>
                        <span class="block text-lg font-bold text-dark-800">{{ $jurusan->tingkat_count }}</span>
                        <span class="block text-[11px] text-dark-400 font-medium uppercase tracking-wider">Tingkat</span>
                    </div>
                    <div class="border-x border-dark-200/50">
                        <span class="block text-lg font-bold text-dark-800">{{ $jurusan->kelas_count }}</span>
                        <span class="block text-[11px] text-dark-400 font-medium uppercase tracking-wider">Rombel</span>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-dark-800">{{ $jurusan->siswa_count }}</span>
                        <span class="block text-[11px] text-dark-400 font-medium uppercase tracking-wider">Siswa</span>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="p-4 bg-white mt-auto">
                    <a href="{{ route('academic.major', $jurusan) }}"
                        class="w-full py-2.5 px-4 rounded-xl border border-dark-200 hover:border-primary-500 hover:bg-primary-50 text-dark-700 hover:text-primary-700 font-semibold text-xs transition-all flex items-center justify-center gap-1.5 group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 shadow-sm">
                        <span>Pilih Jurusan Ini</span>
                        <i class='bx bx-right-arrow-alt text-base transition-transform group-hover:translate-x-1'></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center glass-card rounded-2xl border border-dark-200/50 bg-white">
                <i class='bx bx-layer text-5xl text-dark-300 mb-3'></i>
                <h4 class="text-base font-bold text-dark-800">Belum Ada Program Keahlian</h4>
                <p class="text-xs text-dark-400 mt-1 max-w-sm mx-auto">
                    Data jurusan belum tersedia atau belum diaktifkan. Silakan tambahkan data jurusan pada menu Master Data.
                </p>
            </div>
        @endforelse
    </div>

    {{-- SECTION 2: ANGKATAN AKTIF SEKOLAH --}}
    <div class="mb-4 flex items-center justify-between pt-2 border-t border-dark-200/50">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                <h3 class="text-base font-bold text-dark-800 tracking-tight">ANGKATAN AKTIF (TINGKAT SEKOLAH)</h3>
            </div>
            <p class="text-xs text-dark-400 mt-0.5">Distribusi siswa aktif pada tingkat 10, 11, dan 12 di seluruh program keahlian.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        @foreach ($activeCohortsSummary as $act)
            @php
                $style = match($act->tingkat) {
                    10 => ['icon' => 'bx-compass', 'color' => 'from-blue-500 to-indigo-600', 'fase' => 'Fase E (Kelas 10)'],
                    11 => ['icon' => 'bx-layer', 'color' => 'from-indigo-500 to-purple-600', 'fase' => 'Fase F (Kelas 11)'],
                    12 => ['icon' => 'bx-award', 'color' => 'from-purple-500 to-pink-600', 'fase' => 'Fase F Akhir (Kelas 12)'],
                    default => ['icon' => 'bx-book', 'color' => 'from-gray-500 to-gray-700', 'fase' => 'Fase Akademik'],
                };
            @endphp
            <div class="glass-card rounded-2xl border border-dark-200/50 bg-white shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $style['color'] }} text-white flex items-center justify-center text-xl shadow-xs">
                            <i class='bx {{ $style['icon'] }}'></i>
                        </div>
                        @if ($act->angkatan)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#003EA7] border border-blue-200 font-mono">
                                {{ $act->angkatan->nama_angkatan }}
                            </span>
                        @endif
                    </div>
                    <h4 class="text-xl font-bold text-dark-900">{{ $act->nama_tingkat }}</h4>
                    <p class="text-xs text-dark-400 mt-0.5">{{ $style['fase'] }} • T.A. {{ $act->tahun_ajaran }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-dark-100 text-center">
                    <div class="p-2 rounded-xl bg-dark-50/50">
                        <span class="block text-lg font-bold text-dark-800">{{ $act->rombel_count }}</span>
                        <span class="block text-[10px] text-dark-400 uppercase font-medium">Rombel</span>
                    </div>
                    <a href="{{ route('siswa', array_filter(['tingkat' => $act->tingkat, 'angkatan_id' => $act->angkatan?->id, 'status' => 'aktif'])) }}"
                       class="p-2 rounded-xl bg-blue-50/50 hover:bg-blue-100/60 transition-all block group/act"
                       title="Lihat siswa aktif di Data Siswa">
                        <span class="block text-lg font-bold text-blue-700 group-hover/act:scale-105 transition-transform">{{ $act->siswa_aktif }}</span>
                        <span class="block text-[10px] text-blue-600 uppercase font-medium flex items-center justify-center gap-0.5">
                            Siswa Aktif <i class='bx bx-link-external text-[9px]'></i>
                        </span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- SECTION 3: ANGKATAN ALUMNI / KELULUSAN TINGKAT SEKOLAH --}}
    <div class="mb-4 flex items-center justify-between pt-2 border-t border-dark-200/50">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-base font-bold text-dark-800 tracking-tight">ANGKATAN ALUMNI / LULUS</h3>
            </div>
            <p class="text-xs text-dark-400 mt-0.5">Arsip seluruh kohort angkatan yang telah menyelesaikan studi di SMK Negeri.</p>
        </div>
        <span class="text-xs font-semibold px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full flex items-center gap-1.5">
            <i class='bx bx-check-double text-sm'></i> Arsip Kelulusan
        </span>
    </div>

    @if ($alumniCohorts->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach ($alumniCohorts as $cohort)
                @php
                    $st = $cohort->computed_stats ?? [
                        'total_siswa'  => 0,
                        'siswa_lulus'  => 0,
                        'rombel_count' => 1,
                    ];
                @endphp
                <div class="glass-card rounded-2xl border border-dark-200/60 bg-white shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden group hover:-translate-y-1">
                    {{-- Card Header --}}
                    <div class="p-6 border-b border-dark-100 bg-gradient-to-br from-emerald-50/40 to-teal-50/20">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-12 h-12 rounded-xl bg-white border border-emerald-200/80 text-emerald-700 flex items-center justify-center text-2xl shadow-xs group-hover:scale-105 transition-transform">
                                <i class='bx bxs-graduation'></i>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100/70 text-emerald-800 border border-emerald-200">
                                <i class='bx bx-check text-xs'></i>
                                Alumni Lulus
                            </span>
                        </div>

                        <h4 class="text-xl font-extrabold text-dark-900 group-hover:text-emerald-700 transition-colors">
                            {{ $cohort->nama_angkatan }}
                        </h4>
                        <div class="flex items-center gap-2 mt-1 text-xs text-dark-500">
                            <span class="font-mono font-semibold px-1.5 py-0.5 rounded bg-dark-100 text-dark-700">{{ $cohort->kode_angkatan }}</span>
                            <span>&bull;</span>
                            <span>T.A. Kelulusan {{ $cohort->tahun_ajaran_kelulusan }}</span>
                        </div>
                    </div>

                    {{-- Metrics Box --}}
                    <div class="p-5 grid grid-cols-2 gap-3 border-b border-dark-100 bg-dark-50/30 text-center">
                        <div class="p-3 rounded-xl bg-white border border-dark-200/50 shadow-xs">
                            <span class="block text-xl font-black text-dark-900">{{ $st['rombel_count'] }}</span>
                            <span class="block text-[11px] text-dark-400 font-medium uppercase mt-0.5">Rombel</span>
                        </div>
                        <a href="{{ route('siswa', ['angkatan_id' => $cohort->id, 'status' => 'lulus']) }}"
                           class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-200/60 shadow-xs hover:bg-emerald-100/60 transition-all block group/alumni"
                           title="Buka siswa lulus di Data Siswa">
                            <span class="block text-xl font-black text-emerald-700 group-hover/alumni:scale-105 transition-transform">{{ $st['siswa_lulus'] }}</span>
                            <span class="block text-[11px] text-emerald-600 font-semibold uppercase mt-0.5 flex items-center justify-center gap-1">
                                Siswa Lulus <i class='bx bx-link-external text-[10px]'></i>
                            </span>
                        </a>
                    </div>

                    {{-- Card Footer Link --}}
                    <div class="p-4 bg-white mt-auto">
                        <a href="{{ route('academic.alumni.show', $cohort->id) }}"
                            class="w-full py-2.5 px-4 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-800 font-semibold text-xs transition-all flex items-center justify-center gap-2 shadow-xs group-hover:border-emerald-600">
                            <span>Buka Direktori Alumni {{ $cohort->nomor_angkatan ? 'Angkatan ' . $cohort->nomor_angkatan : '' }}</span>
                            <i class='bx bx-right-arrow-alt text-base transition-transform group-hover:translate-x-1'></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="glass-card rounded-2xl border border-dark-200/50 bg-white p-12 text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-3xl">
                <i class='bx bx-award'></i>
            </div>
            <h4 class="text-base font-bold text-dark-800">Belum Ada Angkatan Alumni</h4>
            <p class="text-xs text-dark-400 mt-1 max-w-sm mx-auto">
                Belum ada kohort angkatan yang berstatus selesai atau menyelesaikan proses kelulusan.
            </p>
        </div>
    @endif
@endsection

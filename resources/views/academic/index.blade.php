@extends('layouts.app')

@section('title', 'Struktur Akademik')
@section('page-title', 'Struktur Akademik')
@section('page-subtitle', 'Hierarki terpadu Jurusan, Tingkat Kelas, Rombel, dan Siswa')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Struktur Akademik</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dark-800">Struktur Akademik & Program Keahlian</h2>
            <p class="text-sm text-dark-400 mt-1">
                Pilih Jurusan untuk menelusuri Tingkat Kelas (10, 11, 12), Rombongan Belajar, dan penempatan Siswa.
            </p>
        </div>
        @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
            <div class="flex items-center gap-2">
                <a href="{{ route('jurusan') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-dark-200 rounded-xl text-dark-700 bg-white hover:bg-dark-50 text-xs font-semibold shadow-sm transition-all">
                    <i class='bx bx-cog text-base'></i>
                    <span>Kelola Master Jurusan</span>
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

    {{-- Cards Grid (Level 1: Jurusan) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
@endsection

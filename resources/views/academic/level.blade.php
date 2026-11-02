@extends('layouts.app')

@section('title', 'Kelas ' . $tingkat . ' ' . $jurusan->kode_jurusan . ' — Rombel')
@section('page-title', 'Rombel Kelas ' . $tingkat . ' ' . $jurusan->kode_jurusan)
@section('page-subtitle', 'Pilih rombongan belajar untuk melihat data siswa terdaftar')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600 transition-colors">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.major', $jurusan) }}" class="hover:text-primary-600 transition-colors font-mono font-bold">{{ $jurusan->kode_jurusan }}</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium font-bold">Kelas {{ $tingkat }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border border-primary-200 bg-primary-50 text-primary-700 font-mono">
                    {{ $jurusan->kode_jurusan }}
                </span>
                <span class="text-xs text-dark-400">&bull; Tingkat {{ $tingkat }}</span>
            </div>
            <h2 class="text-xl font-bold text-dark-800">Daftar Rombongan Belajar (Rombel)</h2>
            <p class="text-sm text-dark-400">Pilih salah satu rombel untuk membuka direktori siswa, histori penempatan, dan profil.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('academic.major', $jurusan) }}"
                class="px-3.5 py-2 border border-dark-200 rounded-xl text-dark-600 hover:bg-dark-50 text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                <i class='bx bx-arrow-back'></i> Kembali ke Tingkat
            </a>
            @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                <a href="{{ route('kelas') }}"
                    class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                    <i class='bx bx-plus-circle'></i> Tambah Rombel
                </a>
            @endif
        </div>
    </div>
@endsection

@section('content')
    {{-- Search Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 mb-6">
        <form action="{{ route('academic.level', ['jurusan' => $jurusan->id, 'tingkat' => $tingkat]) }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-search text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                    placeholder="Cari nama rombel atau nama wali kelas...">
            </div>
            <button type="submit"
                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class='bx bx-filter-alt'></i> Cari
            </button>
            @if ($search)
                <a href="{{ route('academic.level', ['jurusan' => $jurusan->id, 'tingkat' => $tingkat]) }}"
                    class="px-4 py-2.5 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Cards Grid (Level 3: Rombel) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($kelas as $k)
            <div class="glass-card rounded-2xl border border-dark-200/50 bg-white shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col overflow-hidden group hover:-translate-y-1">
                {{-- Header --}}
                <div class="p-6 border-b border-dark-100 bg-dark-50/20">
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-10 h-10 rounded-xl bg-primary-600 text-white font-black flex items-center justify-center font-mono text-base shadow-sm">
                            {{ $k->nomor_rombel ?: substr($k->nama_kelas, -1) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-mono font-bold bg-dark-100 text-dark-700 border border-dark-200">
                            TA {{ $k->tahun_ajaran ?: '2026/2027' }}
                        </span>
                    </div>

                    <h3 class="text-xl font-black text-dark-900 font-mono tracking-tight group-hover:text-primary-600 transition-colors">
                        {{ $k->nama_kelas }}
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs text-dark-500 mt-1">
                        <i class='bx bx-user-voice text-primary-600 text-sm'></i>
                        <span class="truncate">Wali: <strong class="text-dark-700 font-semibold">{{ $k->wali_kelas }}</strong></span>
                    </div>
                </div>

                {{-- Statistics / Detail --}}
                <div class="px-6 py-4 border-b border-dark-100 bg-white flex items-center justify-between text-xs">
                    <div>
                        <span class="text-dark-400 block text-[11px] uppercase font-semibold">Siswa Terdaftar</span>
                        <span class="text-base font-extrabold text-dark-800">{{ $k->siswa_count }} Siswa</span>
                    </div>
                    <div class="text-right">
                        <span class="text-dark-400 block text-[11px] uppercase font-semibold">Kapasitas Maks</span>
                        <span class="text-sm font-semibold text-dark-600">{{ $k->jumlah_siswa }} Kursi</span>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="p-4 bg-white mt-auto">
                    <a href="{{ route('academic.class', $k) }}"
                        class="w-full py-2.5 px-4 rounded-xl border border-dark-200 hover:border-primary-500 hover:bg-primary-50 text-dark-700 hover:text-primary-700 font-semibold text-xs transition-all flex items-center justify-center gap-2 group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 shadow-sm">
                        <span>Buka Direktori Siswa</span>
                        <i class='bx bx-right-arrow-alt text-base transition-transform group-hover:translate-x-1'></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center glass-card rounded-2xl border border-dark-200/50 bg-white">
                <i class='bx bx-home-alt text-5xl text-dark-300 mb-3'></i>
                <h4 class="text-base font-bold text-dark-800">Belum Ada Rombel untuk Kelas {{ $tingkat }} {{ $jurusan->kode_jurusan }}</h4>
                <p class="text-xs text-dark-400 mt-1 max-w-sm mx-auto">
                    Silakan tambahkan data rombongan belajar baru melalui menu Data Rombel.
                </p>
            </div>
        @endforelse
    </div>
@endsection

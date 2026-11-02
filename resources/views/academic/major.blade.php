@extends('layouts.app')

@section('title', $jurusan->nama_jurusan . ' — Struktur Akademik')
@section('page-title', $jurusan->nama_jurusan)
@section('page-subtitle', 'Pilih tingkat kelas untuk menelusuri rombongan belajar')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600 transition-colors">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium font-mono font-bold">{{ $jurusan->kode_jurusan }}</li>
@endsection

@section('content')
    {{-- Jurusan Info Banner --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-6 mb-8 bg-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-primary-50 border border-primary-200 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-sm">
                @if ($jurusan->logo_url)
                    <img src="{{ $jurusan->logo_url }}" alt="{{ $jurusan->kode_jurusan }}" class="w-full h-full object-cover">
                @else
                    <span class="font-extrabold text-xl text-primary-700 font-mono">{{ substr($jurusan->kode_jurusan, 0, 3) }}</span>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border border-primary-200 bg-primary-50 text-primary-700 font-mono">
                        {{ $jurusan->kode_jurusan }}
                    </span>
                    <span class="text-xs text-dark-400">Program Keahlian SMK</span>
                </div>
                <h2 class="text-xl font-extrabold text-dark-900 mt-1">{{ $jurusan->nama_jurusan }}</h2>
                <p class="text-xs text-dark-500 mt-0.5 max-w-xl">{{ $jurusan->deskripsi ?: 'Program Keahlian Terintegrasi.' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('academic.index') }}"
                class="px-4 py-2 border border-dark-200 rounded-xl text-dark-600 hover:bg-dark-50 text-xs font-semibold transition-colors flex items-center gap-1.5 justify-center w-full sm:w-auto">
                <i class='bx bx-arrow-back'></i> Kembali ke Semua Jurusan
            </a>
        </div>
    </div>

    {{-- Cards Grid (Level 2: Tingkat Kelas) --}}
    <div class="mb-4">
        <h3 class="text-base font-bold text-dark-800">Tingkat Kelas</h3>
        <p class="text-xs text-dark-400 mt-0.5">Pilih tingkat kelas untuk melihat daftar rombongan belajar aktif.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($levelsData as $lvl)
            @php
                $levelStyles = match($lvl->tingkat) {
                    10 => ['icon' => 'bx-compass', 'color' => 'from-blue-500 to-indigo-600', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200', 'fase' => 'Fase E (Tingkat Awal)'],
                    11 => ['icon' => 'bx-layer', 'color' => 'from-indigo-500 to-purple-600', 'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'fase' => 'Fase F (Tingkat Lanjutan)'],
                    12 => ['icon' => 'bx-award', 'color' => 'from-purple-500 to-pink-600', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200', 'fase' => 'Fase F Akhir (Pra-Kelulusan)'],
                    default => ['icon' => 'bx-book', 'color' => 'from-gray-500 to-gray-700', 'badge' => 'bg-gray-50 text-gray-700 border-gray-200', 'fase' => 'Fase Akademik'],
                };
            @endphp
            <div class="glass-card rounded-2xl border border-dark-200/50 bg-white shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden group hover:-translate-y-1">
                {{-- Header with gradient accent --}}
                <div class="p-6 border-b border-dark-100 relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $levelStyles['color'] }} text-white flex items-center justify-center text-2xl shadow-md group-hover:scale-110 transition-transform">
                            <i class='bx {{ $levelStyles['icon'] }}'></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $levelStyles['badge'] }}">
                            {{ $levelStyles['fase'] }}
                        </span>
                    </div>

                    <h3 class="text-2xl font-black text-dark-900 leading-tight">
                        Kelas {{ $lvl->tingkat }}
                    </h3>
                    <p class="text-xs text-dark-400 mt-1">
                        {{ $jurusan->kode_jurusan }} &bull; Angkatan Tingkat {{ $lvl->tingkat }}
                    </p>
                </div>

                {{-- Statistics --}}
                <div class="p-6 grid grid-cols-2 gap-4 border-b border-dark-100 bg-dark-50/20 text-center">
                    <div class="p-3 rounded-xl bg-white border border-dark-200/60 shadow-sm">
                        <span class="block text-2xl font-extrabold text-dark-900">{{ $lvl->rombel_count }}</span>
                        <span class="block text-xs text-dark-500 font-medium mt-0.5">Rombel Belajar</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white border border-dark-200/60 shadow-sm">
                        <span class="block text-2xl font-extrabold text-dark-900">{{ $lvl->student_count }}</span>
                        <span class="block text-xs text-dark-500 font-medium mt-0.5">Siswa Terdaftar</span>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="p-4 bg-white mt-auto">
                    <a href="{{ route('academic.level', ['jurusan' => $jurusan->id, 'tingkat' => $lvl->tingkat]) }}"
                        class="w-full py-2.5 px-4 rounded-xl bg-dark-900 hover:bg-primary-600 text-white font-semibold text-xs transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <span>Telusuri Rombel Kelas {{ $lvl->tingkat }}</span>
                        <i class='bx bx-right-arrow-alt text-base transition-transform group-hover:translate-x-1'></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection

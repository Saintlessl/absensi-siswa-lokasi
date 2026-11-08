@extends('layouts.app')

@section('title', $jurusan->nama_jurusan . ' — Struktur Akademik')
@section('page-title', $jurusan->nama_jurusan)
@section('page-subtitle', 'Pilih tingkat kelas aktif atau telusuri direktori alumni angkatan yang telah lulus')

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
                    <span class="text-xs text-dark-400">Program Keahlian SMK • Tahun Ajaran {{ $activeTahunAjaran }}</span>
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

    {{-- SECTION A: ANGKATAN AKTIF --}}
    <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                <h3 class="text-base font-bold text-dark-800 tracking-tight">ANGKATAN AKTIF</h3>
            </div>
            <p class="text-xs text-dark-400 mt-0.5">Tingkat kelas 10, 11, dan 12 yang sedang aktif menempuh pembelajaran pada jurusan ini.</p>
        </div>
        <span class="text-xs font-semibold px-3 py-1 bg-blue-50 text-[#003EA7] border border-blue-100 rounded-full">
            T.A. {{ $activeTahunAjaran }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        @foreach ($levelsData as $lvl)
            @php
                $levelStyles = match($lvl->tingkat) {
                    10 => [
                        'icon' => 'bx-compass',
                        'color' => 'from-blue-500 to-indigo-600',
                        'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'fase' => 'Fase E (Tingkat Awal)',
                        'accent' => 'border-blue-200 bg-blue-50/20'
                    ],
                    11 => [
                        'icon' => 'bx-layer',
                        'color' => 'from-indigo-500 to-purple-600',
                        'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'fase' => 'Fase F (Tingkat Lanjutan)',
                        'accent' => 'border-indigo-200 bg-indigo-50/20'
                    ],
                    12 => [
                        'icon' => 'bx-award',
                        'color' => 'from-purple-500 to-pink-600',
                        'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'fase' => 'Fase F Akhir (Pra-Kelulusan)',
                        'accent' => 'border-purple-200 bg-purple-50/20'
                    ],
                    default => [
                        'icon' => 'bx-book',
                        'color' => 'from-gray-500 to-gray-700',
                        'badge' => 'bg-gray-50 text-gray-700 border-gray-200',
                        'fase' => 'Fase Akademik',
                        'accent' => 'border-gray-200 bg-gray-50/20'
                    ],
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

                    <div class="flex items-baseline justify-between">
                        <h3 class="text-2xl font-black text-dark-900 leading-tight">
                            Kelas {{ $lvl->tingkat }}
                        </h3>
                        @if ($lvl->angkatan)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#003EA7] border border-blue-200 font-mono">
                                {{ $lvl->angkatan->nama_angkatan }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-dark-400 mt-1">
                        {{ $jurusan->kode_jurusan }} &bull; Masuk {{ $lvl->angkatan ? $lvl->angkatan->tahun_masuk : '-' }} (T.A. {{ $lvl->tahun_ajaran }})
                    </p>
                </div>

                {{-- Statistics (Active vs Total) --}}
                <div class="p-6 grid grid-cols-2 gap-4 border-b border-dark-100 bg-dark-50/20 text-center">
                    <div class="p-3 rounded-xl bg-white border border-dark-200/60 shadow-sm">
                        <span class="block text-2xl font-extrabold text-dark-900">{{ $lvl->rombel_count }}</span>
                        <span class="block text-xs text-dark-500 font-medium mt-0.5">Rombel Aktif</span>
                    </div>
                    <a href="{{ route('siswa', array_filter(['jurusan_id' => $jurusan->id, 'tingkat' => $lvl->tingkat, 'angkatan_id' => $lvl->angkatan?->id, 'status' => 'aktif'])) }}"
                       class="p-3 rounded-xl bg-white border border-dark-200/60 shadow-sm hover:border-blue-400 hover:bg-blue-50/40 transition-all block group/stat"
                       title="Lihat siswa aktif di Data Siswa">
                        <span class="block text-2xl font-extrabold text-blue-700 group-hover/stat:scale-105 transition-transform">{{ $lvl->siswa_aktif }}</span>
                        <span class="block text-xs text-dark-500 font-medium mt-0.5 flex items-center justify-center gap-1">
                            Siswa Aktif <i class='bx bx-link-external text-[11px] text-blue-600'></i>
                        </span>
                    </a>
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

    {{-- SECTION B: ANGKATAN ALUMNI / LULUS --}}
    <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pt-2 border-t border-dark-200/50">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-base font-bold text-dark-800 tracking-tight">ANGKATAN ALUMNI / LULUS</h3>
            </div>
            <p class="text-xs text-dark-400 mt-0.5">Daftar kohort angkatan yang telah menyelesaikan studi dan resmi berstatus kelulusan (Alumni).</p>
        </div>
        <span class="text-xs font-semibold px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full flex items-center gap-1.5">
            <i class='bx bx-check-double text-sm'></i> Arsip Kelulusan
        </span>
    </div>

    @if ($alumniCohorts->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($alumniCohorts as $cohort)
                @php
                    $mst = $cohort->major_stats ?? [
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
                            <span>T.A. {{ $cohort->tahun_ajaran_kelulusan }}</span>
                        </div>
                    </div>

                    {{-- Metrics Box --}}
                    <div class="p-5 grid grid-cols-2 gap-3 border-b border-dark-100 bg-dark-50/30 text-center">
                        <div class="p-3 rounded-xl bg-white border border-dark-200/50 shadow-xs">
                            <span class="block text-xl font-black text-dark-900">{{ $mst['rombel_count'] }}</span>
                            <span class="block text-[11px] text-dark-400 font-medium uppercase mt-0.5">Rombel Terakhir</span>
                        </div>
                        <a href="{{ route('siswa', ['angkatan_id' => $cohort->id, 'status' => 'lulus', 'jurusan_id' => $jurusan->id]) }}"
                           class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-200/60 shadow-xs hover:bg-emerald-100/60 transition-all block group/alumni"
                           title="Buka siswa lulus di Data Siswa">
                            <span class="block text-xl font-black text-emerald-700 group-hover/alumni:scale-105 transition-transform">{{ $mst['siswa_lulus'] }}</span>
                            <span class="block text-[11px] text-emerald-600 font-semibold uppercase mt-0.5 flex items-center justify-center gap-1">
                                Siswa Lulus <i class='bx bx-link-external text-[10px]'></i>
                            </span>
                        </a>
                    </div>

                    {{-- Card Footer Link --}}
                    <div class="p-4 bg-white mt-auto">
                        <a href="{{ route('academic.major.alumni', ['jurusan' => $jurusan->id, 'angkatan' => $cohort->id]) }}"
                            class="w-full py-2.5 px-4 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-800 font-semibold text-xs transition-all flex items-center justify-center gap-2 shadow-xs group-hover:border-emerald-600">
                            <span>Lihat Direktori Alumni {{ $cohort->nomor_angkatan ? 'Angkatan ' . $cohort->nomor_angkatan : '' }}</span>
                            <i class='bx bx-right-arrow-alt text-base transition-transform group-hover:translate-x-1'></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 bg-white p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-3xl">
                <i class='bx bx-award'></i>
            </div>
            <h4 class="text-base font-bold text-dark-800">Belum Ada Angkatan Alumni</h4>
            <p class="text-xs text-dark-400 mt-1 max-w-sm mx-auto">
                Belum ada angkatan di Program Keahlian {{ $jurusan->nama_jurusan }} yang berstatus selesai atau menyelesaikan proses kelulusan.
            </p>
        </div>
    @endif
@endsection

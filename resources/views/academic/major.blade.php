@extends('layouts.app')

@section('title', $jurusan->nama_jurusan . ' — Struktur Akademik')
@section('page-title', $jurusan->nama_jurusan)
@section('page-subtitle', 'Pilih tingkat kelas aktif atau telusuri direktori alumni angkatan yang telah lulus')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="text-slate-500 hover:text-[#003FA9] transition">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">{{ $jurusan->nama_jurusan }}</li>
@endsection

@section('content')
    {{-- Main Container Card --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100 space-y-8">

        {{-- Header Section: Banner Jurusan (Matching ADMIN - STRUKTUR AKADEMIK _ JURUSAN DIPILIH.png) --}}
        <div class="border border-slate-100 rounded-[24px] p-6 sm:p-8 bg-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-5">
                <div class="w-16 h-16 sm:w-24 sm:h-24 rounded-2xl flex items-center justify-center shrink-0 overflow-hidden">
                    @if ($jurusan->logo_url)
                        <img src="{{ $jurusan->logo_url }}" alt="{{ $jurusan->kode_jurusan }}" class="w-full h-full object-contain">
                    @else
                        <span class="font-extrabold text-2xl text-[#003FA9] font-mono">{{ substr($jurusan->kode_jurusan, 0, 4) }}</span>
                    @endif
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 tracking-wider uppercase">Program Keahlian</span>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5 tracking-tight">{{ $jurusan->nama_jurusan }}</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        {{ $jurusan->deskripsi ?: 'Mempelajari kompetensi keahlian terintegrasi kurikulum merdeka SMK.' }}
                    </p>
                </div>
            </div>
            <div class="self-start sm:self-center">
                <a href="{{ route('academic.index') }}"
                    class="px-5 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs inline-flex items-center gap-2 shadow-sm transition whitespace-nowrap">
                    <i class='bx bx-arrow-back text-sm'></i>
                    <span>Kembali ke Semua Jurusan</span>
                </a>
            </div>
        </div>

        {{-- Section 1: Tingkat Kelas --}}
        <div>
            <div class="mb-5">
                <span class="text-xs font-bold text-[#003FA9] tracking-wider uppercase block mb-0.5">ANGKATAN AKTIF</span>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Tingkat Kelas</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pilih tingkat kelas untuk melihat daftar rombongan belajar aktif.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($levelsData as $lvl)
                    <div class="border border-slate-100 rounded-[24px] p-6 bg-white shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Kelas {{ $lvl->tingkat }}</h3>
                            <p class="text-xs text-slate-400 mt-1 font-medium">
                                @if ($lvl->angkatan)
                                    {{ $lvl->angkatan->nama_angkatan }} | {{ $lvl->angkatan->tahun_masuk }}/{{ $lvl->angkatan->tahun_masuk + 1 }}
                                @else
                                    T.A. {{ $lvl->tahun_ajaran }}
                                @endif
                            </p>

                            {{-- 2 Stat Boxes --}}
                            <div class="grid grid-cols-2 gap-3 my-5 text-center">
                                <div class="bg-[#EFF5FF] rounded-xl p-3.5">
                                    <span class="block text-xl font-black text-slate-900">{{ $lvl->rombel_count }}</span>
                                    <span class="block text-[11px] text-slate-500 font-medium mt-0.5">Rombel</span>
                                </div>
                                <div class="bg-[#EFF5FF] rounded-xl p-3.5">
                                    <span class="block text-xl font-black text-slate-900">{{ $lvl->student_count }}</span>
                                    <span class="block text-[11px] text-slate-500 font-medium mt-0.5">Siswa Terdaftar</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('academic.level', ['jurusan' => $jurusan->id, 'tingkat' => $lvl->tingkat]) }}"
                                class="w-full py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-sm transition">
                                <span>Telusuri Rombel Kelas {{ $lvl->tingkat }}</span>
                                <i class='bx bx-right-arrow-alt text-base'></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 2: Alumni (Khusus Jurusan Ini) --}}
        <div class="pt-2">
            <div class="mb-5">
                <span class="text-xs font-bold text-emerald-700 tracking-wider uppercase block mb-0.5">ANGKATAN ALUMNI / LULUS</span>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Alumni</h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar kohort angkatan yang telah menyelesaikan studi dan resmi berstatus kelulusan (Alumni).</p>
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
                            $periodDisplay = $cohort->tahun_ajaran_kelulusan ?: ($cohort->tahun_masuk ? ($cohort->tahun_masuk + 3) . '/' . ($cohort->tahun_masuk + 4) : 'Alumni');
                        @endphp
                        <div class="border border-slate-200/80 rounded-[24px] p-6 bg-[#F8FAFC] hover:bg-white hover:border-slate-300 transition-all flex flex-col justify-between group">
                            <div>
                                <span class="text-xs font-semibold text-slate-400">
                                    {{ $cohort->nama_angkatan }}
                                </span>
                                <h3 class="text-2xl font-black text-slate-600 group-hover:text-slate-800 transition-colors mt-0.5 tracking-tight">
                                    {{ $periodDisplay }}
                                </h3>

                                {{-- 2 Stat Boxes --}}
                                <div class="grid grid-cols-2 gap-3 my-5 text-center">
                                    <div class="bg-white border border-slate-200/60 rounded-xl p-3.5">
                                        <span class="block text-xl font-black text-slate-600">{{ $mst['rombel_count'] }}</span>
                                        <span class="block text-[11px] text-slate-400 font-medium mt-0.5">Rombel</span>
                                    </div>
                                    <div class="bg-white border border-slate-200/60 rounded-xl p-3.5">
                                        <span class="block text-xl font-black text-slate-600">{{ $mst['siswa_lulus'] }}</span>
                                        <span class="block text-[11px] text-slate-400 font-medium mt-0.5">Siswa Lulus</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('academic.major.alumni', ['jurusan' => $jurusan->id, 'angkatan' => $cohort->id]) }}"
                                    class="w-full py-2.5 rounded-xl bg-[#5271A7] hover:bg-[#415C8C] text-white font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-sm transition">
                                    <span>Telusuri Angkatan {{ $periodDisplay }}</span>
                                    <i class='bx bx-right-arrow-alt text-base'></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-slate-100 rounded-[24px] p-12 text-center bg-white shadow-sm">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center text-2xl">
                        <i class='bx bx-award'></i>
                    </div>
                    <h4 class="text-base font-bold text-slate-800">Belum Ada Angkatan Alumni</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Belum ada angkatan di Program Keahlian {{ $jurusan->nama_jurusan }} yang telah menyelesaikan studi.
                    </p>
                </div>
            @endif
        </div>

    </div>
@endsection

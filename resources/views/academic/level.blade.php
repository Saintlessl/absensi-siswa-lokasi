@extends('layouts.app')

@section('title', 'Kelas ' . $tingkat . ' ' . $jurusan->kode_jurusan . ' — Rombel')
@section('page-title', 'Rombel Kelas ' . $tingkat . ' ' . $jurusan->kode_jurusan)
@section('page-subtitle', 'Pilih rombongan belajar untuk melihat data siswa terdaftar')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="text-slate-500 hover:text-[#003FA9] transition">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('academic.major', $jurusan) }}" class="text-slate-500 hover:text-[#003FA9] transition font-bold">{{ $jurusan->nama_jurusan }}</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Kelas {{ $tingkat }}</li>
@endsection

@section('content')
    {{-- Main Container Card --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">

        {{-- Header Section: Detail Tingkat Kelas --}}
        <div class="border border-slate-100 rounded-[24px] p-6 sm:p-7 bg-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-3 py-1 rounded-xl bg-[#EFF5FF] text-[#003FA9] text-xs font-bold">
                        Kelas {{ $tingkat }}
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold font-mono">
                        {{ $jurusan->kode_jurusan }}
                    </span>
                    @if (isset($matchedCohort) && $matchedCohort)
                        <span class="text-xs text-slate-400 font-medium">
                            &bull; {{ $matchedCohort->nama_angkatan }} (Masuk {{ $matchedCohort->tahun_masuk }})
                        </span>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Rombongan Belajar Kelas {{ $tingkat }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    {{ $jurusan->nama_jurusan }} &bull; Tahun Ajaran Operasional {{ $activeTahunAjaran ?? '2026/2027' }}
                </p>
            </div>
            <div class="flex items-center gap-2.5 self-start sm:self-center">
                <a href="{{ route('academic.major', $jurusan) }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-2 transition">
                    <i class='bx bx-arrow-back text-sm'></i>
                    <span>Kembali ke Detail Jurusan</span>
                </a>
            </div>
        </div>

        {{-- Search Bar --}}
        <form action="{{ route('academic.level', ['jurusan' => $jurusan->id, 'tingkat' => $tingkat]) }}" method="GET">
            <div class="flex items-center gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class='bx bx-search text-base'></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="w-full h-11 pl-10 pr-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-medium placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                        placeholder="Cari nama rombel atau nama wali kelas...">
                </div>
                <button type="submit"
                    class="h-11 px-6 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition">
                    <span>Cari</span>
                </button>
                @if ($search)
                    <a href="{{ route('academic.level', ['jurusan' => $jurusan->id, 'tingkat' => $tingkat]) }}"
                        class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Grid Cards Rombel (Consistent with Card Tingkat Kelas) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
            @forelse ($kelas as $k)
                @php
                    $waliName = $k->activeHomeroom?->guru?->nama ?? $k->wali_kelas;
                @endphp
                <div class="border border-slate-100 rounded-[24px] p-6 bg-white shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        {{-- Top Badges --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-3 py-1 rounded-xl bg-[#EFF5FF] text-[#003FA9] text-xs font-bold">
                                Kelas {{ $k->tingkat }}
                            </span>
                            <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold font-mono">
                                {{ $jurusan->kode_jurusan }}
                            </span>
                        </div>

                        {{-- Nama Rombel --}}
                        <h3 class="text-2xl font-black text-slate-900 mt-4 tracking-tight group-hover:text-[#003FA9] transition-colors">
                            {{ $k->nama_kelas }}
                        </h3>

                        {{-- Wali Kelas --}}
                        <div class="flex items-center gap-2 mt-2 text-xs text-slate-500">
                            <i class='bx bx-user-voice text-base text-[#003FA9] shrink-0'></i>
                            <span class="truncate">Wali: <strong class="text-slate-800 font-semibold">{{ $waliName ?: 'Belum ditentukan' }}</strong></span>
                        </div>

                        {{-- 2 Stat Boxes --}}
                        <div class="grid grid-cols-2 gap-3 my-5 text-center">
                            <div class="bg-[#EFF5FF] rounded-xl p-3.5">
                                <span class="block text-xl font-black text-slate-900">{{ $k->siswa_count }}</span>
                                <span class="block text-[11px] text-slate-500 font-medium mt-0.5">Siswa Terdaftar</span>
                            </div>
                            <div class="bg-[#EFF5FF] rounded-xl p-3.5">
                                <span class="block text-xl font-black text-slate-900">{{ $k->jumlah_siswa ?: 36 }}</span>
                                <span class="block text-[11px] text-slate-500 font-medium mt-0.5">Kapasitas Maks</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div>
                        <a href="{{ route('academic.class', $k) }}"
                            class="w-full py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-sm transition">
                            <span>Telusuri Rombel</span>
                            <i class='bx bx-right-arrow-alt text-base'></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center border border-slate-100 rounded-[24px] bg-white">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center text-2xl">
                        <i class='bx bx-home-alt'></i>
                    </div>
                    <h4 class="text-base font-bold text-slate-800">Belum Ada Rombongan Belajar</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Tidak ditemukan data rombongan belajar untuk Kelas {{ $tingkat }} {{ $jurusan->kode_jurusan }}.
                    </p>
                </div>
            @endforelse
        </div>

    </div>
@endsection

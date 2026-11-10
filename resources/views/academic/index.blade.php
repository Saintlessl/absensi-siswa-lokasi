@extends('layouts.app')

@section('title', 'Struktur Akademik')
@section('page-title', 'Struktur Akademik')
@section('page-subtitle', 'Hierarki terpadu Jurusan, Tingkat Kelas, Rombel, serta Riwayat Angkatan Alumni')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Struktur Akademik</li>
@endsection

@section('content')
    {{-- Main Container Card (Matching ADMIN - STRUKTUR AKADEMIK.png) --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100">
        {{-- Card Header --}}
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Struktur Akademik & Program Keahlian</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Pilih Jurusan untuk menelusuri Tingkat Kelas (10, 11, 12), Rombongan Belajar, atau telusuri arsip Angkatan Alumni.
            </p>
        </div>

        {{-- Search Bar --}}
        <form action="{{ route('academic.index') }}" method="GET" class="mb-8">
            <div class="flex items-center gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class='bx bx-search text-base'></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="w-full h-11 pl-10 pr-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-medium placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                        placeholder="Cari kode atau nama jurusan...">
                </div>
                <button type="submit"
                    class="h-11 px-6 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition">
                    <span>Cari</span>
                </button>
                @if ($search)
                    <a href="{{ route('academic.index') }}"
                        class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Grid Card Jurusan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($jurusans as $jurusan)
                <div class="bg-white border border-slate-100 rounded-[24px] p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        {{-- Top: Logo & Info --}}
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 overflow-hidden">
                                @if ($jurusan->logo_url)
                                    <img src="{{ $jurusan->logo_url }}" alt="{{ $jurusan->kode_jurusan }}" class="w-full h-full object-contain">
                                @else
                                    <span class="font-extrabold text-base text-[#003FA9] font-mono">{{ substr($jurusan->kode_jurusan, 0, 4) }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-slate-900 leading-snug line-clamp-2 group-hover:text-[#003FA9] transition-colors">
                                    {{ $jurusan->nama_jurusan }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2 leading-relaxed" title="{{ $jurusan->deskripsi }}">
                                    {{ $jurusan->deskripsi ?: 'Mempelajari kompetensi keahlian terintegrasi kurikulum merdeka SMK.' }}
                                </p>
                            </div>
                        </div>

                        {{-- Middle: 3 Stat Boxes --}}
                        <div class="grid grid-cols-3 gap-2.5 my-5 text-center">
                            <div class="bg-[#EFF5FF] rounded-xl p-2.5">
                                <span class="block text-lg font-black text-slate-900">{{ $jurusan->tingkat_count ?: 3 }}</span>
                                <span class="block text-[11px] text-slate-500 font-medium mt-0.5">Tingkat</span>
                            </div>
                            <div class="bg-[#EFF5FF] rounded-xl p-2.5">
                                <span class="block text-lg font-black text-slate-900">{{ $jurusan->kelas_count }}</span>
                                <span class="block text-[11px] text-slate-500 font-medium mt-0.5">Rombel</span>
                            </div>
                            <div class="bg-[#EFF5FF] rounded-xl p-2.5">
                                <span class="block text-lg font-black text-slate-900">{{ $jurusan->siswa_count }}</span>
                                <span class="block text-[11px] text-slate-500 font-medium mt-0.5">Siswa</span>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom: Action Button --}}
                    <div>
                        <a href="{{ route('academic.major', $jurusan) }}"
                            class="w-full py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-sm transition">
                            <span>Pilih Jurusan Ini</span>
                            <i class='bx bx-right-arrow-alt text-base'></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center text-2xl">
                        <i class='bx bx-layer'></i>
                    </div>
                    <h4 class="text-base font-bold text-slate-800">Tidak Ada Program Keahlian</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Tidak ditemukan program keahlian yang cocok dengan kata kunci pencarian.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

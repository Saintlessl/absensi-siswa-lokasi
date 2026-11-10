@extends('layouts.app')

@section('title', 'Direktori Alumni ' . $angkatan->nama_angkatan . ($jurusan ? ' - ' . $jurusan->kode_jurusan : ''))
@section('page-title', 'Direktori Alumni ' . $angkatan->nama_angkatan)
@section('page-subtitle', 'Arsip lengkap data siswa lulusan, rombel terakhir, dan riwayat status akademik')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="text-slate-500 hover:text-[#003FA9] transition">Struktur Akademik</a></li>
    @if ($jurusan)
        <li><i class='bx bx-chevron-right text-slate-400'></i></li>
        <li><a href="{{ route('academic.major', $jurusan) }}" class="text-slate-500 hover:text-[#003FA9] transition font-bold">{{ $jurusan->nama_jurusan }}</a></li>
    @endif
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">Alumni {{ $angkatan->nama_angkatan }}</li>
@endsection

@section('content')
    {{-- Main Container Card --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">

        {{-- Header Section: Detail Angkatan Alumni --}}
        <div class="border border-slate-100 rounded-[24px] p-6 sm:p-7 bg-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#EFF5FF] text-[#003FA9] flex items-center justify-center font-extrabold text-2xl shrink-0 shadow-sm">
                    <i class='bx bxs-graduation'></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-3 py-1 rounded-xl bg-[#EFF5FF] text-[#003FA9] text-xs font-bold">
                            {{ $angkatan->nama_angkatan }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                            Alumni / Lulus
                        </span>
                        @if ($jurusan)
                            <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold font-mono">
                                {{ $jurusan->kode_jurusan }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Direktori Kelulusan {{ $angkatan->nama_angkatan }}
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kode: <span class="font-mono font-semibold text-slate-700">{{ $angkatan->kode_angkatan }}</span>
                        &bull; Masuk: <span class="font-semibold text-slate-700">{{ $angkatan->tahun_masuk }}</span> (T.A. {{ $angkatan->tahun_ajaran_masuk }})
                        &bull; Kelulusan: <span class="font-semibold text-[#003FA9]">{{ $angkatan->tahun_lulus_rencana }}</span> (T.A. {{ $angkatan->tahun_ajaran_kelulusan }})
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-center">
                @if ($jurusan)
                    <a href="{{ route('academic.major', $jurusan) }}"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-2 transition">
                        <i class='bx bx-arrow-back text-sm'></i>
                        <span>Kembali ke Jurusan</span>
                    </a>
                @else
                    <a href="{{ route('academic.index') }}"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-2 transition">
                        <i class='bx bx-arrow-back text-sm'></i>
                        <span>Kembali ke Struktur</span>
                    </a>
                @endif

                <a href="{{ route('academic.alumni.export_pdf', $angkatan->id) . '?' . http_build_query(request()->query()) }}"
                    class="px-4 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-2">
                    <i class='bx bxs-file-pdf text-base'></i>
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        {{-- KPI Cards Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Siswa Terdata</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_siswa'] }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">{{ $stats['rombel_count'] }} Rombel Terakhir</p>
            </div>
            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Lulus (Alumni)</p>
                <p class="text-2xl font-black text-emerald-700 mt-1">{{ $stats['siswa_lulus'] }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Resmi Menyelesaikan Studi</p>
            </div>
            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Tidak Lulus</p>
                <p class="text-2xl font-black text-rose-700 mt-1">{{ $stats['siswa_tidak_lulus'] }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Keputusan Dewan Guru</p>
            </div>
            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Pindah Sekolah</p>
                <p class="text-2xl font-black text-slate-700 mt-1">{{ $stats['siswa_pindah'] }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Mutasi Keluar</p>
            </div>
            <div class="bg-[#EFF5FF] rounded-2xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Keluar / DO</p>
                <p class="text-2xl font-black text-slate-700 mt-1">{{ $stats['siswa_keluar'] }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Mengundurkan Diri</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-[#EFF5FF] rounded-2xl p-4">
            <form action="{{ $jurusan ? route('academic.major.alumni', ['jurusan' => $jurusan->id, 'angkatan' => $angkatan->id]) : route('academic.alumni.show', $angkatan->id) }}"
                  method="GET" class="flex flex-col lg:flex-row items-center gap-3">

                {{-- Search --}}
                <div class="flex-1 w-full relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class='bx bx-search text-base'></i>
                    </div>
                    <input type="text" name="search" value="{{ $currentSearch }}"
                        class="w-full h-11 pl-10 pr-4 rounded-xl bg-white border-0 text-slate-800 text-xs font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition"
                        placeholder="Cari berdasarkan NIS, NISN, atau Nama Siswa...">
                </div>

                {{-- Filter Jurusan (If in global view) --}}
                @if (!$jurusan)
                    <div class="w-full lg:w-48">
                        <select name="jurusan_id" class="w-full h-11 px-3.5 py-2.5 rounded-xl bg-white border-0 text-slate-800 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition cursor-pointer">
                            <option value="">Semua Jurusan</option>
                            @foreach ($jurusans as $j)
                                <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                                    {{ $j->kode_jurusan }} - {{ $j->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Filter Rombel Terakhir --}}
                <div class="w-full lg:w-44">
                    <select name="rombel_id" class="w-full h-11 px-3.5 py-2.5 rounded-xl bg-white border-0 text-slate-800 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition cursor-pointer">
                        <option value="">Semua Rombel</option>
                        @foreach ($rombels as $r)
                            <option value="{{ $r->id }}" {{ $currentRombel == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status --}}
                <div class="w-full lg:w-44">
                    <select name="status" class="w-full h-11 px-3.5 py-2.5 rounded-xl bg-white border-0 text-slate-800 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition cursor-pointer">
                        <option value="semua" {{ $currentStatus === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="lulus" {{ $currentStatus === 'lulus' ? 'selected' : '' }}>Lulus (Alumni Saja)</option>
                        <option value="tidak_lulus" {{ $currentStatus === 'tidak_lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                        <option value="pindah" {{ $currentStatus === 'pindah' ? 'selected' : '' }}>Pindah Sekolah</option>
                        <option value="keluar" {{ $currentStatus === 'keluar' ? 'selected' : '' }}>Keluar / DO</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <button type="submit"
                        class="w-full lg:w-auto h-11 px-5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center justify-center gap-1.5 shadow-sm transition">
                        <i class='bx bx-filter-alt'></i>
                        <span>Filter</span>
                    </button>
                    @if ($currentSearch || ($currentStatus && $currentStatus !== 'semua') || $currentRombel || request('jurusan_id'))
                        <a href="{{ $jurusan ? route('academic.major.alumni', ['jurusan' => $jurusan->id, 'angkatan' => $angkatan->id]) : route('academic.alumni.show', $angkatan->id) }}"
                            class="h-11 px-3.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center justify-center transition" title="Reset Filter">
                            <i class='bx bx-reset text-base'></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Alumni --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4 text-center w-12">NO</th>
                        <th class="py-4 px-4">NAMA ALUMNI</th>
                        <th class="py-4 px-4">NIS / NISN</th>
                        <th class="py-4 px-4">PROGRAM KEAHLIAN</th>
                        <th class="py-4 px-4">ROMBEL TERAKHIR</th>
                        <th class="py-4 px-4">MASA STUDI</th>
                        <th class="py-4 px-4 text-center">STATUS</th>
                        <th class="py-4 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($siswas as $idx => $s)
                        @php
                            $rombelTerakhir = $s->rombel_terakhir;
                            $hasHistoricalEnrollment = $s->relationLoaded('latestEnrollment') && $s->latestEnrollment;
                        @endphp
                        <tr class="hover:bg-[#EFF5FF]/40 transition">
                            <td class="py-4 px-4 text-center font-mono text-xs text-slate-400">
                                {{ $siswas->firstItem() + $idx }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#003FA9] text-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-sm">
                                        {{ strtoupper(substr($s->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $s->nama }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-mono">
                                <p class="text-xs font-bold text-slate-900">{{ $s->nis }}</p>
                                <p class="text-[11px] text-slate-400">NISN: {{ $s->nisn ?: '-' }}</p>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $majorCode = $rombelTerakhir?->jurusan?->kode_jurusan ?? $s->kelas?->jurusan?->kode_jurusan ?? '-';
                                    $majorName = $rombelTerakhir?->jurusan?->nama_jurusan ?? $s->kelas?->jurusan?->nama_jurusan ?? 'Belum terdata';
                                @endphp
                                <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-[#EFF5FF] text-[#003FA9] font-mono">
                                    {{ $majorCode }}
                                </span>
                                <p class="text-[11px] text-slate-400 truncate max-w-[160px] mt-0.5" title="{{ $majorName }}">{{ $majorName }}</p>
                            </td>
                            <td class="py-4 px-4">
                                @if ($rombelTerakhir)
                                    <p class="font-bold text-slate-800 text-xs">{{ $rombelTerakhir->nama_kelas }}</p>
                                    @if ($hasHistoricalEnrollment)
                                        <span class="text-[10px] text-emerald-600 font-semibold block">
                                            Histori Terverifikasi
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum tercatat</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-600">
                                <p>Masuk: <strong class="text-slate-800">{{ $s->tahun_masuk ?? $angkatan->tahun_masuk }}</strong></p>
                                @if ($s->tahun_lulus)
                                    <p class="text-emerald-700 font-semibold">Lulus: {{ $s->tahun_lulus }}</p>
                                @elseif ($angkatan->tanggal_kelulusan)
                                    <p class="text-emerald-700 font-semibold">Lulus: {{ $angkatan->tahun_lulus_rencana }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if ($s->status === 'lulus')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-600 text-white">
                                        LULUS
                                    </span>
                                @elseif ($s->status === 'tidak_lulus')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-600 text-white">
                                        TIDAK LULUS
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-400 text-white">
                                        {{ strtoupper($s->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('siswa') }}?search={{ urlencode($s->nis) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#EFF5FF] hover:bg-[#E3EDFF] text-[#003FA9] text-xs font-bold transition">
                                    <i class='bx bx-user'></i>
                                    <span>Buka di Data Siswa</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center text-slate-400">
                                <i class='bx bx-user-x text-4xl mb-2 text-slate-300'></i>
                                <p class="text-sm font-medium">Tidak ada data alumni yang sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($siswas->hasPages())
            <div class="pt-6 border-t border-slate-100">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>
@endsection

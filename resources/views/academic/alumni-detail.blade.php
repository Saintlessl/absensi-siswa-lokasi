@extends('layouts.app')

@section('title', 'Direktori Alumni ' . $angkatan->nama_angkatan . ($jurusan ? ' - ' . $jurusan->kode_jurusan : ''))
@section('page-title', 'Direktori Alumni ' . $angkatan->nama_angkatan)
@section('page-subtitle', 'Arsip lengkap data siswa lulusan, rombel terakhir, dan riwayat status akademik')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600 transition-colors">Struktur Akademik</a></li>
    @if ($jurusan)
        <li><i class='bx bx-chevron-right text-dark-400'></i></li>
        <li><a href="{{ route('academic.major', $jurusan) }}" class="hover:text-primary-600 transition-colors font-mono font-bold">{{ $jurusan->kode_jurusan }}</a></li>
    @endif
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Alumni {{ $angkatan->nama_angkatan }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center font-extrabold text-2xl shadow-xs flex-shrink-0">
                <i class='bx bxs-graduation'></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-bold text-dark-800">{{ $angkatan->nama_angkatan }}</h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class='bx bx-check text-xs'></i>
                        Alumni / Lulus
                    </span>
                    @if ($jurusan)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono">
                            {{ $jurusan->kode_jurusan }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-dark-500 mt-1">
                    Kode: <span class="font-mono font-semibold text-dark-700">{{ $angkatan->kode_angkatan }}</span>
                    &bull; Masuk: <span class="font-semibold text-dark-700">{{ $angkatan->tahun_masuk }}</span> (T.A. {{ $angkatan->tahun_ajaran_masuk }})
                    &bull; Kelulusan: <span class="font-semibold text-emerald-700">{{ $angkatan->tahun_lulus_rencana }}</span> (T.A. {{ $angkatan->tahun_ajaran_kelulusan }})
                    @if ($angkatan->tanggal_kelulusan)
                        &bull; Yudisium: <span class="font-semibold text-emerald-700">{{ $angkatan->tanggal_kelulusan->translatedFormat('d M Y') }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            @if ($jurusan)
                <a href="{{ route('academic.major', $jurusan) }}"
                    class="px-3.5 py-2 border border-dark-200 rounded-xl text-dark-600 hover:bg-dark-50 text-xs font-semibold transition-colors flex items-center gap-1.5">
                    <i class='bx bx-arrow-back'></i> Kembali ke Jurusan
                </a>
            @else
                <a href="{{ route('academic.index') }}"
                    class="px-3.5 py-2 border border-dark-200 rounded-xl text-dark-600 hover:bg-dark-50 text-xs font-semibold transition-colors flex items-center gap-1.5">
                    <i class='bx bx-arrow-back'></i> Kembali ke Struktur
                </a>
            @endif

            <a href="{{ route('academic.alumni.export_pdf', $angkatan->id) . '?' . http_build_query(request()->query()) }}"
                class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                <i class='bx bxs-file-pdf text-sm'></i> Export PDF
            </a>
        </div>
    </div>
@endsection

@section('content')
    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-6">
        <div class="glass-card rounded-2xl p-4 border border-dark-200/50 bg-white">
            <p class="text-[11px] font-semibold text-dark-400 uppercase tracking-wider">Total Siswa Terdata</p>
            <p class="text-2xl font-black text-dark-800 mt-1">{{ $stats['total_siswa'] }}</p>
            <p class="text-[11px] text-dark-400 mt-0.5">{{ $stats['rombel_count'] }} Rombel Terakhir</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-emerald-200 bg-emerald-50/30">
            <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Lulus (Alumni)</p>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ $stats['siswa_lulus'] }}</p>
            <p class="text-[11px] text-emerald-600 mt-0.5">Resmi Menyelesaikan Studi</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-rose-200 bg-rose-50/30">
            <p class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider">Tidak Lulus</p>
            <p class="text-2xl font-black text-rose-700 mt-1">{{ $stats['siswa_tidak_lulus'] }}</p>
            <p class="text-[11px] text-rose-600 mt-0.5">Keputusan Dewan Guru</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-purple-200 bg-purple-50/30">
            <p class="text-[11px] font-semibold text-purple-700 uppercase tracking-wider">Pindah Sekolah</p>
            <p class="text-2xl font-black text-purple-700 mt-1">{{ $stats['siswa_pindah'] }}</p>
            <p class="text-[11px] text-purple-600 mt-0.5">Mutasi Keluar</p>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-slate-200 bg-slate-50/30">
            <p class="text-[11px] font-semibold text-slate-700 uppercase tracking-wider">Keluar / DO</p>
            <p class="text-2xl font-black text-slate-700 mt-1">{{ $stats['siswa_keluar'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Mengundurkan Diri</p>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 bg-white overflow-hidden shadow-xs">
        {{-- Filter Bar --}}
        <div class="px-6 py-4 border-b border-dark-100 bg-dark-50/30">
            <form action="{{ $jurusan ? route('academic.major.alumni', ['jurusan' => $jurusan->id, 'angkatan' => $angkatan->id]) : route('academic.alumni.show', $angkatan->id) }}"
                  method="GET" class="flex flex-col lg:flex-row items-end gap-3">

                {{-- Search --}}
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-dark-600 mb-1">Cari Alumni</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-dark-400'></i>
                        </div>
                        <input type="text" name="search" value="{{ $currentSearch }}"
                            class="block w-full pl-9 pr-3 py-2 border border-dark-200 rounded-xl bg-white text-xs text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            placeholder="Cari berdasarkan NIS, NISN, atau Nama Siswa...">
                    </div>
                </div>

                {{-- Filter Jurusan (If in global view) --}}
                @if (!$jurusan)
                    <div class="w-full lg:w-48">
                        <label class="block text-xs font-semibold text-dark-600 mb-1">Program Keahlian</label>
                        <select name="jurusan_id" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-xs text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
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
                    <label class="block text-xs font-semibold text-dark-600 mb-1">Rombel Terakhir</label>
                    <select name="rombel_id" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-xs text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
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
                    <label class="block text-xs font-semibold text-dark-600 mb-1">Status Siswa</label>
                    <select name="status" class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-white text-xs text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
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
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap shadow-xs">
                        <i class='bx bx-filter-alt'></i> Filter
                    </button>
                    @if ($currentSearch || ($currentStatus && $currentStatus !== 'semua') || $currentRombel || request('jurusan_id'))
                        <a href="{{ $jurusan ? route('academic.major.alumni', ['jurusan' => $jurusan->id, 'angkatan' => $angkatan->id]) : route('academic.alumni.show', $angkatan->id) }}"
                            class="px-3.5 py-2 border border-dark-200 rounded-xl text-xs text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-dark-100 bg-dark-50/50 text-[11px] font-bold text-dark-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5 text-center w-12">No</th>
                        <th class="px-6 py-3.5">Nama Alumni</th>
                        <th class="px-6 py-3.5">NIS / NISN</th>
                        <th class="px-6 py-3.5">Program Keahlian</th>
                        <th class="px-6 py-3.5">Rombel Terakhir</th>
                        <th class="px-6 py-3.5">Masa Studi</th>
                        <th class="px-6 py-3.5 text-center">Status Kelulusan</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-100 text-sm">
                    @forelse ($siswas as $idx => $s)
                        @php
                            $rombelTerakhir = $s->rombel_terakhir;
                            $hasHistoricalEnrollment = $s->relationLoaded('latestEnrollment') && $s->latestEnrollment;
                        @endphp
                        <tr class="hover:bg-dark-50/30 transition-colors">
                            <td class="px-6 py-4 text-center font-mono text-xs text-dark-400">
                                {{ $siswas->firstItem() + $idx }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                                        {{ strtoupper(substr($s->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-dark-900">{{ $s->nama }}</p>
                                        <p class="text-xs text-dark-400">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-mono text-xs font-bold text-dark-800">{{ $s->nis }}</p>
                                <p class="font-mono text-[11px] text-dark-400">{{ $s->nisn ?: '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $majorCode = $rombelTerakhir?->jurusan?->kode_jurusan ?? $s->kelas?->jurusan?->kode_jurusan ?? '-';
                                    $majorName = $rombelTerakhir?->jurusan?->nama_jurusan ?? $s->kelas?->jurusan?->nama_jurusan ?? 'Belum terdata';
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-dark-100 text-dark-700 font-mono">
                                    {{ $majorCode }}
                                </span>
                                <p class="text-[11px] text-dark-400 truncate max-w-[160px]" title="{{ $majorName }}">{{ $majorName }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if ($rombelTerakhir)
                                    <p class="font-semibold text-dark-800 text-xs">{{ $rombelTerakhir->nama_kelas }}</p>
                                    @if ($hasHistoricalEnrollment)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600">
                                            <i class='bx bx-check-circle'></i> Histori Terverifikasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] text-dark-400" title="Data penempatan kelas aktif terakhir">
                                            <i class='bx bx-info-circle'></i> Kelas Terakhir
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-dark-400 italic">Belum tercatat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-dark-700">Masuk: <span class="font-semibold">{{ $s->tahun_masuk ?? $angkatan->tahun_masuk }}</span></p>
                                @if ($s->tahun_lulus)
                                    <p class="text-xs text-emerald-700 font-semibold">Lulus: {{ $s->tahun_lulus }}</p>
                                @elseif ($angkatan->tanggal_kelulusan)
                                    <p class="text-xs text-emerald-700 font-semibold">Lulus: {{ $angkatan->tahun_lulus_rencana }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $s->status_badge }} border">
                                    {{ $s->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('siswa') }}?search={{ urlencode($s->nis) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-dark-200 text-xs font-semibold text-dark-700 hover:bg-dark-50 transition-colors">
                                    <i class='bx bx-user'></i> Buka di Data Siswa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-dark-400">
                                <i class='bx bx-user-x text-4xl mb-2 text-dark-300'></i>
                                <p class="text-sm font-medium">Tidak ada data alumni yang sesuai filter.</p>
                                <p class="text-xs text-dark-400 mt-0.5">Coba ubah kriteria pencarian atau reset filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($siswas->hasPages())
            <div class="px-6 py-4 border-t border-dark-100">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>
@endsection

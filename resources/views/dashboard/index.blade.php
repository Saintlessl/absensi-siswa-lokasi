@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Absensi Siswa & Manajemen Sekolah')

@section('content')

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0052CC] via-[#0055D4] to-[#0A47B8] p-7 sm:p-9 mb-6 shadow-md">
        {{-- Batik Megamendung Overlay --}}
        <div class="absolute right-0 top-0 bottom-0 h-full w-full sm:w-[50%] pointer-events-none overflow-hidden flex justify-end">
            <img src="{{ asset('img/batik-megamendung.png') }}" alt="Batik"
                 class="h-full w-auto max-w-none object-cover object-right"
                 style="mix-blend-mode: screen; opacity: 0.35;">
        </div>

        {{-- Content inside Banner --}}
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <h1 class="text-white text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">
                    {{ Auth::user()->welcome_greeting }}
                </h1>
                <p class="text-white/80 mt-2 text-xs sm:text-sm max-w-xl leading-relaxed">
                    @if (in_array(Auth::user()->role, ['superadmin', 'admin']))
                        Pantau kehadiran, kelola data siswa, dan administrasi kesiswaan dalam satu dashboard.
                    @elseif (Auth::user()->role === 'kaprog')
                        Pantau kehadiran, kelola program keahlian, dan administrasi kesiswaan dalam satu dashboard.
                    @elseif (Auth::user()->role === 'guru')
                        Pantau jadwal mengajar, kehadiran siswa, dan administrasi kelas dalam satu dashboard.
                    @elseif (Auth::user()->role === 'siswa')
                        Pantau riwayat absensi, jam masuk-pulang, dan informasi akademik Anda.
                    @elseif (Auth::user()->role === 'orangtua')
                        Pantau kehadiran dan kedisiplinan putra/putri Anda secara real-time.
                    @else
                        Pantau kehadiran dan administrasi kesiswaan dalam satu dashboard.
                    @endif
                </p>
            </div>

            {{-- Digital Clock Widget --}}
            <div class="flex-shrink-0 self-start sm:self-auto">
                <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-6 py-3 min-w-[155px] text-center shadow-inner">
                    <div id="banner-clock-time" class="text-white font-extrabold text-3xl sm:text-4xl tracking-tight leading-none font-mono">
                        {{ date('H.i') }}
                    </div>
                    <div id="banner-clock-date" class="text-white/85 text-xs font-medium mt-1.5 whitespace-nowrap">
                        {{ \Carbon\Carbon::now()->isoFormat('dddd, DD MMM Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (in_array(Auth::user()->role, ['superadmin', 'admin', 'kaprog']))
        {{-- Statistics Cards (4 Cards horizontal row) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
            {{-- Total Siswa --}}
            <div class="bg-white rounded-2xl p-6 border border-blue-100/60 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL SISWA</p>
                    <h3 class="text-3xl font-extrabold text-[#0052CC] mt-1.5 leading-none">{{ $totalSiswa ?? 0 }}</h3>
                    <p class="text-xs text-slate-400 mt-1 font-normal">Siswa terdaftar</p>
                </div>
                <div class="flex-shrink-0 text-[#0052CC]">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            {{-- Total Guru --}}
            <div class="bg-white rounded-2xl p-6 border border-blue-100/60 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL GURU</p>
                    <h3 class="text-3xl font-extrabold text-[#0052CC] mt-1.5 leading-none">{{ $totalGuru ?? 0 }}</h3>
                    <p class="text-xs text-slate-400 mt-1 font-normal">Guru aktif</p>
                </div>
                <div class="flex-shrink-0 text-[#0052CC]">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>

            {{-- Total Kelas --}}
            <div class="bg-white rounded-2xl p-6 border border-blue-100/60 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL KELAS</p>
                    <h3 class="text-3xl font-extrabold text-[#0052CC] mt-1.5 leading-none">{{ $totalKelas ?? 0 }}</h3>
                    <p class="text-xs text-slate-400 mt-1 font-normal">Kelas Tersedia</p>
                </div>
                <div class="flex-shrink-0 text-[#0052CC]">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            {{-- Hadir Hari Ini --}}
            <div class="bg-white rounded-2xl p-6 border border-blue-100/60 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">HADIR HARI INI</p>
                    <h3 class="text-3xl font-extrabold text-[#0052CC] mt-1.5 leading-none">{{ $hadirHariIni ?? 0 }}</h3>
                    <p class="text-xs text-slate-400 mt-1 font-normal">Siswa sudah absen</p>
                </div>
                <div class="flex-shrink-0 text-[#0052CC]">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Rekap Absensi Siswa Section --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-blue-100/60 shadow-sm mb-6">
            {{-- Header + Action Buttons --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Rekap Absensi Siswa</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Seluruh data kehadiran siswa</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard.export.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'search' => $search]) }}"
                       target="_blank"
                       class="px-5 py-2.5 bg-[#0052CC] hover:bg-[#0047B8] text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        Cetak PDF
                    </a>
                    <a href="{{ route('dashboard.export.excel', ['bulan' => $bulan, 'tahun' => $tahun, 'search' => $search]) }}"
                       class="px-5 py-2.5 bg-[#0052CC] hover:bg-[#0047B8] text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        Export Excel
                    </a>
                </div>
            </div>

            {{-- Filter Toolbar --}}
            <form action="{{ route('dashboard') }}" method="GET" class="mt-2 mb-6 flex flex-col sm:flex-row items-end gap-3.5">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Cari Siswa</label>
                    <div class="relative">
                        <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Nama / NIS..."
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0052CC]/20 focus:border-[#0052CC] transition">
                    </div>
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Bulan</label>
                    <select name="bulan"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0052CC]/20 focus:border-[#0052CC] transition">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                {{ $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $i, 1)->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="w-full sm:w-32">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
                    <select name="tahun"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0052CC]/20 focus:border-[#0052CC] transition">
                        @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-2.5 bg-[#0052CC] hover:bg-[#0047B8] text-white text-sm font-semibold rounded-xl shadow-xs transition flex items-center justify-center gap-2 whitespace-nowrap self-end">
                    <i class='bx bx-filter-alt text-base'></i> Filter
                </button>
            </form>

            {{-- Attendance Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-12">NO</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIS/NAMA</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">KELAS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KETERANGAN</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center w-28">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($absensi as $a)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 text-center text-slate-400 text-xs font-semibold">
                                    {{ $absensi->firstItem() + $loop->index }}
                                </td>
                                <td class="py-4 px-4 text-slate-800 font-semibold whitespace-nowrap text-xs">
                                    {{ \Carbon\Carbon::parse($a->tanggal)->isoFormat('D MMM Y') }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 text-[#003FA9] font-bold text-xs flex items-center justify-center flex-shrink-0 uppercase">
                                            {{ substr($a->siswa->nama ?? '?', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-800 text-xs truncate">{{ $a->siswa->nama ?? '-' }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $a->siswa->nis ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $a->siswa->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    @php $st = strtolower($a->status); @endphp
                                    @if ($st == 'hadir')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">
                                            Hadir
                                        </span>
                                    @elseif($st == 'izin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">
                                            Izin
                                        </span>
                                    @elseif($st == 'sakit')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">
                                            Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-600 text-white">
                                            Alfa
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-600 max-w-[160px] truncate" title="{{ $a->keterangan }}">
                                    {{ $a->keterangan ?? '-' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button"
                                                onclick='openEditAbsensi({{ $a->id }}, @js($a->status), @js($a->keterangan))'
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition"
                                                title="Edit">
                                            <i class='bx bx-edit text-base'></i>
                                        </button>
                                        @if (Auth::user()->role === 'superadmin')
                                            <form action="{{ route('dashboard.absensi.delete', $a->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="button"
                                                        class="delete-confirm p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                        title="Hapus">
                                                    <i class='bx bx-trash text-base'></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                            <i class='bx bx-folder-open text-2xl text-slate-400'></i>
                                        </div>
                                        <p class="text-sm font-medium text-slate-600">Tidak ada data</p>
                                        <p class="text-xs text-slate-400 mt-1">Belum ada catatan absensi pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($absensi->hasPages())
                <div class="pt-5 mt-4 border-t border-slate-100">
                    {{ $absensi->links() }}
                </div>
            @endif
        </div>
    @endif


    @if (Auth::user()->role === 'orangtua' && isset($siswa))
        {{-- Info Anak --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Card Profil Anak --}}
            <div class="lg:col-span-2 glass-card rounded-2xl border border-dark-200/50 p-6">
                <div class="flex items-center gap-4 mb-5">
                    <div
                        class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-primary-500/25">
                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-dark-400 uppercase tracking-wider">Nama Anak</p>
                        <h3 class="text-lg font-bold text-dark-800">{{ $siswa->nama }}</h3>
                        <p class="text-sm text-dark-500">Kelas:
                            <strong class="text-dark-700">{{ $siswa->kelas->nama_kelas ?? '-' }}</strong>
                            <span class="text-dark-300 mx-1">|</span>
                            NIS: <strong class="font-mono text-dark-700">{{ $siswa->nis }}</strong>
                        </p>
                    </div>
                </div>

                {{-- Status Hari Ini --}}
                <div
                    class="rounded-xl border overflow-hidden
                    @if (isset($absenHariIni) && strtolower($absenHariIni->status) == 'hadir') border-emerald-200 bg-emerald-50/50
                    @elseif(isset($absenHariIni) && strtolower($absenHariIni->status) == 'izin') border-blue-200 bg-blue-50/50
                    @elseif(isset($absenHariIni) && strtolower($absenHariIni->status) == 'sakit') border-amber-200 bg-amber-50/50
                    @elseif(isset($absenHariIni)) border-rose-200 bg-rose-50/50
                    @else border-dark-200 bg-dark-50/50 @endif">
                    <div
                        class="px-4 py-3 flex items-center justify-between
                        @if (isset($absenHariIni) && strtolower($absenHariIni->status) == 'hadir') bg-emerald-100/50
                        @elseif(isset($absenHariIni) && strtolower($absenHariIni->status) == 'izin') bg-blue-100/50
                        @elseif(isset($absenHariIni) && strtolower($absenHariIni->status) == 'sakit') bg-amber-100/50
                        @elseif(isset($absenHariIni)) bg-rose-100/50
                        @else bg-dark-100/50 @endif">
                        <div class="flex items-center gap-3">
                            @if (isset($absenHariIni) && strtolower($absenHariIni->status) == 'hadir')
                                <div
                                    class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                                    <i class='bx bx-check text-lg'></i>
                                </div>
                                <span class="text-sm font-bold text-emerald-700">Hadir hari ini</span>
                            @elseif(isset($absenHariIni) && strtolower($absenHariIni->status) == 'izin')
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                    <i class='bx bx-envelope text-lg'></i>
                                </div>
                                <span class="text-sm font-bold text-blue-700">Izin hari ini</span>
                            @elseif(isset($absenHariIni) && strtolower($absenHariIni->status) == 'sakit')
                                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white">
                                    <i class='bx bx-plus-medical text-lg'></i>
                                </div>
                                <span class="text-sm font-bold text-amber-700">Sakit hari ini</span>
                            @elseif(isset($absenHariIni))
                                <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white">
                                    <i class='bx bx-x text-lg'></i>
                                </div>
                                <span class="text-sm font-bold text-rose-700">Alfa hari ini</span>
                            @else
                                <div class="w-8 h-8 rounded-full bg-dark-400 flex items-center justify-center text-white">
                                    <i class='bx bx-time text-lg'></i>
                                </div>
                                <span class="text-sm font-bold text-dark-600">Belum absen hari ini</span>
                            @endif
                        </div>
                        @if (isset($absenHariIni) && $absenHariIni->jam_masuk)
                            <span
                                class="text-xs font-mono bg-white/80 px-2 py-1 rounded-lg border border-dark-100 text-dark-600">
                                {{ date('H:i', strtotime($absenHariIni->jam_masuk)) }}
                            </span>
                        @endif
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-sm text-dark-600">
                            <i class='bx bx-calendar-check text-primary-500 mr-1'></i>
                            Hadir bulan ini: <strong class="text-dark-800">{{ $rekap['hadir'] ?? 0 }} hari</strong>
                            <span class="text-dark-300 mx-1">dari</span>
                            <strong class="text-dark-800">{{ $hariEfektif ?? 0 }} hari efektif</strong>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Card Rekap Donut Chart --}}
            <div class="lg:col-span-1 glass-card rounded-2xl border border-dark-200/50 p-6">
                <h4 class="text-sm font-bold text-dark-800 mb-2">Rekap Absensi Bulanan</h4>

                <form action="{{ route('dashboard') }}" method="GET" class="mb-4">
                    <select name="bulan" onchange="this.form.submit()"
                        class="block w-full px-3 py-2 border border-dark-200 rounded-xl bg-dark-50/50 text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                {{ $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $i, 1)->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                </form>

                {{-- Donut Chart CSS --}}
                <div class="flex justify-center mb-5">
                    <div class="relative w-40 h-40">
                        <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                            <circle cx="18" cy="18" r="14" fill="none" stroke="#f1f5f9"
                                stroke-width="4" />
                            @php
                                $offset = 0;
                                $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'];
                                $values = [
                                    $rekap['persen_hadir'] ?? 0,
                                    $rekap['persen_izin'] ?? 0,
                                    $rekap['persen_sakit'] ?? 0,
                                    $rekap['persen_alpa'] ?? 0,
                                ];
                            @endphp
                            @foreach ($values as $idx => $val)
                                @if ($val > 0)
                                    <circle cx="18" cy="18" r="14" fill="none"
                                        stroke="{{ $colors[$idx] }}" stroke-width="4"
                                        stroke-dasharray="{{ ($val / 100) * 87.96 }} {{ 87.96 - ($val / 100) * 87.96 }}"
                                        stroke-dashoffset="{{ -$offset }}" stroke-linecap="round" />
                                    @php $offset += ($val / 100) * 87.96; @endphp
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold text-dark-800">{{ $rekap['persen_hadir'] ?? 0 }}%</span>
                            <span class="text-[10px] text-dark-500 uppercase tracking-wider">Hadir</span>
                        </div>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-dark-600">Hadir</span>
                        </div>
                        <span class="font-bold text-dark-800">{{ $rekap['persen_hadir'] ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-dark-600">Izin</span>
                        </div>
                        <span class="font-bold text-dark-800">{{ $rekap['persen_izin'] ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="text-dark-600">Sakit</span>
                        </div>
                        <span class="font-bold text-dark-800">{{ $rekap['persen_sakit'] ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="text-dark-600">Alfa</span>
                        </div>
                        <span class="font-bold text-dark-800">{{ $rekap['persen_alpa'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pantauan Kehadiran Harian --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-6">
            <div class="pb-4 mb-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Pantau Kehadiran Harian</h3>
                <p class="text-xs text-slate-400 mt-0.5">10 catatan terbaru di bulan
                    {{ \Carbon\Carbon::createFromDate(null, $bulan, 1)->isoFormat('MMMM') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Jam Masuk</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($riwayatHarian as $rw)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 text-slate-800 font-semibold text-xs whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($rw->tanggal)->isoFormat('dddd, D MMM') }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @php $rwSt = strtolower($rw->status); @endphp
                                    @if ($rwSt == 'hadir')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">
                                            Hadir
                                        </span>
                                    @elseif($rwSt == 'izin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">
                                            Izin
                                        </span>
                                    @elseif($rwSt == 'sakit')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">
                                            Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-600 text-white">
                                            Alfa
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if ($rw->jam_masuk)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 text-xs font-mono font-semibold text-slate-700">
                                            <i class='bx bx-time-five'></i>
                                            {{ date('H:i', strtotime($rw->jam_masuk)) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-600 text-xs">{{ $rw->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                            <i class='bx bx-history text-2xl text-slate-400'></i>
                                        </div>
                                        <p class="text-sm font-medium text-slate-600">Belum ada riwayat kehadiran</p>
                                        <p class="text-xs text-slate-400 mt-1">Belum ada riwayat kehadiran di bulan ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAMPILAN KHUSUS SISWA --}}
    @if (Auth::user()->role === 'siswa' && isset($siswa))
        {{-- Statistics Cards (Gaya Admin) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            {{-- Status Hari Ini --}}
            <div class="glass-card rounded-2xl p-5 hover-lift border border-dark-200/50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-dark-400 text-xs font-semibold uppercase tracking-wider">Status Hari Ini</p>
                        @if ($absenHariIni && $absenHariIni->jam_masuk)
                            @if ($absenHariIni->menit_terlambat > 0)
                                <h3 class="text-2xl font-bold text-amber-600 mt-2">Terlambat</h3>
                                <p class="text-dark-400 text-xs mt-1">Masuk: {{ date('H:i', strtotime($absenHariIni->jam_masuk)) }} WIB</p>
                            @else
                                <h3 class="text-2xl font-bold text-emerald-600 mt-2">Hadir</h3>
                                <p class="text-dark-400 text-xs mt-1">Masuk: {{ date('H:i', strtotime($absenHariIni->jam_masuk)) }} WIB</p>
                            @endif
                        @else
                            <h3 class="text-2xl font-bold text-rose-600 mt-2">Belum Absen</h3>
                            <p class="text-dark-400 text-xs mt-1">Batas masuk: 07:15 WIB</p>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-xl {{ $absenHariIni && $absenHariIni->jam_masuk ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white' : 'bg-gradient-to-br from-rose-500 to-rose-600 text-white' }} flex items-center justify-center shadow-lg shadow-primary-500/10">
                        <i class='bx {{ $absenHariIni && $absenHariIni->jam_masuk ? 'bx-check-shield' : 'bx-time-five' }} text-2xl'></i>
                    </div>
                </div>
            </div>

            {{-- Hadir Bulan Ini --}}
            <div class="glass-card rounded-2xl p-5 hover-lift border border-dark-200/50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-dark-400 text-xs font-semibold uppercase tracking-wider">Hadir Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-dark-800 mt-2">{{ $statsSiswa['hadir'] ?? 0 }} Hari</h3>
                        <p class="text-dark-400 text-xs mt-1">Periode {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                        <i class='bx bx-calendar-check text-2xl'></i>
                    </div>
                </div>
            </div>

            {{-- Keterlambatan --}}
            <div class="glass-card rounded-2xl p-5 hover-lift border border-dark-200/50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-dark-400 text-xs font-semibold uppercase tracking-wider">Keterlambatan</p>
                        <h3 class="text-2xl font-bold {{ ($statsSiswa['terlambat'] ?? 0) > 0 ? 'text-amber-600' : 'text-dark-800' }} mt-2">
                            {{ $statsSiswa['terlambat'] ?? 0 }} Kali
                        </h3>
                        <p class="text-dark-400 text-xs mt-1">Bulan ini</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                        <i class='bx bx-alarm-exclamation text-2xl'></i>
                    </div>
                </div>
            </div>

            {{-- Izin & Sakit --}}
            <div class="glass-card rounded-2xl p-5 hover-lift border border-dark-200/50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-dark-400 text-xs font-semibold uppercase tracking-wider">Izin & Sakit</p>
                        <h3 class="text-2xl font-bold text-dark-800 mt-2">
                            {{ ($statsSiswa['izin'] ?? 0) + ($statsSiswa['sakit'] ?? 0) }} Hari
                        </h3>
                        <p class="text-dark-400 text-xs mt-1">Izin: {{ $statsSiswa['izin'] ?? 0 }} &bull; Sakit: {{ $statsSiswa['sakit'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                        <i class='bx bx-file-blank text-2xl'></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mid Section: Profil Siswa & Aksi Cepat --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Info Profil Siswa --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 space-y-4">
                <div class="flex items-center gap-3 pb-4 border-b border-dark-100">
                    <div class="w-12 h-12 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-base shadow-md shadow-primary-500/20 flex-shrink-0">
                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-sm font-bold text-dark-900 truncate">{{ $siswa->nama }}</h4>
                        <p class="text-xs text-dark-500">NIS: <span class="font-semibold text-dark-700">{{ $siswa->nis }}</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-dark-50 border border-dark-100">
                        <span class="text-dark-400 block text-[11px]">Kelas</span>
                        <span class="font-bold text-dark-800 mt-0.5 block">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-dark-50 border border-dark-100">
                        <span class="text-dark-400 block text-[11px]">Jurusan</span>
                        <span class="font-bold text-dark-800 mt-0.5 block">{{ $siswa->kelas->jurusan->kode_jurusan ?? '-' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-dark-50 border border-dark-100">
                        <span class="text-dark-400 block text-[11px]">Jam Masuk Hari Ini</span>
                        <span class="font-bold text-dark-800 mt-0.5 block">{{ $absenHariIni && $absenHariIni->jam_masuk ? date('H:i', strtotime($absenHariIni->jam_masuk)) . ' WIB' : '-' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-dark-50 border border-dark-100">
                        <span class="text-dark-400 block text-[11px]">Jam Pulang Hari Ini</span>
                        <span class="font-bold text-dark-800 mt-0.5 block">{{ $absenHariIni && $absenHariIni->jam_pulang ? date('H:i', strtotime($absenHariIni->jam_pulang)) . ' WIB' : '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Action Card Presensi --}}
            <div class="lg:col-span-2 glass-card rounded-2xl border border-dark-200/50 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-dark-100 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-dark-800">Presensi Mandiri Siswa</h3>
                            <p class="text-xs text-dark-500 mt-0.5">Sistem verifikasi QR Code kelas, biometrik wajah & radius GPS sekolah.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                            <i class='bx bx-check-shield'></i> Biometrik Aktif
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs mb-4">
                        <div class="p-3 rounded-xl bg-dark-50 border border-dark-100">
                            <span class="text-dark-400 block text-[11px]">Batas Masuk</span>
                            <span class="font-bold text-dark-800 mt-0.5 block">07:15 WIB</span>
                        </div>
                        <div class="p-3 rounded-xl bg-dark-50 border border-dark-100">
                            <span class="text-dark-400 block text-[11px]">Jam Buka Pulang</span>
                            <span class="font-bold text-dark-800 mt-0.5 block">14:55 WIB (Jadwal 15:00)</span>
                        </div>
                        <div class="p-3 rounded-xl bg-dark-50 border border-dark-100">
                            <span class="text-dark-400 block text-[11px]">Radius Presensi</span>
                            <span class="font-bold text-dark-800 mt-0.5 block">Dalam Area Sekolah</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-dark-100">
                    <span class="text-xs text-dark-500">
                        {{ $tanggalHariIni }} &bull; <span id="clock" class="font-mono font-semibold text-dark-700">00:00:00</span>
                    </span>
                    <a href="{{ route('absensi') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-xs transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <i class='bx bx-scan text-base'></i>
                        <span>Buka Menu Absensi Sekarang</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabel Riwayat Kehadiran Terbaru Siswa --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-8 p-6">
            <div class="pb-4 mb-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Riwayat Presensi Terbaru Anda</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Catatan presensi terakhir yang tercatat di database sekolah.</p>
                </div>
                <a href="{{ route('riwayatkehadiran') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition flex items-center gap-1">
                    <span>Lihat Semua Riwayat</span>
                    <i class='bx bx-chevron-right text-base'></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Jam Masuk</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Jam Pulang</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keterangan</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">Foto Selfie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($riwayatSiswa ?? [] as $index => $r)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 text-center text-slate-400 text-xs font-medium">{{ $index + 1 }}</td>
                                <td class="py-4 px-4 text-slate-800 font-semibold text-xs whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l, d M Y') }}
                                </td>
                                <td class="py-4 px-4 text-center text-xs font-mono text-slate-600">
                                    {{ $r->jam_masuk ? date('H:i', strtotime($r->jam_masuk)) . ' WIB' : '-' }}
                                </td>
                                <td class="py-4 px-4 text-center text-xs font-mono text-slate-600">
                                    {{ $r->jam_pulang ? date('H:i', strtotime($r->jam_pulang)) . ' WIB' : '-' }}
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    @php $st = strtolower($r->status); @endphp
                                    @if ($st == 'hadir')
                                        @if ($r->menit_terlambat > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">
                                                Tepat Waktu
                                            </span>
                                        @endif
                                    @elseif ($st == 'izin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#003FA9] text-white">
                                            Izin
                                        </span>
                                    @elseif ($st == 'sakit')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">
                                            Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-600 text-white">
                                            Alfa
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-600">
                                    {{ $r->keterangan ?? ($r->keterangan_terlambat ?? '-') }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if ($u = image_url($r->foto_masuk))
                                        <img src="{{ $u }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 mx-auto shadow-sm" loading="lazy">
                                    @else
                                        <span class="text-slate-300 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                            <i class='bx bx-history text-2xl text-slate-400'></i>
                                        </div>
                                        <p class="text-sm font-medium text-slate-600">Belum ada riwayat presensi</p>
                                        <p class="text-xs text-slate-400 mt-1">Belum ada catatan riwayat kehadiran bulan ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    

    @if (in_array(Auth::user()->role, ['guru', 'kaprog']) && isset($guru))
        {{-- Top Statistics & Teacher Profile Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            {{-- Clock & Realtime Date --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 flex flex-col justify-center items-center text-center bg-white shadow-sm">
                <div class="w-14 h-14 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mb-3">
                    <i class='bx bx-time-five text-3xl'></i>
                </div>
                <h2 id="clock" class="text-3xl font-black text-dark-900 tracking-tight">{{ $jamSekarang }}</h2>
                <p class="text-dark-500 font-medium text-xs mt-1">{{ $tanggalHariIni }}</p>
            </div>

            {{-- Teacher Profile Details --}}
            <div class="lg:col-span-2 glass-card rounded-2xl border border-dark-200/50 p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5 bg-white shadow-sm">
                <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-primary-100 flex-shrink-0 flex items-center justify-center text-primary-700 font-bold text-xl border-2 border-white shadow-sm">
                    @if($guru->photo_path && file_exists(public_path('storage/' . $guru->photo_path)))
                        <img src="{{ asset('storage/' . $guru->photo_path) }}" alt="{{ $guru->nama }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($guru->nama, 0, 2)) }}
                    @endif
                </div>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h4 class="text-xl font-black text-dark-900">{{ $guru->nama }}</h4>
                        @if($guru->status_kepegawaian)
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                {{ $guru->status_kepegawaian }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-dark-500 mb-2">
                        NIP: {{ $guru->nip ?? '-' }} &bull; NUPTK: {{ $guru->nuptk ?? '-' }}
                    </p>

                    <div class="flex flex-wrap gap-1.5">
                        @if($guru->activeHomeroomAssignment)
                            <a href="{{ route('homeroom.monitoring') }}"
                                class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-200 flex items-center gap-1 transition">
                                <i class='bx bx-id-card'></i> Wali: {{ $guru->activeHomeroomAssignment->kelas->nama_kelas }}
                            </a>
                        @endif

                        @if($guru->is_kaprog)
                            <a href="{{ route('academic.index') }}"
                                class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-purple-100 hover:bg-purple-200 text-purple-800 border border-purple-200 flex items-center gap-1 transition">
                                <i class='bx bx-award'></i> KAPROG {{ $guru->kaprog_jurusan->kode_jurusan ?? '' }}
                            </a>
                        @endif

                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $guru->activeSubjectAssignments->count() }} Mapel Diajar
                        </span>
                    </div>
                </div>
            </div>

            {{-- Workload Status Pill --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-dark-400 text-xs mb-1">
                        <span class="font-bold uppercase tracking-wider text-[10px]">Beban Kerja Guru</span>
                        <i class='bx bx-pie-chart-alt-2 text-primary-600 text-base'></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-black text-dark-900">{{ $workload['total_jp'] ?? 0 }}</span>
                        <span class="text-xs text-dark-500 font-bold">JP / minggu</span>
                    </div>
                    <p class="text-[10px] text-dark-400 mt-0.5">Target Standar: 24 - 36 JP</p>
                </div>
                <div class="mt-3">
                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold {{ ($workload['status_category'] ?? '') === 'optimal' ? 'bg-emerald-100 text-emerald-800' : (($workload['status_category'] ?? '') === 'underload' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                        {{ $workload['status_label'] ?? 'Standar Terpenuhi' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Main Dashboard Body: Schedules & Homeroom / KAPROG Widgets --}}
        <div class="grid grid-cols-1 {{ isset($homeroomStats) || isset($kaprogJurusan) ? 'lg:grid-cols-3' : '' }} gap-6">
            {{-- Today's Teaching Schedule --}}
            <div class="{{ isset($homeroomStats) || isset($kaprogJurusan) ? 'lg:col-span-2' : '' }} glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
                <div class="p-5 border-b border-dark-100/60 flex items-center justify-between bg-dark-50/40">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            <i class='bx bx-calendar-event'></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-dark-800">Jadwal Mengajar Hari Ini</h3>
                            <p class="text-[11px] text-dark-400">Sinkronisasi ruangan fisik terkini dari sistem rolling ruangan</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-xl bg-white border border-dark-200 text-xs font-bold text-dark-700">
                            {{ count($jadwalHariIni) }} Sesi Hari Ini
                        </span>
                        <a href="{{ route('teacher.schedule') }}" class="text-xs text-primary-600 hover:text-primary-700 font-semibold">
                            Lihat Semua
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waktu</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rombel</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mata Pelajaran</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ruangan Fisik</th>
                                <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($jadwalHariIni as $j)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-4 font-mono font-bold text-slate-800 text-xs">
                                        {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="font-bold text-[#003FA9]">{{ $j->kelas->nama_kelas ?? '-' }}</span>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $j->kelas->jurusan->nama_jurusan ?? '' }}</p>
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-slate-800">
                                        {{ $j->mataPelajaran->nama_mapel ?? $j->mata_pelajaran }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($j->live_room)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-md text-xs font-semibold">
                                                <i class='bx bx-building-house text-[#003FA9]'></i>
                                                {{ $j->live_room->name }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-xs">Belum Dialokasikan</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <a href="{{ route('teacher.class.students', $j->kelas_id) }}"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center gap-1 text-xs font-semibold"
                                            title="Daftar Siswa">
                                            <i class='bx bx-group text-base'></i> Siswa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                                <i class='bx bx-calendar-x text-2xl text-slate-400'></i>
                                            </div>
                                            <p class="text-sm font-medium text-slate-600">Tidak ada jadwal mengajar</p>
                                            <p class="text-xs text-slate-400 mt-1">Tidak ada jadwal mengajar untuk hari ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right Widgets: Homeroom / KAPROG Panels --}}
            @if(isset($homeroomStats) || isset($kaprogJurusan))
                <div class="space-y-6">
                    {{-- Homeroom Monitoring Widget --}}
                    @if(isset($homeroomStats))
                        <div class="glass-card rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-dark-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-base font-bold">
                                        <i class='bx bx-id-card'></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-dark-800">Kelas Binaan: {{ $homeroomStats['kelas']->nama_kelas }}</h4>
                                        <p class="text-[10px] text-dark-400">Presensi Hari Ini</p>
                                    </div>
                                </div>
                                <a href="{{ route('homeroom.monitoring') }}" class="text-[11px] text-emerald-700 hover:underline font-bold">
                                    Detail &rarr;
                                </a>
                            </div>

                            {{-- Mini Counters --}}
                            <div class="grid grid-cols-4 gap-2 mb-4 text-center">
                                <div class="bg-emerald-50 rounded-xl p-2">
                                    <span class="text-xs font-bold text-emerald-800 block">{{ $homeroomStats['hadir'] }}</span>
                                    <span class="text-[9px] text-emerald-600">Hadir</span>
                                </div>
                                <div class="bg-amber-50 rounded-xl p-2">
                                    <span class="text-xs font-bold text-amber-800 block">{{ $homeroomStats['terlambat'] }}</span>
                                    <span class="text-[9px] text-amber-600">Telat</span>
                                </div>
                                <div class="bg-blue-50 rounded-xl p-2">
                                    <span class="text-xs font-bold text-blue-800 block">{{ $homeroomStats['izin'] + $homeroomStats['sakit'] }}</span>
                                    <span class="text-[9px] text-blue-600">Izin/Skt</span>
                                </div>
                                <div class="bg-rose-50 rounded-xl p-2">
                                    <span class="text-xs font-bold text-rose-800 block">{{ $homeroomStats['belum_absen'] }}</span>
                                    <span class="text-[9px] text-rose-600">Alpa</span>
                                </div>
                            </div>

                            {{-- Attention Student Mini List --}}
                            @if(count($homeroomStats['attention_students']) > 0)
                                <div class="space-y-1.5">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Perhatian Wali Kelas:</p>
                                    @foreach($homeroomStats['attention_students']->take(3) as $as)
                                        <div class="p-2 bg-dark-50 rounded-lg flex items-center justify-between text-xs">
                                            <span class="font-medium text-dark-800 truncate max-w-[150px]">{{ $as->nama }}</span>
                                            <span class="text-[10px] text-rose-600 font-bold">Belum Presensi</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-4 pt-3 border-t border-dark-100 flex items-center justify-between text-[11px]">
                                <span class="text-dark-500">Notifikasi WA Ortu:</span>
                                <span class="font-bold text-emerald-700 flex items-center gap-1">
                                    <i class='bx bxl-whatsapp text-sm'></i> {{ $homeroomStats['wa_sent_count'] }} Terkirim
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- KAPROG Widget --}}
                    @if(isset($kaprogJurusan))
                        <div class="glass-card rounded-2xl border border-purple-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg">
                                    <i class='bx bx-award'></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-purple-900">Program Keahlian {{ $kaprogJurusan->nama_jurusan }}</h4>
                                    <p class="text-[10px] text-dark-400">{{ $kaprogJurusan->active_kelas_count ?? 0 }} Rombel Aktif</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <a href="{{ route('rooms.assignments') }}"
                                    class="p-2.5 bg-purple-50 hover:bg-purple-100 text-purple-800 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition text-center">
                                    <i class='bx bx-transfer'></i> Rolling Ruang
                                </a>
                                <a href="{{ route('academic.index') }}"
                                    class="p-2.5 bg-dark-50 hover:bg-dark-100 text-dark-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition text-center">
                                    <i class='bx bx-sitemap'></i> Akademik
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif



@endsection

@push('modals')
            <div id="modal-editabsensi-dash" class="fixed inset-0 z-[60] hidden overflow-y-auto">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                    onclick="closeModal('modal-editabsensi-dash')"></div>
                <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
                    <div class="relative w-full max-w-md rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Edit Absensi Siswa</h2>
                                <span class="text-xs font-semibold text-[#003FA9] bg-blue-100/60 px-2.5 py-0.5 rounded-full mt-1.5 inline-block">
                                    Perbarui Kehadiran Siswa
                                </span>
                            </div>
                            <button type="button" onclick="closeModal('modal-editabsensi-dash')"
                                class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

                        <form id="formEditAbsensiDash" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status Kehadiran <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="status" id="dash_edit_status" required
                                        class="block w-full px-4 pr-8 h-11 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition appearance-none cursor-pointer">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Alfa">Alfa</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan (Opsional)</label>
                                <textarea name="keterangan" id="dash_edit_keterangan" rows="3"
                                    class="block w-full p-3.5 rounded-xl bg-[#E2EDFF] border-none text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#003FA9] transition resize-none"
                                    placeholder="Ketik alasan perubahan..."></textarea>
                            </div>
                            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#D8E6FC]">
                                <button type="button" onclick="closeModal('modal-editabsensi-dash')"
                                    class="px-5 py-2.5 rounded-xl bg-[#E2EDFF] hover:bg-[#d4e4fc] text-[#003FA9] font-bold text-sm transition">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#00338d] text-white font-bold text-sm shadow-md shadow-blue-500/20 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endpush

@push('scripts')
    <script>
        function updateBannerClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const timeEl = document.getElementById('banner-clock-time');
            if (timeEl) {
                timeEl.textContent = `${hours}.${minutes}`;
            }
        }
        setInterval(updateBannerClock, 1000);
        updateBannerClock();

        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const clockElement = document.getElementById('clock');
            if (clockElement) {
                clockElement.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    <script>
        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function openEditAbsensi(id, status, keterangan) {
            document.getElementById('formEditAbsensiDash').action = '/dashboard/absensi/' + id + '/update';

            let selectStatus = document.getElementById('dash_edit_status');
            for (let i = 0; i < selectStatus.options.length; i++) {
                if (selectStatus.options[i].value.toLowerCase() === status.toLowerCase()) {
                    selectStatus.selectedIndex = i;
                    break;
                }
            }

            document.getElementById('dash_edit_keterangan').value = keterangan && keterangan !== '-' ? keterangan :
                '';
            openModal('modal-editabsensi-dash');
        }

        // Delete Confirmation
        document.querySelectorAll('.delete-confirm').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Hapus Data Absensi?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'font-inter rounded-2xl',
                        confirmButton: 'rounded-xl',
                        cancelButton: 'rounded-xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Close modal on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('modal-editabsensi-dash');
            }
        });
    </script>
@endpush

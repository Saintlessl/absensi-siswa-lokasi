@extends('layouts.app')

@section('title', $kelas->nama_kelas . ' — Direktori Siswa')
@section('page-title', $kelas->nama_kelas)
@section('page-subtitle', 'Direktori data siswa terdaftar pada rombel ini')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="text-slate-500 hover:text-[#003FA9] transition">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('academic.major', $kelas->jurusan) }}" class="text-slate-500 hover:text-[#003FA9] transition font-bold">{{ $kelas->jurusan->nama_jurusan ?? 'Jurusan' }}</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li><a href="{{ route('academic.level', ['jurusan' => $kelas->jurusan_id, 'tingkat' => $kelas->tingkat]) }}" class="text-slate-500 hover:text-[#003FA9] transition font-bold">Kelas {{ $kelas->tingkat }}</a></li>
    <li><i class='bx bx-chevron-right text-slate-400'></i></li>
    <li class="text-[#003FA9] font-semibold">{{ $kelas->nama_kelas }}</li>
@endsection

@section('content')
    {{-- Main Container Card --}}
    <div class="bg-white rounded-[28px] p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">

        {{-- Header Section: Rombel Detail --}}
        <div class="border border-slate-100 rounded-[24px] p-6 sm:p-7 bg-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-3 py-1 rounded-xl bg-[#EFF5FF] text-[#003FA9] text-xs font-bold">
                        Kelas {{ $kelas->tingkat }}
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold font-mono">
                        {{ $kelas->jurusan->kode_jurusan ?? '-' }}
                    </span>
                    <span class="text-xs text-slate-400">
                        &bull; T.A. {{ $kelas->tahun_ajaran ?: '2026/2027' }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    {{ $kelas->nama_kelas }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Wali Kelas: <strong class="text-slate-700 font-semibold">{{ $kelas->wali_kelas ?: 'Belum ditentukan' }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2.5 self-start sm:self-center">
                <a href="{{ route('academic.level', ['jurusan' => $kelas->jurusan_id, 'tingkat' => $kelas->tingkat]) }}"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-2 transition">
                    <i class='bx bx-arrow-back text-sm'></i>
                    <span>Kembali ke Rombel</span>
                </a>
                <a href="{{ route('siswa') }}?kelas_id={{ $kelas->id }}"
                    class="px-4 py-2.5 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs inline-flex items-center gap-2 shadow-sm transition">
                    <i class='bx bx-user text-sm'></i>
                    <span>Kelola di Direktori Siswa</span>
                </a>
            </div>
        </div>

        {{-- Active Room Allocation Alert Banner --}}
        @php
            $activeAssignment = $kelas->roomAssignments()->where('status', 'active')->whereDate('starts_on', '<=', now())->where(function($q) {
                $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', now());
            })->with('room')->first();
        @endphp

        @if ($activeAssignment && $activeAssignment->room)
            <div class="p-4 rounded-2xl bg-[#EFF5FF] border border-blue-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#003FA9] text-white flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                        <i class='bx bx-building-house'></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-[#003FA9] uppercase tracking-wider">Alokasi Ruangan Fisik Saat Ini</p>
                        <p class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5">
                            {{ $activeAssignment->room->name }} ({{ $activeAssignment->room->code }}) &bull; {{ $activeAssignment->room->building ?: 'Gedung Utama' }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('rooms.assignments') }}" class="text-xs font-bold text-[#003FA9] hover:underline whitespace-nowrap">
                    Lihat Jadwal Rolling Ruangan &rarr;
                </a>
            </div>
        @endif

        {{-- Search Bar --}}
        <form action="{{ route('academic.class', $kelas) }}" method="GET">
            <div class="flex items-center gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class='bx bx-search text-base'></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="w-full h-11 pl-10 pr-4 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs font-medium placeholder-slate-400 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                        placeholder="Cari nama siswa, NIS, atau NISN...">
                </div>
                <button type="submit"
                    class="h-11 px-6 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm transition">
                    <span>Cari</span>
                </button>
                @if ($search)
                    <a href="{{ route('academic.class', $kelas) }}"
                        class="h-11 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold inline-flex items-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Table Siswa (Clean typography matching Data Siswa) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">IDENTITAS SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIS / NISN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">AKUN SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KONTAK ORANG TUA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">BIOMETRIK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-[#EFF5FF]/40 transition">
                            {{-- No --}}
                            <td class="py-4 px-4 text-xs font-medium text-slate-400 text-center">
                                {{ $siswa->firstItem() + $loop->index }}
                            </td>

                            {{-- Identitas Siswa --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#003FA9] text-white flex-shrink-0 flex items-center justify-center text-xs font-extrabold shadow-sm">
                                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('siswa.show', $s->id) }}" class="font-bold text-slate-900 hover:text-[#003FA9] transition block text-xs sm:text-sm">
                                            {{ $s->nama }}
                                        </a>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- NIS / NISN --}}
                            <td class="py-4 px-4 font-mono">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $s->nis }}</div>
                                <div class="text-[11px] text-slate-400">NISN: {{ $s->nisn ?: '-' }}</div>
                            </td>

                            {{-- Akun Siswa --}}
                            <td class="py-4 px-4 font-mono text-xs text-slate-600">
                                {{ $s->user->email ?? '-' }}
                            </td>

                            {{-- Kontak Orang Tua --}}
                            <td class="py-4 px-4">
                                @php
                                    $parent = $s->orangtua->first();
                                    $parentPhone = $parent->telepon ?? $s->no_telp_orangtua;
                                @endphp
                                @if ($parentPhone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $parentPhone) }}" target="_blank"
                                        class="text-[#003FA9] hover:underline font-bold text-xs inline-flex items-center gap-1 font-mono">
                                        <i class='bx bxl-whatsapp text-emerald-600 text-sm'></i>
                                        <span>{{ $parentPhone }}</span>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Biometrik --}}
                            <td class="py-4 px-4 text-center">
                                @if ($s->faceBiometric)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-600 text-white inline-flex items-center gap-1">
                                        <i class='bx bx-check-circle'></i> Terdaftar
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-400 text-white inline-flex items-center gap-1">
                                        <i class='bx bx-time'></i> Belum
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <i class='bx bx-user text-2xl'></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Belum Ada Siswa di Rombel Ini</h3>
                                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                        Siswa belum dialokasikan ke kelas {{ $kelas->nama_kelas }}.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($siswa->hasPages())
            <div class="pt-6 border-t border-slate-100">
                {{ $siswa->links() }}
            </div>
        @endif

    </div>
@endsection

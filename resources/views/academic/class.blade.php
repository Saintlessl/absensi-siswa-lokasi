@extends('layouts.app')

@section('title', $kelas->nama_kelas . ' — Direktori Siswa')
@section('page-title', $kelas->nama_kelas)
@section('page-subtitle', 'Direktori data siswa terdaftar pada rombel ini')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.index') }}" class="hover:text-primary-600 transition-colors">Struktur Akademik</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.major', $kelas->jurusan) }}" class="hover:text-primary-600 transition-colors font-mono font-bold">{{ $kelas->jurusan->kode_jurusan ?? 'UMUM' }}</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('academic.level', ['jurusan' => $kelas->jurusan_id, 'tingkat' => $kelas->tingkat]) }}" class="hover:text-primary-600 transition-colors font-bold">Kelas {{ $kelas->tingkat }}</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium font-mono font-bold">{{ $kelas->nama_kelas }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border border-primary-200 bg-primary-50 text-primary-700 font-mono">
                    {{ $kelas->jurusan->kode_jurusan ?? '-' }}
                </span>
                <span class="text-xs text-dark-400">&bull; Tahun Ajaran {{ $kelas->tahun_ajaran ?: '2026/2027' }}</span>
                <span class="text-xs text-dark-400">&bull; Wali: <strong>{{ $kelas->wali_kelas }}</strong></span>
            </div>
            <h2 class="text-xl font-black text-dark-900 font-mono">{{ $kelas->nama_kelas }}</h2>
            <p class="text-xs text-dark-500 mt-0.5">Daftar siswa yang aktif terdaftar dalam rombongan belajar ini.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('academic.level', ['jurusan' => $kelas->jurusan_id, 'tingkat' => $kelas->tingkat]) }}"
                class="px-3.5 py-2 border border-dark-200 rounded-xl text-dark-600 hover:bg-dark-50 text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                <i class='bx bx-arrow-back'></i> Kembali ke Rombel
            </a>
            <a href="{{ route('siswa') }}?kelas_id={{ $kelas->id }}"
                class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                <i class='bx bx-user-plus'></i> Kelola di Direktori Siswa
            </a>
        </div>
    </div>
@endsection

@section('content')
    {{-- Active Room Allocation Alert Banner --}}
    @php
        $activeAssignment = $kelas->roomAssignments()->where('status', 'active')->whereDate('starts_on', '<=', now())->where(function($q) {
            $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', now());
        })->with('room')->first();
    @endphp

    @if ($activeAssignment && $activeAssignment->room)
        <div class="mb-6 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 flex items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl flex-shrink-0">
                    <i class='bx bx-building-house'></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">Alokasi Ruangan Fisik Saat Ini</p>
                    <p class="text-sm font-bold text-indigo-950">
                        {{ $activeAssignment->room->name }} ({{ $activeAssignment->room->code }}) &bull; {{ $activeAssignment->room->building ?: 'Gedung Utama' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('rooms.assignments') }}" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900 underline whitespace-nowrap">
                Lihat Jadwal Rolling Ruangan &rarr;
            </a>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 mb-6">
        <form action="{{ route('academic.class', $kelas) }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-search text-dark-400 text-lg'></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                    class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-white text-sm text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                    placeholder="Cari nama siswa, NIS, atau NISN...">
            </div>
            <button type="submit"
                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class='bx bx-filter-alt'></i> Cari
            </button>
            @if ($search)
                <a href="{{ route('academic.class', $kelas) }}"
                    class="px-4 py-2.5 border border-dark-200 rounded-xl text-sm text-dark-600 hover:bg-dark-50 transition-colors whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table Siswa --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-50/50 border-b border-dark-200/50">
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider w-14 text-center">No</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Identitas Siswa</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">NIS / NISN</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Akun Siswa</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider">Kontak Orang Tua</th>
                        <th class="px-6 py-4 text-xs font-semibold text-dark-500 uppercase tracking-wider text-center">Biometrik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-200/50">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-dark-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-dark-600 text-center">{{ $siswa->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-dark-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-primary-100 border border-primary-200 flex items-center justify-center overflow-hidden flex-shrink-0 text-primary-700 font-bold text-sm">
                                        @if ($s->photo_url)
                                            <img src="{{ $s->photo_url }}" alt="{{ $s->nama }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($s->nama, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-dark-900">{{ $s->nama }}</div>
                                        <div class="text-[11px] text-dark-400">
                                            Jenis Kelamin: <span class="font-medium text-dark-600">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono">
                                <div class="font-bold text-dark-800">{{ $s->nis }}</div>
                                <div class="text-[11px] text-dark-400">NISN: {{ $s->nisn ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-dark-600">
                                <span class="text-primary-700 bg-primary-50 px-2 py-0.5 rounded border border-primary-100">
                                    {{ $s->user->email ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $parent = $s->orangtua->first();
                                    $parentPhone = $parent->telepon ?? $s->no_telp_orangtua;
                                @endphp
                                @if ($parentPhone)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
                                        <i class='bx bxl-whatsapp text-emerald-600 text-sm'></i>
                                        <span>{{ $parentPhone }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-dark-400 italic">Belum ada nomor WA</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                @if ($s->faceBiometric)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class='bx bx-check-circle'></i> Terdaftar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class='bx bx-time'></i> Belum Rekam
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-dark-100 flex items-center justify-center text-dark-400 mb-3">
                                        <i class='bx bx-user text-2xl'></i>
                                    </div>
                                    <h3 class="text-sm font-semibold text-dark-800">Belum Ada Siswa di Rombel Ini</h3>
                                    <p class="text-xs text-dark-400 mt-1 max-w-sm mx-auto">
                                        Siswa belum ditambahkan atau belum dialokasikan ke kelas {{ $kelas->nama_kelas }}.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($siswa->hasPages())
            <div class="px-6 py-4 border-t border-dark-100 bg-dark-50/30">
                {{ $siswa->links() }}
            </div>
        @endif
    </div>
@endsection

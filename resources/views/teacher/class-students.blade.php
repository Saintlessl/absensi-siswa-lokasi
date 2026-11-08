@extends('layouts.app')

@section('title', 'Siswa Kelas ' . $kelas->nama_kelas)
@section('page-title', 'Daftar Siswa - ' . $kelas->nama_kelas)
@section('page-subtitle', 'Direktori peserta didik pada rombongan belajar ' . $kelas->nama_kelas)

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('teacher.classes') }}" class="text-dark-500 hover:text-primary-600 transition">Kelas yang Diajar</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">{{ $kelas->nama_kelas }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.classes') }}"
                class="w-10 h-10 rounded-xl bg-white border border-dark-200 hover:bg-dark-50 text-dark-600 flex items-center justify-center transition shadow-sm">
                <i class='bx bx-arrow-back text-lg'></i>
            </a>
            <div>
                <h2 class="text-xl font-medium text-dark-800">{{ $kelas->nama_kelas }}</h2>
                <p class="text-xs text-dark-400">Jurusan: {{ $kelas->jurusan->nama_jurusan ?? '-' }} &bull; Wali Kelas: {{ $kelas->wali_kelas ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 bg-primary-50 text-primary-700 border border-primary-200 rounded-xl text-xs font-bold">
                {{ $students->total() }} Peserta Didik
            </span>
        </div>
    </div>
@endsection

@section('content')
    {{-- Search Bar --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white mb-6 shadow-sm">
        <form action="{{ route('teacher.class.students', $kelas->id) }}" method="GET" class="flex items-center gap-3">
            <div class="flex-1 relative">
                <i class='bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-dark-400 text-lg'></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama siswa, NIS, atau NISN..."
                    class="w-full text-xs rounded-xl border-dark-200 pl-10 pr-4 py-2.5 bg-dark-50/30 text-dark-800 focus:bg-white focus:border-primary-500 focus:ring-primary-500 transition">
            </div>
            <button type="submit"
                class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition shadow-sm">
                <i class='bx bx-search'></i>
                <span>Cari</span>
            </button>
            @if($search)
                <a href="{{ route('teacher.class.students', $kelas->id) }}"
                    class="px-3 py-2.5 border border-dark-200 hover:bg-dark-50 text-dark-600 rounded-xl text-xs font-semibold transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Students Table --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-dark-50/70 border-b border-dark-100 text-[11px] font-bold text-dark-500 uppercase tracking-wider">
                        <th class="px-5 py-4">No</th>
                        <th class="px-5 py-4">Identitas Siswa</th>
                        <th class="px-5 py-4">NIS / NISN</th>
                        <th class="px-5 py-4">L/P</th>
                        <th class="px-5 py-4">Nomor HP Siswa</th>
                        <th class="px-5 py-4">Status Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-100/60">
                    @forelse($students as $index => $s)
                        <tr class="hover:bg-dark-50/30 transition">
                            <td class="px-5 py-4 font-mono text-dark-400">
                                {{ $students->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">
                                        {{ strtoupper(substr($s->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-dark-900">{{ $s->nama }}</p>
                                        <p class="text-[10px] text-dark-400">{{ $s->user?->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-dark-600">
                                <div><span class="font-bold text-dark-800">{{ $s->nis }}</span></div>
                                <div class="text-[10px] text-dark-400">{{ $s->nisn ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $s->jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                    {{ $s->jenis_kelamin }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-mono text-dark-600">
                                {{ $s->no_telp_siswa ?? $s->no_telp_orangtua ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-dark-400">
                                <i class='bx bx-user-x text-3xl mb-1 text-dark-300'></i>
                                <p>Tidak ditemukan siswa pada rombel ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-dark-100/60 bg-dark-50/20">
                {{ $students->links() }}
            </div>
        @endif
    </div>
@endsection

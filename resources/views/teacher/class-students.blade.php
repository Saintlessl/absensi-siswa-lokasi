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
    <div class="glass-card rounded-2xl border border-slate-100 p-4 bg-white mb-6 shadow-sm">
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
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">IDENTITAS SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIS / NISN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">L/P</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NOMOR HP SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS AKUN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($students as $index => $s)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 font-medium text-xs text-slate-400 text-center">
                                {{ $students->firstItem() + $index }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#EFF5FF] text-[#003FA9] font-bold flex items-center justify-center shrink-0 text-xs">
                                        {{ strtoupper(substr($s->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $s->nama }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $s->user?->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-mono">
                                <div><span class="font-bold text-slate-900 text-xs sm:text-sm">{{ $s->nis }}</span></div>
                                <div class="text-[11px] text-slate-400">NISN: {{ $s->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 text-center font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $s->jenis_kelamin }}
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-slate-700">
                                {{ $s->no_telp_siswa ?? $s->no_telp_orangtua ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class='bx bx-user-x text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Tidak ditemukan siswa pada rombel ini</p>
                                <p class="text-xs text-slate-400 mt-0.5">Siswa belum dialokasikan ke dalam rombongan belajar ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{ $students->links() }}
            </div>
        @endif
    </div>
@endsection

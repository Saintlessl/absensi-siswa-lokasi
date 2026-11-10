@extends('layouts.app')

@section('title', 'Monitoring Kelas Binaan - ' . $kelas->nama_kelas)
@section('page-title', 'Monitoring Presensi Kelas Binaan')
@section('page-subtitle', 'Pantau kehadiran harian, siswa terlambat/alpa, dan status notifikasi WhatsApp ke orang tua')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><span class="text-dark-500">Wali Kelas</span></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Monitoring {{ $kelas->nama_kelas }}</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Kelas Binaan Anda
                </span>
                <span class="text-xs text-dark-400">&bull; {{ $kelas->jurusan->nama_jurusan ?? '-' }}</span>
            </div>
            <h2 class="text-xl font-medium text-dark-800">{{ $kelas->nama_kelas }}</h2>
        </div>

        {{-- Date Picker Filter --}}
        <form action="{{ route('homeroom.monitoring') }}" method="GET" class="flex items-center gap-2">
            <div class="relative">
                <i class='bx bx-calendar absolute left-3 top-1/2 -translate-y-1/2 text-dark-400 text-base'></i>
                <input type="date" name="tanggal" value="{{ $tanggal }}"
                    class="text-xs rounded-xl border-dark-200 pl-9 pr-3 py-2 bg-white text-dark-800 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
            </div>
            <button type="submit"
                class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold transition shadow-sm">
                Lihat
            </button>
        </form>
    </div>
@endsection

@section('content')
    {{-- Summary Statistics Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 mb-6">
        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white">
            <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Total Siswa</p>
            <h3 class="text-2xl font-black text-dark-900 mt-1">{{ $totalStudents }}</h3>
            <p class="text-[10px] text-dark-400 mt-0.5">Siswa terdaftar</p>
        </div>

        <div class="glass-card rounded-2xl border border-emerald-100 p-4 bg-emerald-50/50">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Hadir Tepat Waktu</p>
            <h3 class="text-2xl font-black text-emerald-800 mt-1">{{ max(0, $hadirCount - $terlambatCount) }}</h3>
            <p class="text-[10px] text-emerald-600 mt-0.5">Siswa hadir on-time</p>
        </div>

        <div class="glass-card rounded-2xl border border-amber-100 p-4 bg-amber-50/50">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Terlambat</p>
            <h3 class="text-2xl font-black text-amber-800 mt-1">{{ $terlambatCount }}</h3>
            <p class="text-[10px] text-amber-600 mt-0.5">Hadir lewat jam masuk</p>
        </div>

        <div class="glass-card rounded-2xl border border-blue-100 p-4 bg-blue-50/50">
            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Izin</p>
            <h3 class="text-2xl font-black text-blue-800 mt-1">{{ $izinCount }}</h3>
            <p class="text-[10px] text-blue-600 mt-0.5">Izin terverifikasi</p>
        </div>

        <div class="glass-card rounded-2xl border border-indigo-100 p-4 bg-indigo-50/50">
            <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Sakit</p>
            <h3 class="text-2xl font-black text-indigo-800 mt-1">{{ $sakitCount }}</h3>
            <p class="text-[10px] text-indigo-600 mt-0.5">Surat dokter / izin sakit</p>
        </div>

        <div class="glass-card rounded-2xl border border-rose-100 p-4 bg-rose-50/50">
            <p class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Alpa / Belum Absen</p>
            <h3 class="text-2xl font-black text-rose-800 mt-1">{{ $alpaCount }}</h3>
            <p class="text-[10px] text-rose-600 mt-0.5">Tanpa keterangan</p>
        </div>
    </div>

    {{-- Attention Section: Siswa Bermasalah / Alpa / Terlambat --}}
    @php
        $problemStudents = $students->filter(function($s) use ($attendanceRecords) {
            $rec = $attendanceRecords->get($s->id);
            return !$rec || strtolower((string) $rec->status) === 'alfa' || $rec->menit_terlambat > 0;
        });
    @endphp

    @if($problemStudents->count() > 0)
        <div class="glass-card rounded-2xl border border-rose-200/80 bg-rose-50/30 p-5 mb-6 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                    <i class='bx bx-error-alt'></i>
                </div>
                <h3 class="text-sm font-bold text-rose-900">Perhatian Wali Kelas: Siswa Alpa atau Terlambat ({{ $problemStudents->count() }} Siswa)</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($problemStudents->take(6) as $ps)
                    @php
                        $rec = $attendanceRecords->get($ps->id);
                        $isLate = $rec && $rec->menit_terlambat > 0;
                        $waLog = $waLogs->get($ps->id);
                    @endphp
                    <div class="bg-white border border-rose-100 rounded-xl p-3 flex flex-col justify-between shadow-xs">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-dark-900">{{ $ps->nama }}</span>
                                @if($isLate)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Terlambat {{ $rec->menit_terlambat }}m
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Alpa / Belum Absen
                                    </span>
                                @endif
                            </div>
                            <p class="text-[10px] text-dark-400">NIS: {{ $ps->nis }} &bull; HP: {{ $ps->no_telp_orangtua ?? $ps->no_telp_siswa ?? '-' }}</p>
                        </div>
                        <div class="pt-2 mt-2 border-t border-dark-100 flex items-center justify-between text-[10px]">
                            <span class="text-dark-500">Ortu: {{ $ps->orangtua->first()?->nama ?? '-' }}</span>
                            @if($waLog)
                                <span class="text-emerald-600 font-bold flex items-center gap-1">
                                    <i class='bx bxl-whatsapp text-xs'></i> WA Terkirim
                                </span>
                            @else
                                <span class="text-dark-400 flex items-center gap-1">
                                    <i class='bx bxl-whatsapp text-xs'></i> Belum Ada Log
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Main Attendance Table for this Homeroom --}}
    <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
        <div class="p-4 px-5 border-b border-dark-100/70 bg-dark-50/40 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-dark-800">Daftar Presensi Peserta Didik</h4>
                <p class="text-xs text-dark-400">Tanggal: {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-primary-50 text-primary-700 border border-primary-200">
                {{ $totalStudents }} Siswa
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NAMA SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIS</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATUS PRESENSI</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JAM MASUK</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JAM PULANG</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KETERLAMBATAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KONTAK ORTU</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">NOTIFIKASI WA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($students as $idx => $s)
                        @php
                            $rec = $attendanceRecords->get($s->id);
                            $wa = $waLogs->get($s->id);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4 font-medium text-xs text-slate-400 text-center">{{ $idx + 1 }}</td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 block text-xs sm:text-sm">{{ $s->nama }}</span>
                                <span class="text-[11px] text-slate-400">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-slate-900 text-xs sm:text-sm">{{ $s->nis }}</td>
                            <td class="py-4 px-4">
                                @if($rec)
                                    @if(strtolower((string) $rec->status) === 'hadir')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                            Hadir
                                        </span>
                                    @elseif(strtolower((string) $rec->status) === 'izin')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#003FA9] text-white">
                                            Izin
                                        </span>
                                    @elseif(strtolower((string) $rec->status) === 'sakit')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white">
                                            Sakit
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-600 text-white">
                                            Alfa
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                        Belum Absen
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-slate-700 font-bold">
                                {{ $rec && $rec->jam_masuk ? \Carbon\Carbon::parse($rec->jam_masuk)->format('H:i') : '-' }}
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-slate-700 font-bold">
                                {{ $rec && $rec->jam_pulang ? \Carbon\Carbon::parse($rec->jam_pulang)->format('H:i') : '-' }}
                            </td>
                            <td class="py-4 px-4">
                                @if($rec && $rec->menit_terlambat > 0)
                                    <span class="text-amber-700 font-bold font-mono text-xs">
                                        +{{ $rec->menit_terlambat }} menit
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 block text-xs sm:text-sm">{{ $s->orangtua->first()?->nama ?? '-' }}</span>
                                @if($s->orangtua->first()?->telepon)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $s->orangtua->first()->telepon) }}" target="_blank"
                                       class="text-[#003FA9] hover:underline font-bold text-xs inline-flex items-center gap-1 font-mono">
                                        {{ $s->orangtua->first()->telepon }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400 font-mono">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($wa)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                        <i class='bx bx-check-double text-base'></i>
                                        <span>Terkirim</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class='bx bx-group text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Tidak ada data siswa di kelas binaan ini</p>
                                <p class="text-xs text-slate-400 mt-0.5">Siswa belum terdaftar pada rombel yang Anda ampu.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

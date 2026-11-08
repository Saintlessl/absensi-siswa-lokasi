@extends('layouts.app')

@section('title', 'Jadwal Mengajar Saya')
@section('page-title', 'Jadwal Mengajar')
@section('page-subtitle', 'Jadwal pembelajaran mingguan dengan sinkronisasi ruangan fisik terkini')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><span class="text-dark-500">Menu Guru</span></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Jadwal Mengajar</li>
@endsection

@section('page-header')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-medium text-dark-800">Jadwal Mengajar Mingguan</h2>
            <p class="text-xs text-dark-400 mt-0.5">Ruangan fisik dialokasikan secara realtime berdasarkan sistem rolling ruangan sekolah.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.classes') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 border border-dark-200 bg-white hover:bg-dark-50 text-dark-700 text-xs font-semibold rounded-xl shadow-sm transition">
                <i class='bx bx-chalkboard text-base text-primary-600'></i>
                <span>Lihat Kelas yang Diajar</span>
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                <i class='bx bx-printer text-base'></i>
                <span>Cetak Jadwal</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    @php
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $groupedSchedules = $schedules->groupBy('hari');
        $totalSessions = $schedules->count();
        $totalMinutes = $schedules->sum(function($j) {
            return \Carbon\Carbon::parse($j->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($j->jam_selesai));
        });
        $totalJp = round($totalMinutes / 45, 1);
    @endphp

    {{-- Quick Stat Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class='bx bx-calendar-check'></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Total Sesi Mingguan</p>
                <h3 class="text-2xl font-black text-dark-900 mt-0.5">{{ $totalSessions }} Sesi</h3>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class='bx bx-time'></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Beban Jam Pelajaran (JP)</p>
                <h3 class="text-2xl font-black text-indigo-700 mt-0.5">{{ $totalJp }} JP <span class="text-xs font-normal text-dark-400">/ minggu</span></h3>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-dark-200/50 p-4 bg-white flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class='bx bx-building-house'></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Sinkronisasi Ruangan</p>
                <h3 class="text-sm font-bold text-emerald-700 mt-0.5 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Rolling Terkini Aktif
                </h3>
            </div>
        </div>
    </div>

    {{-- Schedule Days Container --}}
    <div class="space-y-6">
        @foreach($days as $day)
            @php
                $daySchedules = $groupedSchedules->get($day, collect());
            @endphp
            <div class="glass-card rounded-2xl border border-dark-200/50 overflow-hidden bg-white shadow-sm">
                <div class="p-4 px-5 border-b border-dark-100/70 bg-dark-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $daySchedules->count() > 0 ? 'bg-primary-600' : 'bg-dark-300' }}"></span>
                        <h3 class="text-sm font-bold text-dark-800">{{ $day }}</h3>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $daySchedules->count() > 0 ? 'bg-primary-100 text-primary-700' : 'bg-dark-100 text-dark-400' }}">
                        {{ $daySchedules->count() }} Sesi Mengajar
                    </span>
                </div>

                @if($daySchedules->count() > 0)
                    <div class="divide-y divide-dark-100/60">
                        @foreach($daySchedules as $js)
                            @php
                                $durationMin = \Carbon\Carbon::parse($js->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($js->jam_selesai));
                                $jp = round($durationMin / 45, 1);
                            @endphp
                            <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-dark-50/30 transition">
                                <div class="flex items-start sm:items-center gap-4">
                                    <div class="px-3.5 py-2 rounded-xl bg-dark-100 border border-dark-200/60 text-center flex-shrink-0">
                                        <p class="text-xs font-bold text-dark-800 font-mono">
                                            {{ \Carbon\Carbon::parse($js->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($js->jam_selesai)->format('H:i') }}
                                        </p>
                                        <p class="text-[10px] text-primary-600 font-bold mt-0.5">{{ $jp }} JP</p>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-blue-100 text-blue-800">
                                                {{ $js->kelas->nama_kelas ?? 'Kelas' }}
                                            </span>
                                            <span class="text-xs text-dark-400 font-medium">
                                                {{ $js->kelas->jurusan->nama_jurusan ?? '' }}
                                            </span>
                                        </div>
                                        <h4 class="text-base font-bold text-dark-900">
                                            {{ $js->mataPelajaran->nama_mapel ?? $js->mata_pelajaran }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    {{-- Live Physical Room --}}
                                    @if($js->live_room)
                                        <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2">
                                            <i class='bx bx-building-house text-lg text-emerald-600'></i>
                                            <div>
                                                <p class="text-xs font-bold leading-tight">{{ $js->live_room->name }}</p>
                                                <p class="text-[10px] text-emerald-600">Kode: {{ $js->live_room->code }} &bull; {{ $js->live_room->building ?? 'Gedung Sekolah' }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="px-3 py-1.5 rounded-xl bg-dark-50 border border-dark-200 text-dark-400 flex items-center gap-2">
                                            <i class='bx bx-help-circle text-base'></i>
                                            <span class="text-xs italic">Ruangan belum dialokasikan</span>
                                        </div>
                                    @endif

                                    <a href="{{ route('teacher.class.students', $js->kelas_id) }}"
                                        class="px-3 py-1.5 bg-white hover:bg-dark-50 border border-dark-200 text-dark-700 rounded-xl text-xs font-semibold flex items-center gap-1 transition">
                                        <i class='bx bx-group'></i>
                                        <span>Daftar Siswa</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-dark-400 text-xs">
                        Tidak ada agenda mengajar pada hari {{ $day }}.
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endsection

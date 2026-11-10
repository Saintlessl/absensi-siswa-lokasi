@extends('layouts.app')

@section('page-title', 'Jurnal Mengajar Harian')
@section('page-subtitle', 'Catat agenda pembelajaran dan presensi sesi mata pelajaran per kelas')

@section('content')
<div class="space-y-6">

    {{-- Today's Schedule Section --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Jadwal Mengajar Hari Ini ({{ $today->translatedFormat('l, d F Y') }})</h3>
            </div>
            <a href="{{ route('jurnal.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0052CC] text-white text-xs font-semibold hover:bg-[#003FA9] transition-colors shadow-xs">
                <i class='bx bx-plus text-base'></i>
                <span>Tulis Jurnal Mandiri</span>
            </a>
        </div>

        @if($todaySchedules->isEmpty())
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm border border-blue-50">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-2 text-2xl">
                    <i class='bx bx-calendar-check'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700">Tidak Ada Jadwal Mengajar Hari Ini</h4>
                <p class="text-xs text-slate-400 mt-0.5">Anda tidak memiliki sesi mengajar yang terdaftar pada hari ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($todaySchedules as $sch)
                    @php
                        $isFilled = in_array($sch->id, $filledScheduleIds);
                        $mapelTitle = $sch->mataPelajaran ? $sch->mataPelajaran->nama_mapel : ($sch->mapel ?? 'Mata Pelajaran');
                    @endphp
                    <div class="bg-white rounded-2xl p-4 shadow-sm border {{ $isFilled ? 'border-emerald-200 bg-emerald-50/10' : 'border-blue-100' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase {{ $isFilled ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-[#0052CC]' }}">
                                    {{ $sch->kelas->nama_kelas ?? 'Kelas' }}
                                </span>
                                <span class="text-xs font-mono font-semibold text-slate-600 flex items-center gap-1">
                                    <i class='bx bx-time'></i> {{ substr($sch->jam_mulai, 0, 5) }} - {{ substr($sch->jam_selesai, 0, 5) }}
                                </span>
                            </div>

                            <h4 class="text-sm font-bold text-slate-800 line-clamp-1 mb-1">{{ $mapelTitle }}</h4>
                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                <i class='bx bx-building'></i> Ruang: {{ $sch->room->name ?? ($sch->ruangan ?? 'Kelas Reguler') }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            @if($isFilled)
                                <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
                                    <i class='bx bx-check-circle text-base'></i> Sudah Diisi
                                </span>
                                <a href="{{ route('jurnal.create', ['jadwal_id' => $sch->id]) }}"
                                   class="text-xs font-semibold text-[#0052CC] hover:underline">
                                    Lihat / Edit &rarr;
                                </a>
                            @else
                                <span class="text-xs font-semibold text-amber-600 flex items-center gap-1">
                                    <i class='bx bx-time-five text-base'></i> Belum Diisi
                                </span>
                                <a href="{{ route('jurnal.create', ['jadwal_id' => $sch->id]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#0052CC] text-white hover:bg-[#003FA9] transition-colors shadow-xs">
                                    <i class='bx bx-pencil text-sm'></i>
                                    <span>Isi Jurnal</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Filter & History Section --}}
    <div class="bg-white rounded-3xl shadow-sm border border-blue-50 overflow-hidden">
        {{-- Header & Filter --}}
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Riwayat Jurnal Pembelajaran Saya</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar agenda pengajaran dan rekap kehadiran siswa yang telah diisi</p>
            </div>

            <form method="GET" action="{{ route('jurnal.index') }}" class="flex items-center gap-2 flex-wrap">
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-1.5 px-3">

                <select name="kelas_id" class="text-xs rounded-xl border-slate-200 focus:border-[#0052CC] focus:ring-1 focus:ring-[#0052CC] bg-slate-50/50 py-1.5 px-3">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('kelas_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_kelas }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="py-1.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                    Filter
                </button>
                @if(request('tanggal') || request('kelas_id'))
                    <a href="{{ route('jurnal.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        @if($journals->isEmpty())
            <div class="py-12 px-6 text-center">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0052CC] flex items-center justify-center mx-auto mb-2 text-xl">
                    <i class='bx bx-book-content'></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700">Belum Ada Riwayat Jurnal</h4>
                <p class="text-xs text-slate-400 mt-0.5">Jurnal yang telah Anda simpan akan muncul dalam daftar ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">KELAS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MATA PELAJARAN</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MATERI PEMBELAJARAN</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">PRESENSI SESI</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">STATUS</th>
                            <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right w-20">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($journals as $jurnal)
                            @php
                                $hadirCount = $jurnal->attendances->where('status', 'hadir')->count();
                                $totalSiswa = $jurnal->attendances->count();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-4 whitespace-nowrap text-slate-900 font-bold text-xs sm:text-sm">
                                    {{ $jurnal->tanggal->translatedFormat('d M Y') }}
                                    <div class="text-[11px] text-slate-400 font-mono font-normal">{{ $jurnal->jam_ke ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-900 font-mono text-xs sm:text-sm">
                                    {{ $jurnal->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-800 text-xs sm:text-sm">
                                    {{ $jurnal->nama_mapel }}
                                </td>
                                <td class="py-4 px-4 text-slate-700 max-w-xs truncate" title="{{ $jurnal->materi_pokok }}">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm truncate">{{ $jurnal->materi_pokok }}</div>
                                    <div class="text-[11px] text-slate-400 truncate">{{ $jurnal->aktivitas }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    @if($totalSiswa > 0)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                            <i class='bx bx-user-check text-base'></i> {{ $hadirCount }}/{{ $totalSiswa }} Hadir
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $jurnal->status === 'submitted' ? 'bg-[#003FA9] text-white' : 'bg-slate-200 text-slate-700' }}">
                                        {{ strtoupper($jurnal->status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('jurnal.show', $jurnal->id) }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-[#003FA9] hover:bg-blue-50 transition inline-flex items-center gap-1"
                                       title="Lihat Detail Jurnal">
                                        <i class='bx bx-info-circle text-lg'></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($journals->hasPages())
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $journals->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

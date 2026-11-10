@extends('layouts.app')

@section('title', 'Monitoring PKL & Magang Industri')
@section('page-title', 'Monitoring PKL & Magang Industri')
@section('page-subtitle', 'Pemantauan jurnal harian, logbook, dan presensi PKL siswa di industri.')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Monitoring PKL & Magang Industri</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Pemantauan menyeluruh penempatan industri, kepatuhan presensi geofence, dan logbook siswa.</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-blue-50 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mitra Industri</span>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['total_partners'] }}</div>
                <span class="text-[10px] text-emerald-600 font-semibold">DU/DI Aktif</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-blue-50 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Siswa Terplotting</span>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['total_active_pk'] }}</div>
                <span class="text-[10px] text-blue-600 font-semibold">Sedang PKL</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-blue-50 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Presensi Hari Ini</span>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['today_attendances'] }}</div>
                <span class="text-[10px] text-emerald-600 font-semibold">Tercatat Hadir</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-blue-50 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Antrean Review</span>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['pending_logbooks'] }}</div>
                <span class="text-[10px] text-amber-600 font-semibold">Perlu Diverifikasi</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Recent Attendances -->
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm overflow-hidden p-6">
        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">Aktivitas Presensi PKL Hari Ini</h3>
            <span class="text-xs text-slate-400 font-medium">{{ now()->format('d M Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">SISWA</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">MITRA PERUSAHAAN</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JAM MASUK</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">JAM PULANG</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">VALIDASI LOKASI</th>
                        <th class="py-4 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($todayAttendances as $att)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-900 text-xs sm:text-sm block">{{ $att->placement->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[11px] text-slate-400 font-normal">{{ $att->placement->siswa->kelas->nama_kelas ?? '' }}</span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-800 text-xs sm:text-sm">
                                {{ $att->placement->partner->nama_dudi ?? '-' }}
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-slate-800 text-xs sm:text-sm">{{ $att->jam_masuk ?? '-' }}</td>
                            <td class="py-4 px-4 font-mono font-bold text-slate-800 text-xs sm:text-sm">{{ $att->jam_pulang ?? '-' }}</td>
                            <td class="py-4 px-4">
                                @if($att->is_location_valid)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                        <i class='bx bx-check-circle text-base'></i>
                                        Valid ({{ $att->distance_meters }}m)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600">
                                        <i class='bx bx-error-circle text-base'></i>
                                        Di Luar Radius ({{ $att->distance_meters }}m)
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $att->status === 'hadir' ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' }}">
                                    {{ strtoupper($att->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class='bx bx-time text-4xl mb-2 text-slate-300'></i>
                                <p class="font-semibold text-sm text-slate-600">Belum ada presensi siswa masuk hari ini</p>
                                <p class="text-xs text-slate-400 mt-0.5">Data presensi PKL harian akan muncul otomatis ketika siswa check-in.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

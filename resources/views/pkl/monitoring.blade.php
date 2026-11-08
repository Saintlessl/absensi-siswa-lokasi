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
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-800">Aktivitas Presensi PKL Hari Ini</h3>
            <span class="text-[11px] text-slate-400 font-medium">{{ now()->format('d M Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Siswa</th>
                        <th class="py-3 px-4">Mitra Perusahaan</th>
                        <th class="py-3 px-4">Jam Masuk</th>
                        <th class="py-3 px-4">Jam Pulang</th>
                        <th class="py-3 px-4">Validasi Lokasi</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($todayAttendances as $att)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $att->placement->siswa->nama ?? 'Siswa' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $att->placement->siswa->kelas->nama_kelas ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $att->placement->partner->nama_dudi ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $att->jam_masuk ?? '-' }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $att->jam_pulang ?? '-' }}</td>
                            <td class="py-3 px-4">
                                @if($att->is_location_valid)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Valid ({{ $att->distance_meters }}m)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Di Luar Radius ({{ $att->distance_meters }}m)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $att->status === 'hadir' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ strtoupper($att->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Belum ada presensi siswa masuk hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

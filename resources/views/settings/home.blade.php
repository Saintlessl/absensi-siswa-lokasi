@extends('settings.layout')

@php
    $settingsTitle = 'Home Pengaturan — MySkanic';
    $settingsPageTitle = 'Home';
    $settingsPageSubtitle = 'Dashboard ringkasan administrasi dan konfigurasi sistem MySkanic';
@endphp

@section('settings-content')
<div class="space-y-6">

    {{-- ROW 1: Jam Sekolah & Kalender Akademik (Matches Reference Image) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        
        {{-- Card: Jam Absensi / Jam Sekolah --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#003EA7] flex items-center justify-center text-lg font-bold">
                            <i class='bx bx-time-five'></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Jam Absensi</h3>
                            <p class="text-[11px] text-slate-400">{{ $namaSekolah }}</p>
                        </div>
                    </div>
                    @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.jam_sekolah'))
                        <a href="{{ route('settings.school') }}" class="text-xs font-semibold text-[#003EA7] hover:underline flex items-center gap-1">
                            Ubah <i class='bx bx-chevron-right'></i>
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 my-4 p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                    <div>
                        <span class="block text-[11px] font-medium text-slate-400">Batas Jam Masuk</span>
                        <span class="block text-2xl sm:text-3xl font-black text-[#003EA7] tracking-tight mt-1">{{ $jamMasuk }}</span>
                        <span class="inline-block mt-1 text-[10px] text-rose-500 font-semibold">Terlambat setelah ini</span>
                    </div>
                    <div class="border-l border-slate-200/80 pl-4">
                        <span class="block text-[11px] font-medium text-slate-400">Jam Buka Absen Pulang</span>
                        <span class="block text-2xl sm:text-3xl font-black text-[#003EA7] tracking-tight mt-1">{{ $jamPulang }}</span>
                        <span class="inline-block mt-1 text-[10px] text-emerald-600 font-semibold">Waktu pulang</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><i class='bx bx-check-circle text-emerald-500 text-sm'></i> Sesi Reguler Aktif</span>
                @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.jam_sekolah'))
                    <a href="{{ route('settings.school') }}" class="font-medium text-[#003EA7] hover:underline">Kelola Jam</a>
                @endif
            </div>
        </div>

        {{-- Card: Kalender Akademik --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                            <i class='bx bx-calendar-event'></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Kalender Akademik</h3>
                            <p class="text-[11px] text-slate-400">Agenda terdekat & hari libur</p>
                        </div>
                    </div>
                    @if(Auth::user()->hasRole(['superadmin', 'admin']) || Auth::user()->hasPermission('pengaturan.kalender'))
                        <a href="{{ route('settings.calendar') }}" class="text-xs font-semibold text-[#003EA7] hover:underline flex items-center gap-1">
                            Semua <i class='bx bx-chevron-right'></i>
                        </a>
                    @endif
                </div>

                {{-- Agenda List (Matches screenshot blue pill badges) --}}
                <div class="space-y-2.5 my-2">
                    @forelse($upcomingAgendas as $agenda)
                        <div class="px-4 py-2.5 rounded-xl bg-[#003EA7] text-white flex items-center justify-between shadow-sm text-xs font-medium">
                            <div class="truncate mr-2">
                                <span class="font-bold">{{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('d M Y') }}:</span>
                                <span>{{ $agenda->nama_kegiatan }}</span>
                            </div>
                            <span class="flex-shrink-0 text-[10px] px-2 py-0.5 rounded-md bg-white/20 text-white font-semibold">
                                Libur
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-xs text-slate-400">
                            <i class='bx bx-calendar-check text-2xl mb-1 text-slate-300 block'></i>
                            Tidak ada agenda libur terdekat yang tercatat
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 text-right">
                <a href="{{ route('settings.calendar') }}" class="text-xs font-semibold text-[#003EA7] hover:underline inline-flex items-center gap-1">
                    Lihat dan kelola selengkapnya <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ROW 2: Lokasi Sekolah & Ringkasan Role --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        
        {{-- Card: Lokasi Sekolah --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                            <i class='bx bx-map-pin'></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Lokasi Sekolah</h3>
                            <p class="text-[11px] text-slate-400">Radius geofencing kehadiran</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $lokasi ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $lokasi ? 'Terkonfigurasi' : 'Belum Diatur' }}
                    </span>
                </div>

                <div class="space-y-3 p-4 rounded-2xl bg-slate-50/70 border border-slate-100 text-xs">
                    <div class="flex items-start justify-between">
                        <span class="text-slate-400 font-medium">Nama Institusi:</span>
                        <span class="font-bold text-slate-800 text-right">{{ $namaSekolah }}</span>
                    </div>
                    <div class="flex items-start justify-between">
                        <span class="text-slate-400 font-medium">Koordinat GPS:</span>
                        <span class="font-mono font-semibold text-slate-700 text-right">
                            {{ $lokasi ? "{$lokasi->latitude}, {$lokasi->longitude}" : 'Belum ditentukan' }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between">
                        <span class="text-slate-400 font-medium">Radius Geofence:</span>
                        <span class="font-bold text-[#003EA7]">{{ $lokasi ? "{$lokasi->radius} Meter" : '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-right">
                <a href="{{ route('settings.location') }}" class="text-xs font-semibold text-[#003EA7] hover:underline inline-flex items-center gap-1">
                    Kelola Pengaturan Lokasi <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
        </div>

        {{-- Card: Ringkasan Manajemen Role --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">
                            <i class='bx bx-shield-quarter'></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Ringkasan Manajemen Role</h3>
                            <p class="text-[11px] text-slate-400">Kontrol granular hak akses sistem</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                        {{ $activeRolesCount }} Role Aktif
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="p-3.5 rounded-2xl bg-purple-50/50 border border-purple-100/60">
                        <span class="text-[11px] font-medium text-purple-600">Total Role Sistem</span>
                        <span class="block text-2xl font-black text-purple-900 mt-1">{{ $activeRolesCount }}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100/60">
                        <span class="text-[11px] font-medium text-blue-600">Total Permission</span>
                        <span class="block text-2xl font-black text-[#003EA7] mt-1">{{ $totalPermissionsCount }}</span>
                    </div>
                </div>

                {{-- Role Badges with User Counts --}}
                <div class="flex flex-wrap gap-2">
                    @foreach($mainRoles as $r)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/60">
                            <span>{{ $r->display_name }}</span>
                            <span class="px-1.5 py-0.2 rounded-md bg-white text-[10px] font-bold text-[#003EA7] shadow-xs">
                                {{ $r->users_count }}
                            </span>
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-right">
                @if(Auth::user()->hasRole('superadmin') || Auth::user()->hasPermission('pengaturan.role'))
                    <a href="{{ route('settings.roles.index') }}" class="text-xs font-semibold text-[#003EA7] hover:underline inline-flex items-center gap-1">
                        Kelola Role & Permission <i class='bx bx-right-arrow-alt'></i>
                    </a>
                @else
                    <span class="text-xs text-slate-400 italic">Dikelola oleh Superadmin</span>
                @endif
            </div>
        </div>

    </div>

    {{-- ROW 3: Ringkasan Pengajuan Izin (Siswa & Guru) --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                    <i class='bx bx-task'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Ringkasan Pengajuan Izin</h3>
                    <p class="text-[11px] text-slate-400">Monitoring permohonan izin siswa dan guru terintegrasi</p>
                </div>
            </div>

            <a href="{{ route('settings.izin.approval') }}" class="w-fit text-xs font-semibold text-white bg-[#003EA7] hover:bg-blue-800 px-4 py-2 rounded-xl transition shadow-xs inline-flex items-center gap-1.5">
                <i class='bx bx-check-shield'></i> Buka Persetujuan Izin
            </a>
        </div>

        {{-- Metrics Grid: Siswa vs Guru --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Total Pengajuan --}}
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Pengajuan</span>
                <span class="block text-2xl font-black text-slate-800 mt-1">{{ $leaveStats['total_siswa'] + $leaveStats['total_guru'] }}</span>
                <div class="flex items-center gap-2 mt-2 text-[11px] font-medium">
                    <span class="text-blue-600 font-bold">Siswa: {{ $leaveStats['total_siswa'] }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-indigo-600 font-bold">Guru: {{ $leaveStats['total_guru'] }}</span>
                </div>
            </div>

            {{-- Menunggu Persetujuan --}}
            <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100">
                <span class="block text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Menunggu Persetujuan</span>
                <span class="block text-2xl font-black text-amber-700 mt-1">{{ $leaveStats['total_pending'] }}</span>
                <div class="flex items-center gap-2 mt-2 text-[11px] font-medium">
                    <span class="text-amber-800">Siswa: {{ $leaveStats['pending_siswa'] }}</span>
                    <span class="text-amber-300">•</span>
                    <span class="text-amber-800">Guru: {{ $leaveStats['pending_guru'] }}</span>
                </div>
            </div>

            {{-- Disetujui --}}
            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                <span class="block text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Disetujui</span>
                <span class="block text-2xl font-black text-emerald-700 mt-1">{{ $leaveStats['total_approved'] }}</span>
                <div class="flex items-center gap-2 mt-2 text-[11px] font-medium">
                    <span class="text-emerald-800">Siswa: {{ $leaveStats['approved_siswa'] }}</span>
                    <span class="text-emerald-300">•</span>
                    <span class="text-emerald-800">Guru: {{ $leaveStats['approved_guru'] }}</span>
                </div>
            </div>

            {{-- Ditolak --}}
            <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-100">
                <span class="block text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Ditolak</span>
                <span class="block text-2xl font-black text-rose-700 mt-1">{{ $leaveStats['total_rejected'] }}</span>
                <div class="flex items-center gap-2 mt-2 text-[11px] font-medium">
                    <span class="text-rose-800">Siswa: {{ $leaveStats['rejected_siswa'] }}</span>
                    <span class="text-rose-300">•</span>
                    <span class="text-rose-800">Guru: {{ $leaveStats['rejected_guru'] }}</span>
                </div>
            </div>
        </div>

        {{-- Recent Submissions Table --}}
        <div>
            <h4 class="text-xs font-bold text-slate-700 mb-3 flex items-center gap-1.5">
                <i class='bx bx-history text-slate-400'></i> Pengajuan Izin Terbaru
            </h4>
            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100 font-bold">
                        <tr>
                            <th class="px-4 py-3">Pemohon</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Jenis Izin</th>
                            <th class="px-4 py-3">Tanggal / Periode</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentLeaves as $leave)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    {{ $leave->applicant_name }}
                                    <span class="block text-[10px] font-normal text-slate-400">{{ $leave->applicant_detail }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($leave->applicant_type === 'guru')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">Guru</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Siswa</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700">
                                    {{ $leave->jenis_label }}
                                    <span class="block text-[10px] text-slate-400">{{ $leave->durasi_label }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px]">
                                    {{ $leave->tanggal_mulai ? $leave->tanggal_mulai->format('d/m/Y') : $leave->tanggal->format('d/m/Y') }}
                                    @if($leave->tanggal_selesai && $leave->tanggal_selesai->ne($leave->tanggal_mulai ?? $leave->tanggal))
                                        - {{ $leave->tanggal_selesai->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($leave->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                    @elseif($leave->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Disetujui</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Dibatalkan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-xs text-slate-400">
                                    Belum ada data pengajuan izin
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ROW 4: Informasi Akun Terlogin --}}
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#003EA7] flex items-center justify-center text-white text-xl font-bold shadow-md shadow-blue-900/10 flex-shrink-0">
                <i class='bx bx-user-check'></i>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-900">
                    {{ Auth::user()->salutation_name ?? Auth::user()->name }}
                </h4>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ Auth::user()->email }} • Role: <strong class="text-[#003EA7]">{{ strtoupper(Auth::user()->roleRel->display_name ?? Auth::user()->role) }}</strong>
                </p>
            </div>
        </div>

        <a href="{{ route('settings.profile') }}" class="w-fit text-xs font-semibold text-[#003EA7] hover:text-white bg-blue-50 hover:bg-[#003EA7] border border-blue-200 px-4 py-2.5 rounded-xl transition duration-150 inline-flex items-center gap-2">
            <i class='bx bx-cog'></i> Pengaturan Akun & Password
        </a>
    </div>

</div>
@endsection

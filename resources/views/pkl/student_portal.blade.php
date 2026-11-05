@extends('layouts.app')

@section('title', 'Portal PKL Siswa')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Portal Magang & PKL Siswa</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Presensi geofencing harian di tempat magang industri dan pengisian logbook kegiatan.</p>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @if(!$activePlacement)
        <div class="bg-white rounded-3xl p-10 text-center border border-slate-100 shadow-sm space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Penempatan PKL Aktif</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">Anda belum dialokasikan ke industri mitra atau periode PKL Anda belum dimulai. Hubungi Ketua Program Keahlian (KAPROG) untuk informasi plotting.</p>
        </div>
    @else
        <!-- Placement Info Card -->
        <div class="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 rounded-3xl p-6 text-white shadow-lg space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-sm tracking-wider uppercase mb-2">
                        Industri Mitra Aktif
                    </span>
                    <h2 class="text-xl font-black">{{ $activePlacement->partner->nama_dudi }}</h2>
                    <p class="text-xs text-blue-200 mt-0.5">{{ $activePlacement->partner->alamat }}, {{ $activePlacement->partner->kota }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15 text-right">
                    <span class="text-[10px] text-blue-200 uppercase font-bold block">Guru Pembimbing</span>
                    <span class="text-xs font-bold text-white">{{ $activePlacement->guruPembimbing->name ?? 'Belum Ditugaskan' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-white/10 text-xs">
                <div>
                    <span class="text-[10px] text-blue-200 block">Bidang Usaha</span>
                    <span class="font-bold">{{ $activePlacement->partner->bidang_industri }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-blue-200 block">Pembimbing Industri</span>
                    <span class="font-bold">{{ $activePlacement->partner->pembimbing_dudi ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-blue-200 block">Periode Magang</span>
                    <span class="font-bold">{{ $activePlacement->tanggal_mulai->format('d M') }} - {{ $activePlacement->tanggal_selesai->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-blue-200 block">Radius Geofence</span>
                    <span class="font-bold">{{ $activePlacement->partner->radius_meter }} Meter</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Attendance Check-in Widget -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-sm font-black text-slate-800">Presensi Mandiri Hari Ini</h3>
                    <span class="text-[11px] font-semibold text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>

                @if($todayAttendance)
                    <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-center space-y-2">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-900 uppercase">
                            Status: {{ $todayAttendance->status }}
                        </span>
                        <div class="text-xs text-slate-700">
                            <div>Masuk: <strong>{{ $todayAttendance->jam_masuk ?? '-' }}</strong></div>
                            <div>Pulang: <strong>{{ $todayAttendance->jam_pulang ?? 'Belum check-out' }}</strong></div>
                            <div class="text-[10px] text-slate-500 mt-1">Jarak dari kantor: {{ $todayAttendance->distance_meters }} m</div>
                        </div>
                    </div>

                    @if(!$todayAttendance->jam_pulang)
                        <form method="POST" action="{{ route('pkl.student.checkin') }}">
                            @csrf
                            <input type="hidden" name="latitude" id="checkin_lat" value="{{ $activePlacement->partner->latitude ?? -6.917464 }}">
                            <input type="hidden" name="longitude" id="checkin_lng" value="{{ $activePlacement->partner->longitude ?? 107.619123 }}">
                            <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black rounded-xl transition shadow-sm">
                                Check-Out Pulang Magang
                            </button>
                        </form>
                    @endif
                @else
                    <form method="POST" action="{{ route('pkl.student.checkin') }}" enctype="multipart/form-data" class="space-y-3" id="attendanceForm">
                        @csrf
                        <input type="hidden" name="latitude" id="input_lat" value="">
                        <input type="hidden" name="longitude" id="input_lng" value="">

                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-[11px] text-blue-900 space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                Geolocation Device
                            </div>
                            <p id="geoStatus" class="text-slate-600">Mendeteksi koordinat GPS perangkat...</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="catatan" placeholder="Contoh: Sedang standby di divisi marketing" class="w-full text-xs rounded-xl border-slate-200">
                        </div>

                        <button type="submit" id="btnCheckin" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl transition shadow-sm">
                            Kirim Presensi Masuk PKL
                        </button>
                    </form>
                @endif
            </div>

            <!-- Right: Submit Logbook & History -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Logbook Submission Form -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                    <h3 class="text-sm font-black text-slate-800">Isi Jurnal Aktivitas Harian (Logbook)</h3>
                    <form method="POST" action="{{ route('pkl.student.logbook') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Kegiatan *</label>
                                <input type="date" name="tanggal" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required class="w-full text-xs rounded-xl border-slate-200">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Foto Bukti / Dokumentasi</label>
                                <input type="file" name="foto_dokumentasi" accept="image/*" class="w-full text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Uraian Pekerjaan / Tugas yang Dikerjakan *</label>
                            <textarea name="aktivitas" rows="3" required placeholder="Jelaskan secara mendalam tugas, alat yang digunakan, dan hasil pekerjaan hari ini..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Kompetensi / Keterampilan yang Dipelajari</label>
                            <input type="text" name="kompetensi_dipelajari" placeholder="Contoh: Troubleshooting Jaringan Mikrotik, Animasi Rigging 3D" class="w-full text-xs rounded-xl border-slate-200">
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            Simpan & Kirim Jurnal PKL
                        </button>
                    </form>
                </div>

                <!-- Logbook History -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                    <h3 class="text-sm font-black text-slate-800">Riwayat Logbook PKL Terakhir</h3>
                    <div class="divide-y divide-slate-100">
                        @forelse($recentLogbooks as $log)
                            <div class="py-3.5 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800">{{ $log->tanggal->format('d M Y') }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $log->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($log->status === 'revision_requested' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                        {{ strtoupper(str_replace('_', ' ', $log->status)) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600">{{ $log->aktivitas }}</p>
                                @if($log->kompetensi_dipelajari)
                                    <div class="text-[11px] text-blue-700 font-medium">🎯 {{ $log->kompetensi_dipelajari }}</div>
                                @endif
                                @if($log->catatan_guru)
                                    <div class="p-2 bg-rose-50 rounded-xl text-[11px] text-rose-800 font-medium border border-rose-100">
                                        Catatan Pembimbing: {{ $log->catatan_guru }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-4 text-center">Belum ada jurnal yang diserahkan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const geoStatus = document.getElementById('geoStatus');
        const inputLat = document.getElementById('input_lat');
        const inputLng = document.getElementById('input_lng');

        if (navigator.geolocation && inputLat && inputLng) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    inputLat.value = pos.coords.latitude;
                    inputLng.value = pos.coords.longitude;
                    if (geoStatus) {
                        geoStatus.innerHTML = `<span class="text-emerald-600 font-bold">Lokasi GPS Terdeteksi</span> (${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)})`;
                    }
                },
                function(err) {
                    if (geoStatus) {
                        geoStatus.innerHTML = `<span class="text-amber-600 font-semibold">Izin GPS belum diaktifkan.</span> Menggunakan estimasi koordinat.`;
                    }
                    // Fallback to partner coords
                    inputLat.value = "{{ $activePlacement->partner->latitude ?? -6.917464 }}";
                    inputLng.value = "{{ $activePlacement->partner->longitude ?? 107.619123 }}";
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        }
    });
</script>
@endsection

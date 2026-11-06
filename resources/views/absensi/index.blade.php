@extends('layouts.app')

@section('title', 'Absensi Kehadiran')
@section('page-title', 'Absensi Kehadiran')
@section('page-subtitle', 'Rekam kehadiran harian Anda')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Absensi</li>
@endsection

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map {
            height: 800px;
            width: 100%;
            border-radius: 1rem;
            z-index: 10;
        }
    </style>
@endpush

@section('content')

    @if (Auth::user()->role == 'orangtua')
        <div class="lg:col-span-1 space-y-6">
            {{-- Student ID Card --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 relative overflow-hidden group">
                <h3 class="text-sm font-bold text-dark-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class='bx bx-id-card text-primary-500 text-lg'></i>
                    {{ Auth::user()->role == 'orangtua' ? 'Informasi Absensi Anak' : 'Profil Siswa' }}
                </h3>

                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center text-xl font-bold shadow-lg">
                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-dark-900">{{ $siswa->nama }}</h4>
                        <p class="text-sm text-dark-500">NIS: {{ $siswa->nis }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-dark-50/50 border border-dark-100">
                        <span class="text-xs font-semibold text-dark-500 uppercase">Kelas</span>
                        <span class="text-sm font-bold text-dark-800">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-dark-50/50 border border-dark-100">
                        <span class="text-xs font-semibold text-dark-500 uppercase">Status Hari Ini</span>
                        @if ($absenHariIni)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class='bx bx-check-circle'></i> {{ ucfirst($absenHariIni->status) }}
                                ({{ date('H:i', strtotime($absenHariIni->jam_masuk)) }})
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <i class='bx bx-x-circle'></i> Belum Absen
                            </span>
                        @endif
                    </div>
                </div>
            </div>
    @endif

    @if (Auth::user()->role == 'siswa')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Info Panel --}}
            <div class="lg:col-span-1 space-y-6">

                {{-- Student ID Card --}}
                <div class="glass-card rounded-2xl border border-dark-200/50 p-6 relative overflow-hidden group">
                    <div
                        class="absolute -right-6 -top-6 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500">
                    </div>

                    <h3 class="text-sm font-bold text-dark-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                        <i class='bx bx-id-card text-primary-500 text-lg'></i> Profil Siswa
                    </h3>

                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center text-xl font-bold shadow-lg shadow-primary-500/30">
                            {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-dark-900">{{ $siswa->nama }}</h4>
                            <p class="text-sm text-dark-500">{{ $siswa->nis }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-dark-50/50 border border-dark-100">
                            <span class="text-xs font-semibold text-dark-500 uppercase">Kelas</span>
                            <span
                                class="text-sm font-bold text-dark-800">{{ $siswa->kelas->nama_kelas ?? 'Belum Diatur' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-dark-50/50 border border-dark-100">
                            <span class="text-xs font-semibold text-dark-500 uppercase">Status Hari Ini</span>
                            @if ($absenHariIni)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class='bx bx-check-circle'></i> Hadir
                                    ({{ date('H:i', strtotime($absenHariIni->jam_masuk)) }})
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class='bx bx-x-circle'></i> Belum Absen
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Live Clock & Selfie Panel --}}
                <div class="glass-card rounded-2xl border border-dark-200/50 p-6 text-center shadow-lg overflow-hidden relative">
                    {{-- Webcam Container --}}
                    @if (!$absenHariIni)
                        <div class="mb-4 relative group">
                            <div id="my_camera" class="mx-auto rounded-xl overflow-hidden border-2 border-primary-500/30 bg-dark-100 shadow-inner" style="width: 100%; max-width: 320px; height: 240px;">
                                {{-- Video will be rendered here --}}
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20 group-hover:opacity-40 transition-opacity">
                                <i class='bx bx-camera text-6xl text-dark-400'></i>
                            </div>
                            <div class="mt-2 text-[10px] text-dark-400 uppercase tracking-widest font-bold">Preview Kamera</div>
                        </div>
                    @endif

                    <p class="text-sm font-medium text-dark-500 mb-1">
                        {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    <div class="text-4xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-accent-600 mb-4 font-mono"
                        id="live-clock">
                        --:--:--
                    </div>

                    @if (!$absenHariIni)
                        <div class="p-4 rounded-xl mb-6 flex flex-col items-center justify-center gap-2 border transition-all duration-300"
                            id="status-panel">
                            <i class='bx bx-loader-alt animate-spin text-3xl text-dark-400'></i>
                            <p class="text-sm font-medium text-dark-600">Mendeteksi lokasi & kamera...</p>
                        </div>

                        <div class="flex flex-col gap-3">
                            <button id="btn-refresh-location" 
                                class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-dark-100 hover:bg-dark-200 text-dark-700 text-xs font-bold rounded-lg transition-all border border-dark-200">
                                <i class='bx bx-refresh text-lg'></i>
                                <span>Refresh Lokasi</span>
                            </button>
                            
                            <button id="btn-absen" disabled
                                class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-dark-300 text-dark-500 font-bold rounded-xl transition-all duration-300 cursor-not-allowed">
                                <i class='bx bx-camera text-xl'></i>
                                <span>Siapkan Kamera</span>
                            </button>
                        </div>
                        <p id="distance-info" class="mt-3 text-xs text-dark-400 hidden"></p>
                    @else
                        <div
                            class="p-5 rounded-xl bg-emerald-50 border border-emerald-200 flex flex-col items-center justify-center gap-2 mb-2">
                            <i class='bx bx-check-shield text-4xl text-emerald-500'></i>
                            <h4 class="font-bold text-emerald-700">Absensi Berhasil</h4>
                            <p class="text-xs text-emerald-600 text-center">Anda sudah melakukan rekam kehadiran untuk hari
                                ini.
                                Selamat belajar!</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Interactive Map Panel --}}
            <div class="lg:col-span-2 glass-card rounded-2xl border border-dark-200/50 p-2 relative">
                <div id="map" class="shadow-inner relative">
                    @if (!$absenHariIni)
                        <div id="map-loading"
                            class="absolute inset-0 z-[1000] bg-white/50 backdrop-blur-sm flex flex-col items-center justify-center rounded-xl">
                            <i class='bx bx-target-lock text-5xl text-primary-500 animate-pulse mb-3'></i>
                            <p class="font-semibold text-dark-700">Mencari Satelit GPS...</p>
                            <p class="text-xs text-dark-500 mt-1">Pastikan izin lokasi browser Anda aktif.</p>
                        </div>
                    @endif
                </div>

                <div class="absolute top-6 right-6 z-[400] flex flex-col gap-2">
                    <div
                        class="bg-white/90 backdrop-blur-md border border-dark-100 shadow-xl rounded-xl p-3 flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-primary-500"></div>
                        <span class="text-xs font-semibold text-dark-700">Area Sekolah</span>
                    </div>
                    <div
                        class="bg-white/90 backdrop-blur-md border border-dark-100 shadow-xl rounded-xl p-3 flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></div>
                        <span class="text-xs font-semibold text-dark-700">Lokasi Anda</span>
                    </div>
                </div>
            </div>
        </div>
    @endif


@endsection

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- Webcam JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

    <script>
        // Live Clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('live-clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        @if (!$absenHariIni)
            let currentLat = null;
            let currentLng = null;
            let currentDistance = null;
            let isMocked = false;
            let watchId = null;

            function startLocationWatch() {
                const statusPanel = document.getElementById('status-panel');
                const pDistanceInfo = document.getElementById('distance-info');
                const btnAbsen = document.getElementById('btn-absen');
                const mapLoading = document.getElementById('map-loading');

                if (watchId) navigator.geolocation.clearWatch(watchId);

                if ("geolocation" in navigator) {
                    statusPanel.innerHTML = `<i class='bx bx-loader-alt animate-spin text-3xl text-dark-400'></i><p class="text-sm font-medium text-dark-600">Mencari lokasi...</p>`;
                    
                    watchId = navigator.geolocation.watchPosition(
                        function(position) {
                            if (mapLoading) mapLoading.classList.add('hidden');
                            currentLat = position.coords.latitude;
                            currentLng = position.coords.longitude;
                            
                            // Basic Fake GPS Detection
                            isMocked = position.mocked || (position.coords.accuracy && position.coords.accuracy < 1);
                            
                            updateLocationOnMap(currentLat, currentLng);

                            currentDistance = calculateDistance(currentLat, currentLng, schoolLat, schoolLng);
                            const distanceRounded = Math.round(currentDistance);
                            pDistanceInfo.classList.remove('hidden');
                            pDistanceInfo.innerHTML = `Jarak: <b>${distanceRounded} Meter</b>`;

                            if (currentDistance <= allowedRadius) {
                                statusPanel.className = 'p-4 rounded-xl mb-6 flex flex-col items-center justify-center gap-2 border bg-emerald-50 border-emerald-200 transition-all duration-300';
                                statusPanel.innerHTML = `<i class='bx bx-check-shield text-3xl text-emerald-500'></i><p class="text-sm font-bold text-emerald-700">Area Valid!</p>`;
                                btnAbsen.disabled = false;
                                btnAbsen.className = 'w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-emerald-500/20';
                                btnAbsen.innerHTML = `<i class='bx bx-fingerprint text-2xl'></i><span>Absen Sekarang</span>`;
                            } else {
                                statusPanel.className = 'p-4 rounded-xl mb-6 flex flex-col items-center justify-center gap-2 border bg-rose-50 border-rose-200 transition-all duration-300';
                                statusPanel.innerHTML = `<i class='bx bx-error-alt text-3xl text-rose-500'></i><p class="text-sm font-bold text-rose-700">Di Luar Area!</p>`;
                                btnAbsen.disabled = true;
                                btnAbsen.className = 'w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-rose-100 text-rose-400 font-bold rounded-xl cursor-not-allowed';
                                btnAbsen.innerHTML = `<i class='bx bx-block text-xl'></i><span>Luar Radius</span>`;
                            }
                        },
                        function(error) {
                            console.error("Geolocation Error", error);
                            let msg = "Gagal mengambil lokasi.";
                            if (error.code === 1) msg = "Izin lokasi ditolak.";
                            else if (error.code === 2) msg = "Lokasi tidak tersedia.";
                            else if (error.code === 3) msg = "Waktu habis.";
                            
                            statusPanel.innerHTML = `<i class='bx bx-error-alt text-3xl text-amber-500'></i><p class="text-sm font-bold text-amber-700">${msg}</p>`;
                            Swal.fire({ icon: 'warning', title: 'Lokasi Bermasalah', text: msg });
                        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                } else {
                    Swal.fire({ icon: 'error', title: 'Tidak Didukung', text: 'Gunakan HTTPS atau Localhost.' });
                }
            }

            // Global configurations
            const schoolLat = parseFloat('{{ $lokasiSekolah->latitude }}');
            const schoolLng = parseFloat('{{ $lokasiSekolah->longitude }}');
            const allowedRadius = parseFloat('{{ $lokasiSekolah->radius }}');
            let studentMarker = null;
            let map = null;

            function updateLocationOnMap(lat, lng) {
                const newLatLng = new L.LatLng(lat, lng);
                const studentIcon = L.divIcon({
                    html: `<div class="relative w-4 h-4"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span><span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 border-2 border-white shadow-md"></span></div>`,
                    className: '',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });

                if (studentMarker) {
                    studentMarker.setLatLng(newLatLng);
                } else {
                    studentMarker = L.marker([lat, lng], { icon: studentIcon }).addTo(map);
                }

                const group = new L.featureGroup([L.marker([schoolLat, schoolLng]), studentMarker]);
                map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 17 });
            }

            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3;
                const φ1 = lat1 * Math.PI / 180;
                const φ2 = lat2 * Math.PI / 180;
                const Δφ = (lat2 - lat1) * Math.PI / 180;
                const Δλ = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Webcam
                Webcam.set({ width: 320, height: 240, image_format: 'jpeg', jpeg_quality: 90 });
                Webcam.attach('#my_camera');

                // Initialize Map
                map = L.map('map').setView([schoolLat, schoolLng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                const schoolIcon = L.divIcon({
                    html: `<div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center border-2 border-primary-500 shadow-lg"><i class='bx bxs-school text-primary-600 text-lg'></i></div>`,
                    className: '',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                L.marker([schoolLat, schoolLng], { icon: schoolIcon }).addTo(map);
                L.circle([schoolLat, schoolLng], { color: '#6366f1', fillOpacity: 0.15, radius: allowedRadius }).addTo(map);

                // Start tracking
                startLocationWatch();

                // Button refresh manual
                document.getElementById('btn-refresh-location').addEventListener('click', function() {
                    startLocationWatch();
                    Swal.fire({ icon: 'info', title: 'Refresh', text: 'Mencoba mendapatkan lokasi ulang...', timer: 1500, showConfirmButton: false });
                });

                document.getElementById('btn-absen').addEventListener('click', function() {
                    if (!currentLat || !currentLng) return;
                    Webcam.snap(function(data_uri) {
                        const btn = document.getElementById('btn-absen');
                        const originalText = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = `<i class='bx bx-loader-alt animate-spin text-2xl'></i><span>Memproses...</span>`;

                        fetch('{{ route('absensi.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                latitude: currentLat, longitude: currentLng, image: data_uri, is_mocked: isMocked
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message }).then(() => window.location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                            }
                        });
                    });
                });
            });
        @else
            document.addEventListener('DOMContentLoaded', function() {
                const schoolLat = parseFloat('{{ $lokasiSekolah->latitude }}');
                const schoolLng = parseFloat('{{ $lokasiSekolah->longitude }}');
                const map = L.map('map').setView([schoolLat, schoolLng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                const schoolIcon = L.divIcon({
                    html: `<div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center border-2 border-primary-500 shadow-lg"><i class='bx bxs-school text-primary-600 text-lg'></i></div>`,
                    className: '',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                L.marker([schoolLat, schoolLng], { icon: schoolIcon }).addTo(map);
                L.circle([schoolLat, schoolLng], { color: '#6366f1', radius: parseFloat('{{ $lokasiSekolah->radius }}') }).addTo(map);
            });
        @endif
    </script>
@endpush

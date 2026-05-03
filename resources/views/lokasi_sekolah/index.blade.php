@extends('layouts.app')

@section('title', 'Lokasi Sekolah')
@section('page-title', 'Pengaturan Lokasi')
@section('page-subtitle', 'Tentukan titik koordinat dan radius absensi sekolah')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Lokasi Sekolah</li>
@endsection

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map {
            height: 450px;
            width: 100%;
            border-radius: 0.75rem;
            z-index: 10;
        }
    </style>
@endpush

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Form Card --}}
        <div class="lg:col-span-1 glass-card rounded-2xl border border-dark-200/50 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center text-primary-600">
                    <i class='bx bx-map-pin text-xl'></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-dark-800">Koordinat Sekolah</h3>
                    <p class="text-xs text-dark-400">Atur lewat form atau klik pada peta</p>
                </div>
            </div>

            <form action="{{ route('lokasi_sekolah.update') }}" method="POST" id="frmLokasi" class="flex-1 flex flex-col">
                @csrf
                <div class="space-y-4 flex-1">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-dark-700 mb-1.5">Latitude</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class='bx bx-map text-dark-400 text-lg'></i>
                            </div>
                            <input type="number" step="any" name="latitude" id="latitude" value="{{ $lokasi->latitude }}"
                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                placeholder="-0.5413156" required autocomplete="off">
                        </div>
                    </div>
                    
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-dark-700 mb-1.5">Longitude</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class='bx bx-map text-dark-400 text-lg'></i>
                            </div>
                            <input type="number" step="any" name="longitude" id="longitude" value="{{ $lokasi->longitude }}"
                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                placeholder="123.059495" required autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label for="radius" class="block text-sm font-medium text-dark-700 mb-1.5">Radius (Meter)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class='bx bx-radar text-dark-400 text-lg'></i>
                            </div>
                            <input type="number" name="radius" id="radius" value="{{ $lokasi->radius }}"
                                class="block w-full pl-10 pr-3 py-2.5 border border-dark-200 rounded-xl bg-dark-50/50 focus:bg-white text-dark-800 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all duration-200"
                                placeholder="Contoh: 100" required autocomplete="off">
                        </div>
                        <p class="mt-2 text-xs text-dark-500 leading-relaxed">
                            Jarak maksimal siswa dapat melakukan absensi dalam hitungan meter dari titik lokasi.
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-dark-100">
                    <button type="submit"
                        class="w-full px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 transition-all duration-200 flex items-center justify-center gap-2">
                        <i class='bx bx-save text-lg'></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        {{-- Map Card --}}
        <div class="lg:col-span-2 glass-card rounded-2xl border border-dark-200/50 p-2 relative">
            <div id="map" class="shadow-inner"></div>
            
            <div class="absolute top-6 right-6 z-[400]">
                <div class="bg-white/90 backdrop-blur-md border border-dark-100 shadow-xl rounded-xl p-3 flex items-center gap-2">
                    <span class="flex h-3 w-3 relative">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-primary-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-dark-700">Area Absensi Valid</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initial variables
            let latInput = document.getElementById('latitude');
            let lngInput = document.getElementById('longitude');
            let radiusInput = document.getElementById('radius');
            
            let initLat = parseFloat(latInput.value) || -0.5413156;
            let initLng = parseFloat(lngInput.value) || 123.059495;
            let initRadius = parseFloat(radiusInput.value) || 100;

            // Initialize Map
            let map = L.map('map').setView([initLat, initLng], 17);

            // Add OpenStreetMap tile layer (Free)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Initialize Marker and Circle
            let marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
            let circle = L.circle([initLat, initLng], {
                color: '#6366f1', // primary-500
                fillColor: '#818cf8', // primary-400
                fillOpacity: 0.2,
                radius: initRadius
            }).addTo(map);

            // Function to update map and circle
            function updateMapComponents(lat, lng, rad) {
                let newLatLng = new L.LatLng(lat, lng);
                marker.setLatLng(newLatLng);
                circle.setLatLng(newLatLng);
                circle.setRadius(parseFloat(rad));
                map.panTo(newLatLng);
            }

            // Map Click Event
            map.on('click', function(e) {
                let lat = e.latlng.lat;
                let lng = e.latlng.lng;
                
                latInput.value = lat;
                lngInput.value = lng;
                
                updateMapComponents(lat, lng, radiusInput.value);
            });

            // Marker Drag Event
            marker.on('dragend', function(e) {
                let position = marker.getLatLng();
                
                latInput.value = position.lat;
                lngInput.value = position.lng;
                
                updateMapComponents(position.lat, position.lng, radiusInput.value);
            });

            // Input Change Events (Realtime map update)
            latInput.addEventListener('input', function() {
                updateMapComponents(this.value, lngInput.value, radiusInput.value);
            });
            
            lngInput.addEventListener('input', function() {
                updateMapComponents(latInput.value, this.value, radiusInput.value);
            });
            
            radiusInput.addEventListener('input', function() {
                updateMapComponents(latInput.value, lngInput.value, this.value);
            });

            // Form Submit Validation
            document.getElementById('frmLokasi').addEventListener('submit', function(e) {
                if (!latInput.value || !lngInput.value || !radiusInput.value) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Semua data wajib diisi!',
                        icon: 'warning',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Tutup',
                        customClass: { popup: 'font-inter rounded-2xl', confirmButton: 'rounded-xl' }
                    });
                }
            });
        });
    </script>
@endpush

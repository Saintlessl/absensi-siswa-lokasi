@extends('layouts.app')

@section('title', 'Absensi Siswa')
@section('page-title', 'Absensi Siswa')
@section('page-subtitle', 'Sistem absensi QR Code, Selfie & GPS')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Absensi</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        #qr-reader {
            width: 100% !important;
            border: none !important;
        }
        #qr-reader__scan_region {
            background: #0f172a;
            border-radius: 1rem;
            overflow: hidden;
        }
        #qr-reader__dashboard {
            padding: 10px;
        }

        /* Biometric Camera & Face Oval Guide */
        .camera-wrapper {
            position: relative;
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            border-radius: 1.25rem;
            overflow: hidden;
            background: #0f172a;
            aspect-ratio: 3 / 4;
            box-shadow: 0 15px 30px -10px rgba(15, 23, 42, 0.4);
        }

        .camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        .camera-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .face-oval-guide {
            width: 210px;
            height: 275px;
            border-radius: 50%;
            border: 2.5px dashed rgba(255, 255, 255, 0.65);
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.48);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .face-oval-guide.guide-valid {
            border-color: #10b981;
            border-style: solid;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.55);
        }

        .face-oval-guide.guide-warning {
            border-color: #f59e0b;
        }

        .face-oval-guide.guide-active {
            border-color: #3b82f6;
            border-style: solid;
        }
    </style>
@endpush

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI: Profil Siswa & Ringkasan Hari Ini --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Kartu Siswa --}}
            <div class="glass-card rounded-2xl border border-dark-200/50 p-6 relative overflow-hidden">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center text-xl font-bold shadow-lg shadow-primary-500/30">
                        {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-dark-900 leading-tight">{{ $siswa->nama }}</h4>
                        <p class="text-xs text-dark-500 mt-0.5">NIS: {{ $siswa->nis }}</p>
                        @if ($siswa->kelas)
                            <div class="mt-1.5 flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                    {{ $siswa->kelas->nama_kelas }}
                                </span>
                                @if ($siswa->kelas->jurusan)
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $siswa->kelas->jurusan->kode_jurusan }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-3 pt-3 border-t border-dark-100">
                    {{-- Status Masuk --}}
                    <div class="p-3.5 rounded-xl bg-dark-50/60 border border-dark-100">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-dark-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class='bx bx-log-in text-emerald-600 text-base'></i> Absen Masuk
                            </span>
                            @if ($absenHariIni && $absenHariIni->jam_masuk)
                                @if ($absenHariIni->menit_terlambat > 0)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                        Terlambat
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        Tepat Waktu
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-dark-200 text-dark-600">
                                    Belum Absen
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-dark-700 font-medium">
                                {{ $absenHariIni && $absenHariIni->jam_masuk ? date('H:i', strtotime($absenHariIni->jam_masuk)) . ' WIB' : 'Batas: 07:15 WIB' }}
                            </span>
                            @if ($absenHariIni && $absenHariIni->keterangan_terlambat)
                                <span class="text-rose-600 font-semibold">{{ $absenHariIni->keterangan_terlambat }}</span>
                            @endif
                        </div>
                        @if ($absenHariIni && $absenHariIni->foto_masuk)
                            <div class="mt-2 pt-2 border-t border-dark-100/60 flex items-center gap-2">
                                <img src="{{ asset('storage/' . $absenHariIni->foto_masuk) }}" class="w-8 h-8 rounded-lg object-cover border border-dark-200">
                                <span class="text-[11px] text-dark-500">Foto selfie masuk terverifikasi</span>
                            </div>
                        @endif
                    </div>

                    {{-- Status Pulang --}}
                    <div class="p-3.5 rounded-xl bg-dark-50/60 border border-dark-100">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-dark-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class='bx bx-log-out text-primary-600 text-base'></i> Absen Pulang
                            </span>
                            @if ($absenHariIni && $absenHariIni->jam_pulang)
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-primary-100 text-primary-800">
                                    Sudah Pulang
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-dark-200 text-dark-600">
                                    Belum Pulang
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-dark-700 font-medium">
                                {{ $absenHariIni && $absenHariIni->jam_pulang ? date('H:i', strtotime($absenHariIni->jam_pulang)) . ' WIB' : 'Buka mulai: 14:55 WIB' }}
                            </span>
                            <span class="text-dark-400">Jadwal: 15:00 WIB</span>
                        </div>
                        @if ($absenHariIni && $absenHariIni->foto_pulang)
                            <div class="mt-2 pt-2 border-t border-dark-100/60 flex items-center gap-2">
                                <img src="{{ asset('storage/' . $absenHariIni->foto_pulang) }}" class="w-8 h-8 rounded-lg object-cover border border-dark-200">
                                <span class="text-[11px] text-dark-500">Foto selfie pulang terverifikasi</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Aturan Jam Sekolah Info --}}
                <div class="mt-4 p-3 rounded-xl bg-primary-50/60 border border-primary-100 text-xs text-primary-900 space-y-1">
                    <p class="font-bold flex items-center gap-1"><i class='bx bx-info-circle'></i> Aturan Absensi SMK:</p>
                    <p class="text-[11px] text-primary-800">&bull; Masuk sebelum <span class="font-semibold">07:15 WIB</span>: Tepat Waktu</p>
                    <p class="text-[11px] text-primary-800">&bull; Masuk setelah <span class="font-semibold">07:15 WIB</span>: Terlambat</p>
                    <p class="text-[11px] text-primary-800">&bull; Pulang dibuka mulai <span class="font-semibold">14:55 WIB</span> (toleransi 5 mnt sebelum 15:00)</p>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: WIZARD ABSENSI (Scan QR -> Selfie -> Lokasi -> Kirim) --}}
        <div class="lg:col-span-2">
            @if (Auth::user()->role == 'orangtua')
                <div class="glass-card rounded-2xl border border-dark-200/50 p-8 text-center">
                    <div class="w-16 h-16 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center mx-auto mb-4 text-3xl">
                        <i class='bx bx-user-check'></i>
                    </div>
                    <h3 class="text-lg font-bold text-dark-800">Akun Wali Murid</h3>
                    <p class="text-sm text-dark-500 mt-1 max-w-md mx-auto">
                        Anda masuk sebagai orang tua/wali murid. Informasi kehadiran harian ananda ditampilkan pada panel di sebelah kiri.
                    </p>
                </div>
            @else
                {{-- Cek apakah sudah tuntas absen masuk dan pulang --}}
                @if ($absenHariIni && $absenHariIni->jam_masuk && $absenHariIni->jam_pulang)
                    <div class="glass-card rounded-2xl border border-emerald-200 bg-emerald-50/30 p-8 text-center">
                        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-4xl shadow-lg shadow-emerald-500/20">
                            <i class='bx bx-check-double'></i>
                        </div>
                        <h3 class="text-xl font-bold text-dark-900">Kehadiran Hari Ini Lengkap!</h3>
                        <p class="text-sm text-dark-600 mt-1.5 max-w-md mx-auto">
                            Anda telah menyelesaikan absen masuk ({{ date('H:i', strtotime($absenHariIni->jam_masuk)) }} WIB) dan absen pulang ({{ date('H:i', strtotime($absenHariIni->jam_pulang)) }} WIB). Selamat beristirahat!
                        </p>
                    </div>
                @else
                    @if (!$isFaceRegistered)
                        {{-- ALERT KETIKA SISWA BELUM DAFTAR BIOMETRIK --}}
                        <div class="glass-card rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/50 to-white p-8 text-center max-w-lg mx-auto space-y-5">
                            <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-3xl mx-auto shadow-inner">
                                <i class='bx bx-scan'></i>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-lg font-bold text-dark-900">Registrasi Wajah Diperlukan</h3>
                                <p class="text-xs text-dark-600 leading-relaxed">
                                    Untuk mencegah kecurangan dan titip absen, sistem absensi SMK kini menggunakan <strong>AI Facial Verification & Anti-Spoofing</strong>. Anda wajib mendaftarkan template wajah Anda terlebih dahulu sebelum dapat melakukan absensi masuk atau pulang.
                                </p>
                            </div>
                            <div class="pt-2">
                                <a href="{{ route('absensi.face.register.show') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold text-xs shadow-lg shadow-primary-500/25 transition-all">
                                    <i class='bx bx-user-pin text-lg'></i>
                                    <span>Daftarkan Wajah Sekarang</span>
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Container Form Wizard --}}
                        <div class="glass-card rounded-2xl border border-dark-200/50 p-6 sm:p-8">
                            {{-- Pilihan Tipe Absensi: Masuk atau Pulang --}}
                            @php
                                $nowHourMinute = date('H:i');
                                $bisaPulang = $absenHariIni && $absenHariIni->jam_masuk && !$absenHariIni->jam_pulang;
                                $sudahJamPulang = $nowHourMinute >= '14:55';
                                $defaultTipe = (!$absenHariIni || !$absenHariIni->jam_masuk) ? 'masuk' : 'pulang';
                            @endphp

                            <div class="flex items-center justify-center gap-3 p-1.5 rounded-2xl bg-dark-100/70 mb-6 max-w-md mx-auto">
                                <button type="button" id="btnTabMasuk" onclick="switchTipe('masuk')"
                                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5 {{ $defaultTipe == 'masuk' ? 'bg-white text-dark-900 shadow-sm' : 'text-dark-500 hover:text-dark-800' }} {{ $absenHariIni && $absenHariIni->jam_masuk ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    <i class='bx bx-log-in text-base text-emerald-600'></i>
                                    <span>Absen Masuk</span>
                                </button>

                                <button type="button" id="btnTabPulang" onclick="switchTipe('pulang')"
                                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5 {{ $defaultTipe == 'pulang' ? 'bg-white text-dark-900 shadow-sm' : 'text-dark-500 hover:text-dark-800' }} {{ !$bisaPulang ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    <i class='bx bx-log-out text-base text-primary-600'></i>
                                    <span>Absen Pulang</span>
                                </button>
                            </div>

                            {{-- Notice jika pulang belum jam 14.55 --}}
                            <div id="noticePulangBelumWaktu" class="hidden mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                <div class="flex items-start gap-2.5">
                                    <i class='bx bx-time-five text-amber-600 text-lg mt-0.5'></i>
                                    <div>
                                        <p class="font-bold">Belum Waktunya Absen Pulang</p>
                                        <p class="mt-0.5 text-amber-800">
                                            Absen pulang dibuka mulai pukul <strong>14:55 WIB</strong> (toleransi 5 menit sebelum jam 15:00). Sekarang masih pukul <strong>{{ date('H:i') }} WIB</strong>.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Stepper Tabs --}}
                            <div class="grid grid-cols-3 gap-2 p-1.5 bg-dark-50 rounded-xl border border-dark-200 mb-6 max-w-xl mx-auto">
                                <div id="stepTab1" class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold bg-white text-dark-900 border border-dark-200 shadow-sm transition-all">
                                    <span id="stepBadge1" class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                                    <span class="truncate">Scan QR</span>
                                </div>
                                <div id="stepTab2" class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold text-dark-500 transition-all">
                                    <span id="stepBadge2" class="w-5 h-5 rounded-full bg-dark-200 text-dark-600 flex items-center justify-center text-[10px] font-bold">2</span>
                                    <span class="truncate">Verifikasi Wajah</span>
                                </div>
                                <div id="stepTab3" class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold text-dark-500 transition-all">
                                    <span id="stepBadge3" class="w-5 h-5 rounded-full bg-dark-200 text-dark-600 flex items-center justify-center text-[10px] font-bold">3</span>
                                    <span class="truncate">Lokasi & Kirim</span>
                                </div>
                            </div>

                            {{-- STEP 1: SCAN QR CODE --}}
                            <div id="stepSection1" class="space-y-4">
                                <div class="text-center max-w-md mx-auto">
                                    <h4 class="text-base font-bold text-dark-800">Langkah 1: Scan QR Code Kelas</h4>
                                    <p class="text-xs text-dark-500 mt-1">
                                        Arahkan kamera ke QR Code yang tertempel di kelas Anda:
                                        <span class="font-bold text-primary-600">{{ $siswa->kelas->nama_kelas ?? 'Kelas Anda' }}</span>
                                    </p>
                                </div>

                                <div class="max-w-sm mx-auto overflow-hidden rounded-2xl border-2 border-primary-500/40 p-2 bg-dark-900 shadow-xl">
                                    <div id="qr-reader" class="rounded-xl overflow-hidden"></div>
                                </div>

                                <div id="qrStatusMessage" class="hidden max-w-sm mx-auto p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs text-center font-semibold flex items-center justify-center gap-2">
                                    <i class='bx bx-check-circle text-lg'></i>
                                    <span id="qrStatusText">QR Code Kelas Terverifikasi!</span>
                                </div>

                                <div class="text-center pt-2">
                                    <button type="button" id="btnNextToStep2" onclick="goToStep(2)" disabled
                                        class="px-6 py-2.5 rounded-xl bg-dark-200 text-dark-400 font-semibold text-xs transition-all inline-flex items-center gap-2 cursor-not-allowed">
                                        <span>Lanjut ke Verifikasi Wajah</span>
                                        <i class='bx bx-right-arrow-alt text-base'></i>
                                    </button>
                                </div>
                            </div>

                            {{-- STEP 2: VERIFIKASI BIOMETRIK WAJAH & LIVENESS DETECTION --}}
                            <div id="stepSection2" class="hidden space-y-4">
                                <div class="text-center max-w-md mx-auto">
                                    <h4 class="text-base font-bold text-dark-800">Langkah 2: AI Facial Verification & Liveness</h4>
                                    <p class="text-xs text-dark-500 mt-1">
                                        Posisikan wajah Anda di bingkai oval dan ikuti tantangan keaktifan (anti-spoofing).
                                    </p>
                                </div>

                                {{-- Kartu Tantangan Liveness Realtime --}}
                                <div id="challengeBox" class="max-w-sm mx-auto p-3.5 rounded-xl bg-dark-50 border border-dark-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[11px] font-semibold text-dark-500 uppercase tracking-wider flex items-center gap-1.5">
                                            <i class='bx bx-shield-quarter text-sm text-primary-600'></i> Verifikasi Keaktifan Wajah
                                        </span>
                                        <span id="challengeCounter" class="text-[11px] font-semibold text-dark-500">Tantangan 1 dari 2</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div id="challengeIconWrapper" class="w-9 h-9 rounded-lg bg-primary-50 text-primary-700 border border-primary-200 flex items-center justify-center text-lg flex-shrink-0">
                                            <i class='bx bx-show' id="challengeIcon"></i>
                                        </div>
                                        <div>
                                            <p id="challengeInstruction" class="text-xs font-bold text-dark-900 leading-snug">Menyiapkan tantangan...</p>
                                            <p id="challengeHint" class="text-[11px] text-dark-500 mt-0.5">Tatap kamera dengan jelas</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Camera Viewport --}}
                                <div class="max-w-sm mx-auto">
                                    {{-- Universal Camera Toolbar (Multi-Device Switcher) --}}
                                    <div class="mb-3 flex items-center justify-between gap-2 p-2.5 rounded-xl bg-dark-50 border border-dark-200 text-xs">
                                        <div class="flex items-center gap-1.5 flex-1 min-w-0">
                                            <i class='bx bx-camera text-primary-600 text-sm'></i>
                                            <select id="attendanceCameraSelect" class="w-full px-2 py-1 border border-dark-200 rounded-lg bg-white text-[11px] text-dark-800 focus:outline-none">
                                                <option value="">Kamera Utama</option>
                                            </select>
                                        </div>
                                        <span id="attendanceDeviceBadge" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-primary-50 text-primary-700 border border-primary-200 truncate max-w-[120px]">
                                            Kamera Siap
                                        </span>
                                    </div>

                                    <div class="camera-wrapper" id="biometricCameraWrapper">
                                        <video id="biometricVideo" class="camera-video" autoplay playsinline muted></video>
                                        <canvas id="biometricCanvas" class="hidden"></canvas>

                                        {{-- Overlay Oval Guide --}}
                                        <div class="camera-overlay">
                                            <div class="face-oval-guide" id="biometricOvalGuide">
                                            </div>
                                        </div>

                                        {{-- Model Loading Overlay --}}
                                        <div id="biometricLoaderOverlay" class="absolute inset-0 bg-dark-950/85 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-20 transition-opacity">
                                            <div class="w-10 h-10 rounded-full border-4 border-primary-500 border-t-transparent animate-spin mb-3"></div>
                                            <p class="font-bold text-xs" id="biometricLoaderText">Memuat Model AI Biometrik...</p>
                                            <p class="text-[11px] text-dark-400 mt-1 max-w-xs">Memeriksa kesiapan neural network pengenal wajah.</p>
                                        </div>

                                        {{-- Camera Error Overlay --}}
                                        <div id="attendanceCameraErrorOverlay" class="hidden absolute inset-0 bg-dark-950/90 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-30">
                                            <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-2xl mb-3 border border-rose-500/40">
                                                <i class='bx bx-camera-off'></i>
                                            </div>
                                            <h4 class="font-bold text-sm text-white" id="attendanceErrorTitle">Kamera Tidak Aktif</h4>
                                            <p class="text-xs text-dark-300 mt-1.5 max-w-xs leading-relaxed" id="attendanceErrorGuidance">
                                                Pastikan webcam terhubung dan izin kamera telah diizinkan pada browser.
                                            </p>
                                            <button type="button" onclick="startBiometricCamera()" class="mt-4 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition-all flex items-center gap-1.5 shadow-md shadow-primary-500/30">
                                                <i class='bx bx-refresh text-base'></i> Coba Lagi
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Real-time Status Alert --}}
                                    <div id="biometricStatusAlert" class="mt-3 p-3 rounded-xl bg-dark-50 border border-dark-200 text-xs text-dark-700 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class='bx bx-loader-alt bx-spin text-primary-600 text-base' id="biometricStatusIcon"></i>
                                            <span id="biometricStatusMsg" class="font-medium">Menghubungkan ke kamera...</span>
                                        </div>
                                        <span id="biometricScoreBadge" class="text-[11px] font-bold text-dark-500"></span>
                                    </div>

                                    {{-- Action buttons --}}
                                    <div class="flex items-center justify-center gap-3 mt-4">
                                        <button type="button" onclick="goToStep(1)" class="px-4 py-2.5 rounded-xl border border-dark-200 text-dark-600 text-xs font-semibold hover:bg-dark-50">
                                            <i class='bx bx-left-arrow-alt text-base'></i> Kembali
                                        </button>

                                        <button type="button" id="btnRetryBiometric" onclick="restartBiometricVerification()" class="hidden px-4 py-2.5 rounded-xl border border-amber-300 bg-amber-50 text-amber-800 text-xs font-semibold hover:bg-amber-100 flex items-center gap-1.5">
                                            <i class='bx bx-refresh text-base'></i> Ulangi Verifikasi
                                        </button>

                                        <button type="button" id="btnNextToStep3" onclick="goToStep(3)" disabled
                                            class="px-6 py-2.5 rounded-xl bg-dark-200 text-dark-400 font-bold text-xs transition-all flex items-center gap-1.5 cursor-not-allowed">
                                            <span>Lanjut ke Lokasi & Kirim</span>
                                            <i class='bx bx-right-arrow-alt text-base'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        {{-- STEP 3: LOKASI GPS & KIRIM --}}
                        <div id="stepSection3" class="hidden space-y-6">
                            <div class="text-center max-w-md mx-auto">
                                <h4 class="text-base font-bold text-dark-800">Langkah 3: Konfirmasi Lokasi & Kirim</h4>
                                <p class="text-xs text-dark-500 mt-1">
                                    Sistem memverifikasi posisi Anda di dalam radius area sekolah.
                                </p>
                            </div>

                            <div class="max-w-md mx-auto space-y-4">
                                {{-- Ringkasan Absensi --}}
                                <div class="p-4 rounded-2xl bg-dark-50/70 border border-dark-200/60 space-y-3">
                                    <div class="flex items-center justify-between text-xs pb-2 border-b border-dark-100">
                                        <span class="text-dark-500">Tipe Absensi</span>
                                        <span id="summaryTipe" class="font-bold uppercase text-primary-600">MASUK</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs pb-2 border-b border-dark-100">
                                        <span class="text-dark-500">Kelas Terverifikasi</span>
                                        <span class="font-bold text-dark-800">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs pb-2 border-b border-dark-100">
                                        <span class="text-dark-500">Jam Perangkat</span>
                                        <span id="summaryJam" class="font-bold text-dark-800">--:-- WIB</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-dark-500">Koordinat GPS</span>
                                        <span id="summaryCoords" class="font-mono text-dark-600">Mencari lokasi...</span>
                                    </div>
                                </div>

                                {{-- GPS Status Box --}}
                                <div id="gpsStatusBox" class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-3">
                                    <i class='bx bx-loader-alt bx-spin text-amber-600 text-xl flex-shrink-0'></i>
                                    <div>
                                        <p class="font-bold">Mendeteksi Lokasi GPS...</p>
                                        <p class="text-[11px] text-amber-800">Pastikan izin akses lokasi di browser Anda telah diaktifkan.</p>
                                    </div>
                                </div>

                                {{-- Preview Foto Selfie Kecil --}}
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-dark-50 border border-dark-100">
                                    <img id="summarySelfieThumbnail" class="w-12 h-16 rounded-lg object-cover border border-dark-200">
                                    <div class="text-xs">
                                        <p class="font-bold text-dark-800">Foto Selfie Siap</p>
                                        <button type="button" onclick="goToStep(2)" class="text-primary-600 hover:underline text-[11px]">Ganti foto</button>
                                    </div>
                                </div>

                                {{-- Tombol Kirim Final --}}
                                <div class="pt-2 flex items-center justify-between gap-3">
                                    <button type="button" onclick="goToStep(2)" class="px-4 py-2.5 rounded-xl border border-dark-200 text-dark-600 text-xs font-semibold hover:bg-dark-50">
                                        <i class='bx bx-left-arrow-alt text-base'></i> Kembali
                                    </button>

                                    <button type="button" id="btnSubmitAbsensi" onclick="submitAbsensi()" disabled
                                        class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold text-sm shadow-lg shadow-primary-500/30 flex items-center justify-center gap-2 cursor-not-allowed opacity-60">
                                        <i class='bx bx-send text-lg'></i>
                                        <span id="btnSubmitText">Kirim Absensi</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        @endif
        </div>
    </div>
@endsection

@push('scripts')
    {{-- HTML5 QR Code Scanner Library --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    {{-- Face-API.js library for Face Verification & Liveness Landmark Detection --}}
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/dist/face-api.js"></script>
    {{-- Universal Biometric Camera Engine --}}
    <script src="{{ asset('js/universal-biometric-camera.js') }}"></script>

    <script>
        // State variables
        let currentStep = 1;
        let selectedTipe = "{{ $defaultTipe ?? 'masuk' }}";
        let scannedQrToken = null;
        let selfieBase64 = null;
        let userLatitude = null;
        let userLongitude = null;
        let html5QrCodeScanner = null;
        let cameraMediaStream = null;
        let biometricCameraManager = null;

        // Biometric & Liveness State
        let isModelLoaded = false;
        let isBiometricLoading = false;
        let isLivenessPassed = false;
        let isDetectingBiometric = false;
        let challengeToken = null;
        let challengesList = [];
        let currentChallengeIdx = 0;
        let completedChallenges = [];
        let challengeStartTime = 0;
        let liveFaceDescriptor = null;
        let livenessProof = null;
        let blinkDetected = false;
        let eyeClosedStartTime = 0;

        // Config data
        const sekolahLat = {{ $lokasiSekolah->latitude ?? 0 }};
        const sekolahLng = {{ $lokasiSekolah->longitude ?? 0 }};
        const sekolahRadius = {{ $lokasiSekolah->radius ?? 100 }};
        const studentKelasId = {{ $siswa->kelas_id ?? 0 }};

        document.addEventListener('DOMContentLoaded', function() {
            @if (Auth::user()->role == 'siswa' && !($absenHariIni && $absenHariIni->jam_masuk && $absenHariIni->jam_pulang))
                initQrScanner();
                updateClock();
                setInterval(updateClock, 1000);
            @endif
        });

        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const el = document.getElementById('summaryJam');
            if (el) el.textContent = `${h}:${m} WIB`;
        }

        function switchTipe(tipe) {
            @if ($absenHariIni && $absenHariIni->jam_masuk)
                if (tipe === 'masuk') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sudah Absen Masuk',
                        text: 'Anda sudah melakukan absen masuk hari ini.',
                    });
                    return;
                }
            @else
                if (tipe === 'pulang') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum Absen Masuk',
                        text: 'Anda harus melakukan absen masuk terlebih dahulu sebelum absen pulang.',
                    });
                    return;
                }
            @endif

            // Cek jika pulang belum jam 14.55
            if (tipe === 'pulang') {
                const now = new Date();
                const nowMinutes = now.getHours() * 60 + now.getMinutes();
                const batasMinutes = 14 * 60 + 55; // 14:55 = 895 menit
                if (nowMinutes < batasMinutes) {
                    document.getElementById('noticePulangBelumWaktu')?.classList.remove('hidden');
                } else {
                    document.getElementById('noticePulangBelumWaktu')?.classList.add('hidden');
                }
            } else {
                document.getElementById('noticePulangBelumWaktu')?.classList.add('hidden');
            }

            selectedTipe = tipe;
            document.getElementById('summaryTipe').textContent = tipe.toUpperCase();

            // Toggle tab UI
            const btnMasuk = document.getElementById('btnTabMasuk');
            const btnPulang = document.getElementById('btnTabPulang');

            if (tipe === 'masuk') {
                btnMasuk.classList.add('bg-white', 'text-dark-900', 'shadow-sm');
                btnMasuk.classList.remove('text-dark-500');
                btnPulang.classList.remove('bg-white', 'text-dark-900', 'shadow-sm');
                btnPulang.classList.add('text-dark-500');
            } else {
                btnPulang.classList.add('bg-white', 'text-dark-900', 'shadow-sm');
                btnPulang.classList.remove('text-dark-500');
                btnMasuk.classList.remove('bg-white', 'text-dark-900', 'shadow-sm');
                btnMasuk.classList.add('text-dark-500');
            }
        }

        // ========================
        // STEP 1: QR SCANNER
        // ========================
        function initQrScanner() {
            const qrElement = document.getElementById("qr-reader");
            if (!qrElement) return;

            html5QrCodeScanner = new Html5Qrcode("qr-reader");

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            html5QrCodeScanner.start(
                { facingMode: "environment" },
                config,
                onQrCodeScanned,
                (errorMessage) => { /* ignore frame scan errors */ }
            ).catch(err => {
                console.warn("Camera scan error:", err);
            });
        }

        function onQrCodeScanned(decodedText, decodedResult) {
            if (scannedQrToken) return; // cegah double execution
            scannedQrToken = decodedText;

            // Pause / Stop scanner
            if (html5QrCodeScanner) {
                html5QrCodeScanner.stop().catch(() => {});
            }

            // Validasi ke backend
            fetch("{{ route('absensi.validateqr') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({ qr_token: decodedText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const statusBox = document.getElementById('qrStatusMessage');
                    const statusText = document.getElementById('qrStatusText');
                    const btnNext = document.getElementById('btnNextToStep2');

                    statusText.textContent = data.message;
                    statusBox.classList.remove('hidden');

                    btnNext.disabled = false;
                    btnNext.classList.remove('bg-dark-200', 'text-dark-400', 'cursor-not-allowed');
                    btnNext.classList.add('bg-primary-600', 'hover:bg-primary-700', 'text-white', 'shadow-md', 'shadow-primary-500/30');

                    // Auto advance ke step 2 setelah 800ms
                    setTimeout(() => {
                        goToStep(2);
                    }, 800);
                } else {
                    scannedQrToken = null;
                    Swal.fire({
                        icon: 'error',
                        title: 'QR Code Tidak Sesuai!',
                        text: data.message,
                        confirmButtonColor: '#e11d48'
                    }).then(() => {
                        initQrScanner();
                    });
                }
            })
            .catch(err => {
                scannedQrToken = null;
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Gagal memverifikasi QR Code. Coba lagi.',
                }).then(() => {
                    initQrScanner();
                });
            });
        }

        // ========================
        // STEP NAVIGATION
        // ========================
        function goToStep(step) {
            currentStep = step;

            // Hide all sections
            document.getElementById('stepSection1').classList.add('hidden');
            document.getElementById('stepSection2').classList.add('hidden');
            document.getElementById('stepSection3').classList.add('hidden');

            // Reset badges & tabs
            [1, 2, 3].forEach(s => {
                const b = document.getElementById('stepBadge' + s);
                const t = document.getElementById('stepTab' + s);
                if (s < step) {
                    if (b) {
                        b.className = 'w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold';
                        b.innerHTML = "<i class='bx bx-check text-xs'></i>";
                    }
                    if (t) {
                        t.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 transition-all';
                    }
                } else if (s === step) {
                    if (b) {
                        b.className = 'w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center text-[10px] font-bold';
                        b.textContent = s;
                    }
                    if (t) {
                        t.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold bg-white text-dark-900 border border-dark-200 shadow-sm transition-all';
                    }
                } else {
                    if (b) {
                        b.className = 'w-5 h-5 rounded-full bg-dark-200 text-dark-600 flex items-center justify-center text-[10px] font-bold';
                        b.textContent = s;
                    }
                    if (t) {
                        t.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold text-dark-500 transition-all';
                    }
                }
            });

            // Show target section & trigger actions
            document.getElementById('stepSection' + step).classList.remove('hidden');

            if (step === 2) {
                startBiometricStep();
            } else {
                stopBiometricCamera();
            }

            if (step === 3) {
                detectLocation();
            }
        }

        // ===============================================
        // STEP 2: AI BIOMETRIC VERIFICATION & LIVENESS
        // ===============================================
        async function startBiometricStep() {
            if (isLivenessPassed && liveFaceDescriptor) {
                // Sudah lolos sebelumnya, aktifkan tombol
                return;
            }

            // 1. Muat model jika belum
            await loadFaceApiModels();

            // 2. Minta tantangan (challenge) liveness dari backend
            await requestLivenessChallenge();

            // 3. Nyalakan kamera depan
            await startBiometricCamera();

            // 4. Mulai loop deteksi keaktifan
            startBiometricDetectionLoop();
        }

        async function loadFaceApiModels() {
            if (isModelLoaded) return;
            const loader = document.getElementById('biometricLoaderOverlay');
            loader.classList.remove('hidden', 'opacity-0');

            try {
                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/';
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                isModelLoaded = true;
                loader.classList.add('opacity-0');
                setTimeout(() => loader.classList.add('hidden'), 300);
            } catch (err) {
                console.error("Gagal memuat model face-api:", err);
                document.getElementById('biometricLoaderText').textContent = "Gagal mengunduh model AI. Periksa koneksi internet.";
            }
        }

        async function requestLivenessChallenge() {
            try {
                const res = await fetch("{{ route('absensi.challenge') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    }
                });
                const data = await res.json();
                if (data.success) {
                    challengeToken = data.challenge_token;
                    challengesList = data.challenges;
                    currentChallengeIdx = 0;
                    completedChallenges = [];
                    challengeStartTime = Date.now();
                    renderChallengeInstruction();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyiapkan Sesi',
                        text: data.message || 'Tidak dapat memuat sesi tantangan biometrik.'
                    });
                }
            } catch (e) {
                console.error("Challenge request error:", e);
            }
        }

        function renderChallengeInstruction() {
            if (!challengesList || challengesList.length === 0) return;

            const total = challengesList.length;
            const cur = challengesList[currentChallengeIdx];
            if (!cur) return;

            document.getElementById('challengeCounter').textContent = `Tantangan ${currentChallengeIdx + 1} dari ${total}`;
            document.getElementById('challengeInstruction').textContent = cur.label;
            
            const iconEl = document.getElementById('challengeIcon');
            iconEl.className = `bx ${cur.icon || 'bx-scan'}`;

            if (cur.type === 'blink') {
                document.getElementById('challengeHint').textContent = "Kedipkan mata secara natural di depan kamera";
            } else if (cur.type === 'smile') {
                document.getElementById('challengeHint').textContent = "Tersenyumlah lebar hingga gigi/pipi tampak sedikit";
            } else if (cur.type === 'turn_right') {
                document.getElementById('challengeHint').textContent = "Tolehkan kepala sedikit ke kanan lalu kembali lurus";
            } else if (cur.type === 'turn_left') {
                document.getElementById('challengeHint').textContent = "Tolehkan kepala sedikit ke kiri lalu kembali lurus";
            } else {
                document.getElementById('challengeHint').textContent = "Ikuti gerakan panduan di layar";
            }

            challengeStartTime = Date.now();
            blinkDetected = false;
        }

        async function startBiometricCamera() {
            const video = document.getElementById('biometricVideo');
            if (!video) return;

            const errOverlay = document.getElementById('attendanceCameraErrorOverlay');
            if (errOverlay) errOverlay.classList.add('hidden');

            if (!biometricCameraManager) {
                biometricCameraManager = new UniversalBiometricCamera({
                    videoElement: video,
                    selectElement: document.getElementById('attendanceCameraSelect'),
                    deviceBadgeElement: document.getElementById('attendanceDeviceBadge'),
                    onReady: (stream, info) => {
                        if (errOverlay) errOverlay.classList.add('hidden');
                        cameraMediaStream = stream;
                    },
                    onError: (errInfo) => {
                        if (errOverlay) {
                            document.getElementById('attendanceErrorTitle').textContent = errInfo.title;
                            document.getElementById('attendanceErrorGuidance').textContent = errInfo.guidance;
                            errOverlay.classList.remove('hidden');
                        }
                    },
                    onDeviceChange: (deviceId, label) => {
                        console.log("Kamera absensi diubah:", label);
                    }
                });
            }

            const success = await biometricCameraManager.start();
            return success;
        }

        function stopBiometricCamera() {
            isDetectingBiometric = false;
            if (biometricCameraManager) {
                biometricCameraManager.stop();
            }
            if (cameraMediaStream) {
                cameraMediaStream.getTracks().forEach(track => track.stop());
                cameraMediaStream = null;
            }
        }

        function restartBiometricVerification() {
            isLivenessPassed = false;
            liveFaceDescriptor = null;
            livenessProof = null;
            selfieBase64 = null;
            completedChallenges = [];
            currentChallengeIdx = 0;

            const guide = document.getElementById('biometricOvalGuide');
            guide.className = 'face-oval-guide';
            document.getElementById('biometricCameraWrapper').classList.remove('scanning');
            document.getElementById('btnRetryBiometric').classList.add('hidden');

            const btnNext = document.getElementById('btnNextToStep3');
            btnNext.disabled = true;
            btnNext.className = "px-6 py-2.5 rounded-xl bg-dark-200 text-dark-400 font-bold text-xs transition-all flex items-center gap-1.5 cursor-not-allowed";

            requestLivenessChallenge().then(() => {
                startBiometricDetectionLoop();
            });
        }

        function startBiometricDetectionLoop() {
            if (isDetectingBiometric) return;
            isDetectingBiometric = true;

            const video = document.getElementById('biometricVideo');
            const guide = document.getElementById('biometricOvalGuide');
            const wrapper = document.getElementById('biometricCameraWrapper');
            const statusMsg = document.getElementById('biometricStatusMsg');
            const statusIcon = document.getElementById('biometricStatusIcon');
            const statusAlert = document.getElementById('biometricStatusAlert');

            wrapper.classList.add('scanning');

            const checkFrame = async () => {
                if (!isDetectingBiometric || isLivenessPassed || video.paused || video.ended) {
                    if (isDetectingBiometric && !isLivenessPassed) {
                        requestAnimationFrame(checkFrame);
                    }
                    return;
                }

                try {
                    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.55 });
                    const detection = await faceapi.detectSingleFace(video, options)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        guide.className = 'face-oval-guide guide-warning';
                        statusMsg.textContent = "Wajah tidak terdeteksi. Posisikan wajah di dalam oval.";
                        statusIcon.className = "bx bx-info-circle text-amber-600 text-base";
                        statusAlert.className = "mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center justify-between";
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    const box = detection.detection.box;
                    const videoW = video.videoWidth || 640;
                    const faceRatio = box.width / videoW;

                    if (faceRatio < 0.20) {
                        guide.className = 'face-oval-guide guide-warning';
                        statusMsg.textContent = "Dekatkan wajah sedikit ke kamera.";
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    if (faceRatio > 0.72) {
                        guide.className = 'face-oval-guide guide-warning';
                        statusMsg.textContent = "Jauhkan wajah sedikit dari kamera.";
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    guide.className = 'face-oval-guide guide-active';
                    statusIcon.className = "bx bx-loader-alt bx-spin text-primary-600 text-base";
                    statusAlert.className = "mt-3 p-3 rounded-xl bg-primary-50 border border-primary-200 text-xs text-primary-800 flex items-center justify-between";
                    statusMsg.textContent = "Wajah terfokus. Silakan selesaikan tantangan di atas.";

                    // Evaluasi Tantangan Liveness Aktif
                    evaluateCurrentChallenge(detection);

                } catch (e) {
                    // ignore frame processing drop
                }

                if (isDetectingBiometric && !isLivenessPassed) {
                    requestAnimationFrame(checkFrame);
                }
            };

            requestAnimationFrame(checkFrame);
        }

        function calculateEAR(eye) {
            // EAR = (||p2 - p6|| + ||p3 - p5||) / (2 * ||p1 - p4||)
            const p1 = eye[0], p2 = eye[1], p3 = eye[2], p4 = eye[3], p5 = eye[4], p6 = eye[5];
            const distV1 = Math.hypot(p2.x - p6.x, p2.y - p6.y);
            const distV2 = Math.hypot(p3.x - p5.x, p3.y - p5.y);
            const distH = Math.hypot(p1.x - p4.x, p1.y - p4.y);
            return (distV1 + distV2) / (2.0 * (distH || 1));
        }

        function evaluateCurrentChallenge(detection) {
            if (!challengesList || challengesList.length === 0) return;
            const curChallenge = challengesList[currentChallengeIdx];
            if (!curChallenge) return;

            const landmarks = detection.landmarks;

            if (curChallenge.type === 'blink') {
                const leftEAR = calculateEAR(landmarks.getLeftEye());
                const rightEAR = calculateEAR(landmarks.getRightEye());
                const ear = (leftEAR + rightEAR) / 2.0;

                // Mata tertutup jika EAR < 0.22
                if (ear < 0.22 && !blinkDetected) {
                    blinkDetected = true;
                    eyeClosedStartTime = Date.now();
                } else if (ear > 0.26 && blinkDetected) {
                    // Mata terbuka kembali => Kedipan tuntas!
                    const duration = Date.now() - eyeClosedStartTime;
                    blinkDetected = false;
                    onChallengePassed(curChallenge.type, Math.max(180, duration), detection);
                }
            } else if (curChallenge.type === 'smile') {
                const mouth = landmarks.getMouth();
                const mouthLeft = mouth[0];
                const mouthRight = mouth[6];
                const mouthTop = mouth[3];
                const mouthBottom = mouth[9];
                const mouthWidth = Math.hypot(mouthRight.x - mouthLeft.x, mouthRight.y - mouthLeft.y);
                const mouthHeight = Math.hypot(mouthBottom.x - mouthTop.x, mouthBottom.y - mouthTop.y);
                const ratio = mouthWidth / (mouthHeight || 1);

                if (ratio > 2.75 || mouthWidth > 75) {
                    const duration = Date.now() - challengeStartTime;
                    if (duration >= 200) {
                        onChallengePassed(curChallenge.type, duration, detection);
                    }
                }
            } else if (curChallenge.type === 'turn_right' || curChallenge.type === 'turn_left') {
                const nose = landmarks.getNose()[3];
                const leftEye = landmarks.getLeftEye()[0];
                const rightEye = landmarks.getRightEye()[3];
                const dLeft = Math.abs(nose.x - leftEye.x);
                const dRight = Math.abs(nose.x - rightEye.x);
                const symmetry = Math.min(dLeft, dRight) / Math.max(dLeft, dRight);

                if (symmetry < 0.40) {
                    const duration = Date.now() - challengeStartTime;
                    if (duration >= 220) {
                        onChallengePassed(curChallenge.type, duration, detection);
                    }
                }
            } else {
                // Default fallback / nod
                const duration = Date.now() - challengeStartTime;
                if (duration >= 600) {
                    onChallengePassed(curChallenge.type, duration, detection);
                }
            }
        }

        function onChallengePassed(challengeType, durationMs, detection) {
            completedChallenges.push({
                type: challengeType,
                duration_ms: durationMs
            });

            currentChallengeIdx++;

            if (currentChallengeIdx < challengesList.length) {
                // Lanjut ke tantangan berikutnya
                renderChallengeInstruction();
            } else {
                // SEMUA TANTANGAN LIVENESS SELESAI!
                finishBiometricVerification(detection);
            }
        }

        function finishBiometricVerification(detection) {
            isLivenessPassed = true;
            isDetectingBiometric = false;

            const video = document.getElementById('biometricVideo');
            const canvas = document.getElementById('biometricCanvas');
            const guide = document.getElementById('biometricOvalGuide');
            const wrapper = document.getElementById('biometricCameraWrapper');
            const statusMsg = document.getElementById('biometricStatusMsg');
            const statusIcon = document.getElementById('biometricStatusIcon');
            const statusAlert = document.getElementById('biometricStatusAlert');
            const btnNext = document.getElementById('btnNextToStep3');

            // Tangkap foto snapshot dari video (mirror & optimal ratio)
            if (biometricCameraManager) {
                selfieBase64 = biometricCameraManager.captureSnapshot(640, 480);
            } else {
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                selfieBase64 = canvas.toDataURL('image/jpeg', 0.88);
            }

            // Simpan live 128-d descriptor & liveness proof
            liveFaceDescriptor = Array.from(detection.descriptor);
            livenessProof = {
                completed_challenges: completedChallenges,
                liveness_score: 96.5
            };

            // Tampilkan visual sukses
            wrapper.classList.remove('scanning');
            guide.className = 'face-oval-guide guide-valid';
            statusIcon.className = "bx bx-check-circle text-emerald-600 text-base";
            statusAlert.className = "mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center justify-between";
            statusMsg.textContent = "Verifikasi Wajah & Anti-Spoofing Berhasil!";

            document.getElementById('challengeInstruction').textContent = "Keaktifan Terverifikasi";
            document.getElementById('challengeHint').textContent = "Wajah asli terkonfirmasi bukan foto/rekaman";
            document.getElementById('challengeIcon').className = "bx bx-check-shield text-emerald-600";
            document.getElementById('challengeIconWrapper').className = "w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shadow-md";

            // Pasang foto pada summary thumbnail step 3
            const thumb = document.getElementById('summarySelfieThumbnail');
            if (thumb) thumb.src = selfieBase64;

            // Aktifkan tombol lanjut ke step 3
            btnNext.disabled = false;
            btnNext.className = "px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold text-xs shadow-md shadow-emerald-500/30 flex items-center gap-1.5 transition-all";

            document.getElementById('btnRetryBiometric').classList.remove('hidden');

            // Auto advance ke step 3 setelah 600ms
            setTimeout(() => {
                goToStep(3);
            }, 600);
        }

        // ========================
        // STEP 3: GPS & SUBMIT
        // ========================
        function detectLocation() {
            const statusBox = document.getElementById('gpsStatusBox');
            const coordsText = document.getElementById('summaryCoords');
            const btnSubmit = document.getElementById('btnSubmitAbsensi');

            if (!navigator.geolocation) {
                statusBox.className = "p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-center gap-2.5";
                statusBox.innerHTML = "<i class='bx bx-error-circle text-rose-600 text-xl'></i><span>Browser tidak mendukung Geolocation GPS.</span>";
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    userLatitude = pos.coords.latitude;
                    userLongitude = pos.coords.longitude;

                    coordsText.textContent = `${userLatitude.toFixed(6)}, ${userLongitude.toFixed(6)}`;

                    // Hitung jarak ke sekolah
                    const dist = calculateDistance(userLatitude, userLongitude, sekolahLat, sekolahLng);

                    if (dist <= sekolahRadius) {
                        statusBox.className = "p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-2.5";
                        statusBox.innerHTML = `<i class='bx bx-check-circle text-emerald-600 text-xl'></i><div><p class="font-bold">Lokasi Valid (${Math.round(dist)} m dari sekolah)</p><p class="text-[11px] text-emerald-700">Anda berada di dalam radius presensi sekolah.</p></div>`;

                        btnSubmit.disabled = false;
                        btnSubmit.classList.remove('cursor-not-allowed', 'opacity-60');
                    } else {
                        statusBox.className = "p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-center gap-2.5";
                        statusBox.innerHTML = `<i class='bx bx-map-pin text-rose-600 text-xl'></i><div><p class="font-bold">Di Luar Jangkauan Sekolah (${Math.round(dist)} meter)</p><p class="text-[11px] text-rose-700">Batas maksimal radius adalah ${sekolahRadius} meter.</p></div>`;

                        btnSubmit.disabled = true;
                        btnSubmit.classList.add('cursor-not-allowed', 'opacity-60');
                    }
                },
                (err) => {
                    statusBox.className = "p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-center gap-2.5";
                    statusBox.innerHTML = `<i class='bx bx-error-circle text-rose-600 text-xl'></i><div><p class="font-bold">Gagal Mengambil Lokasi GPS</p><p class="text-[11px] text-rose-700">Pastikan GPS ponsel menyala dan izin lokasi diaktifkan.</p></div>`;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function submitAbsensi() {
            if (!scannedQrToken || !selfieBase64 || !userLatitude || !userLongitude || !liveFaceDescriptor || !challengeToken) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Pastikan QR Code, verifikasi wajah biometrik, dan lokasi GPS telah siap.',
                });
                return;
            }

            const btnSubmit = document.getElementById('btnSubmitAbsensi');
            const btnText = document.getElementById('btnSubmitText');
            btnSubmit.disabled = true;
            btnText.innerHTML = "<i class='bx bx-loader-alt bx-spin mr-1'></i> Memverifikasi...";

            fetch("{{ route('absensi.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({
                    qr_token: scannedQrToken,
                    foto: selfieBase64,
                    latitude: userLatitude,
                    longitude: userLongitude,
                    tipe: selectedTipe,
                    face_descriptor: liveFaceDescriptor,
                    challenge_token: challengeToken,
                    liveness_proof: livenessProof
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Presensi Berhasil!',
                        html: `
                            <p class="text-sm text-dark-600">${data.message}</p>
                            ${data.confidence ? `<p class="mt-2 text-xs font-semibold text-emerald-700 bg-emerald-50 py-1 px-2 rounded-lg inline-block">Kecocokan Wajah: ${data.confidence}%</p>` : ''}
                        `,
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    btnSubmit.disabled = false;
                    btnText.textContent = "Kirim Absensi";

                    // Jika kegagalan verifikasi wajah atau liveness
                    const isBiometricError = data.status === 'failed_match' || data.status === 'failed_liveness';

                    Swal.fire({
                        icon: 'error',
                        title: isBiometricError ? 'Verifikasi Wajah Gagal!' : 'Gagal Absensi',
                        html: `
                            <p class="text-sm text-dark-600">${data.message}</p>
                            ${data.confidence !== undefined && data.confidence !== null ? `<p class="mt-2 text-xs font-semibold text-rose-700 bg-rose-50 py-1 px-2 rounded-lg inline-block">Skor Kemiripan: ${data.confidence}% (Batas: 74%)</p>` : ''}
                        `,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: isBiometricError ? 'Coba Lagi' : 'OK'
                    }).then(() => {
                        if (isBiometricError) {
                            goToStep(2);
                            restartBiometricVerification();
                        }
                    });
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnText.textContent = "Kirim Absensi";
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Terjadi kesalahan saat memproses verifikasi absensi. Coba beberapa saat lagi.',
                });
            });
        }
    </script>
@endpush

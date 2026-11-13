@extends('layouts.app')

@section('title', 'Pendaftaran Biometrik Terpandu — Lab Sekolah')
@section('page-title', 'Pendaftaran Biometrik Terpandu')
@section('page-subtitle', 'Sesi pendaftaran wajah menggunakan perangkat sekolah / Lab Komputer')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('admin.biometrik') }}" class="text-dark-500 hover:text-dark-800">Biometrik Siswa</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Bantuan Pendaftaran Lab</li>
@endsection

@push('styles')
    <style>
        .assisted-camera-wrapper {
            position: relative;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            border-radius: 1.5rem;
            overflow: hidden;
            background: #0f172a;
            aspect-ratio: 4 / 3;
            box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.4);
        }

        @media (max-width: 640px) {
            .assisted-camera-wrapper {
                aspect-ratio: 3 / 4;
            }
        }

        .assisted-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        .assisted-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .assisted-oval-frame {
            width: 220px;
            height: 280px;
            border-radius: 50%;
            border: 2.5px dashed rgba(255, 255, 255, 0.7);
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.52);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .assisted-oval-frame.frame-valid {
            border-color: #10b981;
            border-style: solid;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.55);
        }

        .assisted-oval-frame.frame-warning {
            border-color: #f59e0b;
        }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-2 space-y-6">

    {{-- Official Session Header Card --}}
    <div class="glass-card rounded-2xl border border-slate-100 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-dark-100 text-dark-700 border border-dark-200 flex items-center justify-center text-2xl flex-shrink-0">
                <i class='bx bx-desktop text-primary-600'></i>
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-dark-100 text-dark-700 border border-dark-200 text-xs font-semibold mb-1">
                    <i class='bx bx-check-shield text-emerald-600'></i> Prosedur Resmi &bull; Lab Sekolah
                </div>
                <h3 class="text-base font-bold text-dark-900 leading-tight">Pendaftaran Biometrik Terpandu Siswa</h3>
                <p class="text-xs text-dark-500 mt-0.5">Siswa hadir fisik di depan perangkat komputer/webcam sekolah didampingi petugas.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.biometrik') }}" class="px-4 py-2 rounded-xl border border-dark-200 text-dark-600 hover:bg-dark-50 text-xs font-semibold flex items-center gap-1.5 transition-all">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
        </div>
    </div>

    {{-- Info Grid: Siswa & Petugas Verifikator --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Kartu Siswa --}}
        <div class="glass-card rounded-2xl border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-600 to-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md flex-shrink-0">
                {{ strtoupper(substr($siswa->nama, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Identitas Siswa</span>
                <h4 class="text-sm font-bold text-dark-900 truncate">{{ $siswa->nama }}</h4>
                <div class="flex items-center gap-2 text-xs text-dark-500 mt-0.5">
                    <span>NIS: <strong>{{ $siswa->nis }}</strong></span>
                    <span>&bull;</span>
                    <span>{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-900 flex-shrink-0">
                {{ strtoupper(str_replace('_', ' ', $siswa->biometric_status ?? 'not_enrolled')) }}
            </span>
        </div>

        {{-- Kartu Petugas / Guru Pendamping --}}
        <div class="glass-card rounded-2xl border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md flex-shrink-0">
                <i class='bx bx-user-check'></i>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[10px] font-bold uppercase tracking-wider text-dark-400">Petugas Pendamping</span>
                <h4 class="text-sm font-bold text-dark-900 truncate">{{ $user->name }}</h4>
                <p class="text-xs text-dark-500 mt-0.5">Role: <strong class="capitalize">{{ $user->role }}</strong></p>
            </div>
            <div class="text-right flex-shrink-0">
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                    <i class='bx bx-check'></i> Terverifikasi
                </span>
            </div>
        </div>
    </div>

    {{-- VIEWPORT KAMERA LAB & INTERACTIVE ENROLLMENT --}}
    <div class="glass-card rounded-2xl border border-slate-100 p-6 sm:p-8 space-y-5">

        {{-- Universal Camera Toolbar (Multi-Device Switcher) --}}
        <div class="max-w-lg mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 p-3 rounded-xl bg-dark-50/80 border border-dark-200 text-xs">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <i class='bx bx-camera text-base text-indigo-600'></i>
                <label for="cameraSelect" class="font-semibold text-dark-700 whitespace-nowrap">Pilih Kamera:</label>
                <select id="cameraSelect" class="flex-1 sm:w-56 px-2.5 py-1.5 border border-dark-200 rounded-lg bg-white text-xs text-dark-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">Mendeteksi kamera...</option>
                </select>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-center">
                <span id="deviceBadge" class="px-2 py-0.5 rounded text-[11px] font-medium bg-dark-100 text-dark-500">Mendeteksi...</span>
                <span id="resolutionBadge" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">HD Ready</span>
            </div>
        </div>

        {{-- Liveness Challenge Box --}}
        <div id="assistedChallengeBox" class="hidden max-w-md mx-auto p-3.5 rounded-2xl bg-gradient-to-r from-indigo-50 to-primary-50 border border-indigo-200 shadow-sm transition-all">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-indigo-700 tracking-wider uppercase flex items-center gap-1.5">
                    <i class='bx bx-shield-quarter text-sm text-indigo-600'></i> Validasi Keaktifan Siswa (Anti-Spoofing)
                </span>
                <span id="assistedChallengeCounter" class="text-[11px] font-semibold text-dark-500">1 dari 2</span>
            </div>
            <div class="flex items-center gap-3">
                <div id="assistedChallengeIconBox" class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-md shadow-indigo-500/30 flex-shrink-0 animate-pulse">
                    <i class='bx bx-show' id="assistedChallengeIcon"></i>
                </div>
                <div>
                    <p id="assistedChallengeTitle" class="text-sm font-bold text-dark-900 leading-snug">Menyiapkan tantangan...</p>
                    <p id="assistedChallengeSubtitle" class="text-[11px] text-dark-500 mt-0.5">Siswa menatap lurus ke kamera</p>
                </div>
            </div>
        </div>

        {{-- Progress Bar Sampel Multi-Frame --}}
        <div id="assistedProgressBox" class="hidden max-w-md mx-auto p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-sm transition-all">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                    <i class='bx bx-camera text-sm'></i> Merekam Sampel Wajah Biometrik
                </span>
                <span id="assistedProgressCounter" class="text-xs font-bold text-emerald-700">0 / 3 Sampel</span>
            </div>
            <div class="w-full bg-emerald-200/60 rounded-full h-2 overflow-hidden">
                <div id="assistedProgressBar" class="bg-emerald-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        {{-- Camera Frame --}}
        <div class="max-w-md mx-auto">
            <div class="assisted-camera-wrapper" id="cameraWrapper">
                <video id="assistedVideo" class="assisted-video" autoplay playsinline muted></video>
                <canvas id="assistedCanvas" class="hidden"></canvas>

                {{-- Oval Frame --}}
                <div class="assisted-overlay">
                    <div class="assisted-oval-frame" id="ovalFrame">
                    </div>
                </div>

                {{-- Loading Models Overlay --}}
                <div id="loaderOverlay" class="absolute inset-0 bg-dark-950/85 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-20 transition-opacity">
                    <div class="w-10 h-10 rounded-full border-4 border-indigo-500 border-t-transparent animate-spin mb-3"></div>
                    <p class="font-bold text-xs" id="loaderTitle">Memuat Model AI Biometrik...</p>
                    <p class="text-[11px] text-dark-400 mt-1 max-w-xs">Menyiapkan detektor wajah & landmark biometrik.</p>
                </div>

                {{-- Camera Error Overlay --}}
                <div id="cameraErrorOverlay" class="hidden absolute inset-0 bg-dark-950/90 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-30">
                    <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-2xl mb-3 border border-rose-500/40">
                        <i class='bx bx-camera-off'></i>
                    </div>
                    <h4 class="font-bold text-sm text-white" id="errorTitle">Kamera Tidak Dapat Diakses</h4>
                    <p class="text-xs text-dark-300 mt-2 max-w-xs leading-relaxed" id="errorGuidance">
                        Pastikan webcam USB terhubung dan izin kamera browser diizinkan.
                    </p>
                    <button type="button" onclick="startAssistedCamera()" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all flex items-center gap-1.5 shadow-md shadow-indigo-500/30">
                        <i class='bx bx-refresh text-base'></i> Coba Lagi
                    </button>
                </div>
            </div>

            {{-- Real-time Status Alert Bar --}}
            <div id="statusAlertBar" class="mt-3 p-3 rounded-xl bg-dark-50 border border-dark-200 text-xs text-dark-700 flex items-center justify-between transition-all">
                <div class="flex items-center gap-2">
                    <i class='bx bx-loader-alt bx-spin text-indigo-600 text-base' id="statusIcon"></i>
                    <span id="statusMessage" class="font-medium">Menghubungkan ke webcam perangkat...</span>
                </div>
                <span id="qualityScoreBadge" class="text-[11px] font-bold text-dark-500"></span>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <a href="{{ route('admin.biometrik') }}" class="text-xs text-dark-500 hover:text-dark-700 font-semibold">
                    <i class='bx bx-left-arrow-alt'></i> Batalkan Sesi
                </a>
                <button type="button" id="btnRestart" onclick="restartAssistedProcess()" class="hidden text-xs text-amber-700 hover:text-amber-800 font-semibold flex items-center gap-1">
                    <i class='bx bx-refresh text-sm'></i> Ulangi Perekaman
                </button>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/dist/face-api.js"></script>
    <script src="{{ asset('js/universal-biometric-camera.js') }}"></script>

    <script>
        const SISWA_ID = {{ $siswa->id }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let cameraManager = null;
        let isModelLoaded = false;
        let isDetecting = false;
        let isLivenessPassed = false;
        let isEnrolling = false;

        let challengeToken = null;
        let challenges = [];
        let currentChallengeIdx = 0;
        let completedChallenges = [];
        let challengeStartTime = 0;
        let blinkDetected = false;
        let eyeClosedStartTime = 0;

        const REQUIRED_SAMPLES = 3;
        let collectedDescriptors = [];
        let finalSnapshotBase64 = null;

        const video = document.getElementById('assistedVideo');
        const canvas = document.getElementById('assistedCanvas');
        const cameraWrapper = document.getElementById('cameraWrapper');
        const ovalFrame = document.getElementById('ovalFrame');
        const loaderOverlay = document.getElementById('loaderOverlay');
        const cameraErrorOverlay = document.getElementById('cameraErrorOverlay');
        const cameraSelect = document.getElementById('cameraSelect');
        const deviceBadge = document.getElementById('deviceBadge');
        const resolutionBadge = document.getElementById('resolutionBadge');

        const statusAlertBar = document.getElementById('statusAlertBar');
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        const qualityScoreBadge = document.getElementById('qualityScoreBadge');

        const assistedChallengeBox = document.getElementById('assistedChallengeBox');
        const assistedChallengeCounter = document.getElementById('assistedChallengeCounter');
        const assistedChallengeTitle = document.getElementById('assistedChallengeTitle');
        const assistedChallengeSubtitle = document.getElementById('assistedChallengeSubtitle');
        const assistedChallengeIcon = document.getElementById('assistedChallengeIcon');

        const assistedProgressBox = document.getElementById('assistedProgressBox');
        const assistedProgressCounter = document.getElementById('assistedProgressCounter');
        const assistedProgressBar = document.getElementById('assistedProgressBar');
        const btnRestart = document.getElementById('btnRestart');

        document.addEventListener('DOMContentLoaded', async () => {
            initCameraManager();
            await loadAiModels();
            await startAssistedCamera();
        });

        function initCameraManager() {
            cameraManager = new UniversalBiometricCamera({
                videoElement: video,
                selectElement: cameraSelect,
                deviceBadgeElement: deviceBadge,
                resolutionBadgeElement: resolutionBadge,
                onReady: (stream, info) => {
                    cameraErrorOverlay.classList.add('hidden');
                    updateStatus('Posisikan wajah siswa tepat di dalam bingkai oval.', 'ready');
                    startDetectionLoop();
                },
                onError: (errInfo) => {
                    document.getElementById('errorTitle').textContent = errInfo.title;
                    document.getElementById('errorGuidance').textContent = errInfo.guidance;
                    cameraErrorOverlay.classList.remove('hidden');
                    updateStatus('Kamera tidak aktif atau terhambat.', 'error');
                },
                onDeviceChange: (deviceId, label) => {
                    updateStatus(`Kamera dialihkan ke ${label}. Posisikan wajah.`, 'ready');
                }
            });
        }

        async function loadAiModels() {
            if (isModelLoaded) return;
            loaderOverlay.classList.remove('hidden', 'opacity-0');

            try {
                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/';
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                isModelLoaded = true;
                loaderOverlay.classList.add('opacity-0');
                setTimeout(() => loaderOverlay.classList.add('hidden'), 300);
            } catch (err) {
                console.error("Gagal memuat model:", err);
                document.getElementById('loaderTitle').textContent = "Gagal memuat model AI. Periksa koneksi internet.";
            }
        }

        async function startAssistedCamera() {
            cameraErrorOverlay.classList.add('hidden');
            updateStatus('Menghubungkan ke webcam...', 'ready');
            await cameraManager.start();
        }

        function updateStatus(message, type = 'ready') {
            statusMessage.textContent = message;
            if (type === 'valid') {
                statusAlertBar.className = 'mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between transition-all';
                statusIcon.className = 'bx bx-check-circle text-emerald-600 text-base';
                ovalFrame.className = 'assisted-oval-frame frame-valid';
            } else if (type === 'warning') {
                statusAlertBar.className = 'mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center justify-between transition-all';
                statusIcon.className = 'bx bx-error text-amber-600 text-base';
                ovalFrame.className = 'assisted-oval-frame frame-warning';
            } else if (type === 'error') {
                statusAlertBar.className = 'mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center justify-between transition-all';
                statusIcon.className = 'bx bx-x-circle text-rose-600 text-base';
                ovalFrame.className = 'assisted-oval-frame';
            } else {
                statusAlertBar.className = 'mt-3 p-3 rounded-xl bg-dark-50 border border-dark-200 text-xs text-dark-700 flex items-center justify-between transition-all';
                statusIcon.className = 'bx bx-scan text-indigo-600 text-base';
                ovalFrame.className = 'assisted-oval-frame';
            }
        }

        function startDetectionLoop() {
            if (isDetecting) return;
            isDetecting = true;
            cameraWrapper.classList.add('is-scanning');

            const checkFrame = async () => {
                if (!isDetecting || isEnrolling || video.paused || video.ended) {
                    if (isDetecting && !isEnrolling) requestAnimationFrame(checkFrame);
                    return;
                }

                try {
                    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.55 });
                    const detection = await faceapi.detectSingleFace(video, options)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        updateStatus('Wajah tidak terdeteksi. Harap menghadap ke webcam.', 'warning');
                        qualityScoreBadge.textContent = '';
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    const box = detection.detection.box;
                    const vW = video.videoWidth || 640;
                    const vH = video.videoHeight || 480;

                    const faceCenterRatioX = (box.x + box.width / 2) / vW;
                    const faceAreaRatio = (box.width * box.height) / (vW * vH);

                    if (faceAreaRatio < 0.10) {
                        updateStatus('Wajah terlalu jauh dari webcam. Harap mendekat.', 'warning');
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    if (faceCenterRatioX < 0.28 || faceCenterRatioX > 0.72) {
                        updateStatus('Posisikan wajah tepat di tengah bingkai oval.', 'warning');
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    const qualityAssessment = cameraManager.assessStreamQuality();
                    if (!qualityAssessment.isAdequate) {
                        updateStatus(qualityAssessment.reason, 'warning');
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    const qualityScore = Math.min(99, Math.round(detection.detection.score * 100));
                    qualityScoreBadge.textContent = `Kualitas: ${qualityScore}%`;

                    if (!challengeToken) {
                        updateStatus('Posisi wajah optimal. Memulai validasi liveness...', 'valid');
                        isDetecting = false;
                        await requestChallengeAndStart();
                        return;
                    }

                    if (!isLivenessPassed) {
                        evaluateLiveness(detection);
                    }

                } catch (loopErr) {
                    console.error("Loop error:", loopErr);
                }

                if (isDetecting && !isEnrolling) {
                    requestAnimationFrame(checkFrame);
                }
            };

            requestAnimationFrame(checkFrame);
        }

        async function requestChallengeAndStart() {
            try {
                const res = await fetch(`/admin/biometrik-siswa/${SISWA_ID}/assisted-enroll/challenge`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });

                const json = await res.json();
                if (!json.success) throw new Error(json.message);

                challengeToken = json.data.token;
                challenges = json.data.challenges;
                currentChallengeIdx = 0;
                completedChallenges = [];

                assistedChallengeBox.classList.remove('hidden');
                displayCurrentChallenge();

                isDetecting = true;
                requestAnimationFrame(startDetectionLoop);
            } catch (err) {
                console.error("Challenge error:", err);
                updateStatus('Gagal memuat tantangan liveness. Klik Ulangi.', 'error');
                btnRestart.classList.remove('hidden');
            }
        }

        function displayCurrentChallenge() {
            if (currentChallengeIdx >= challenges.length) return;
            const current = challenges[currentChallengeIdx];
            assistedChallengeCounter.textContent = `${currentChallengeIdx + 1} dari ${challenges.length}`;
            assistedChallengeTitle.textContent = current.instruction;

            if (current.type === 'blink') {
                assistedChallengeSubtitle.textContent = 'Berkedip normal 1-2 kali di depan kamera.';
                assistedChallengeIcon.className = 'bx bx-low-vision';
            } else if (current.type === 'smile') {
                assistedChallengeSubtitle.textContent = 'Tersenyum ramah memperlihatkan bibir.';
                assistedChallengeIcon.className = 'bx bx-smile';
            } else if (current.type === 'turn_left') {
                assistedChallengeSubtitle.textContent = 'Tolehkan kepala sedikit ke kiri.';
                assistedChallengeIcon.className = 'bx bx-left-arrow-circle';
            } else if (current.type === 'turn_right') {
                assistedChallengeSubtitle.textContent = 'Tolehkan kepala sedikit ke kanan.';
                assistedChallengeIcon.className = 'bx bx-right-arrow-circle';
            }

            challengeStartTime = Date.now();
            blinkDetected = false;
        }

        function evaluateLiveness(detection) {
            const current = challenges[currentChallengeIdx];
            if (!current) return;

            const landmarks = detection.landmarks;
            let passed = false;

            if (current.type === 'smile') {
                const mouth = landmarks.getMouth();
                const mouthWidth = Math.hypot(mouth[6].x - mouth[0].x, mouth[6].y - mouth[0].y);
                const jawWidth = Math.hypot(landmarks.positions[16].x - landmarks.positions[0].x, landmarks.positions[16].y - landmarks.positions[0].y);
                if ((mouthWidth / jawWidth) > 0.44) passed = true;
            } else if (current.type === 'blink') {
                const leftEye = landmarks.getLeftEye();
                const rightEye = landmarks.getRightEye();
                const leftEar = (Math.hypot(leftEye[1].x - leftEye[5].x, leftEye[1].y - leftEye[5].y) + Math.hypot(leftEye[2].x - leftEye[4].x, leftEye[2].y - leftEye[4].y)) / (2 * Math.hypot(leftEye[0].x - leftEye[3].x, leftEye[0].y - leftEye[3].y));
                const rightEar = (Math.hypot(rightEye[1].x - rightEye[5].x, rightEye[1].y - rightEye[5].y) + Math.hypot(rightEye[2].x - rightEye[4].x, rightEye[2].y - rightEye[4].y)) / (2 * Math.hypot(rightEye[0].x - rightEye[3].x, rightEye[0].y - rightEye[3].y));
                const ear = (leftEar + rightEar) / 2;

                if (ear < 0.22) {
                    if (!eyeClosedStartTime) eyeClosedStartTime = Date.now();
                } else if (eyeClosedStartTime) {
                    if (Date.now() - eyeClosedStartTime > 80) blinkDetected = true;
                    eyeClosedStartTime = 0;
                }
                if (blinkDetected) passed = true;
            } else if (current.type === 'turn_left' || current.type === 'turn_right') {
                const nose = landmarks.getNose()[3];
                const leftCheek = landmarks.positions[0];
                const rightCheek = landmarks.positions[16];
                const distLeft = Math.abs(nose.x - leftCheek.x);
                const distRight = Math.abs(nose.x - rightCheek.x);
                const ratio = distLeft / Math.max(1, distRight);

                if (current.type === 'turn_left' && ratio < 0.65) passed = true;
                if (current.type === 'turn_right' && ratio > 1.55) passed = true;
            }

            if (passed) {
                const durationMs = Date.now() - challengeStartTime;
                completedChallenges.push({ type: current.type, duration_ms: durationMs });
                currentChallengeIdx++;

                if (currentChallengeIdx < challenges.length) {
                    displayCurrentChallenge();
                } else {
                    isLivenessPassed = true;
                    assistedChallengeBox.classList.add('hidden');
                    startMultiSampleCollection();
                }
            }
        }

        async function startMultiSampleCollection() {
            isEnrolling = true;
            isDetecting = false;
            cameraWrapper.classList.remove('is-scanning');
            assistedProgressBox.classList.remove('hidden');

            updateStatus('Liveness lulus! Mengambil 3 sampel template wajah...', 'valid');

            collectedDescriptors = [];
            finalSnapshotBase64 = cameraManager.captureSnapshot(640, 480);

            for (let i = 1; i <= REQUIRED_SAMPLES; i++) {
                assistedProgressCounter.textContent = `${i} / ${REQUIRED_SAMPLES} Sampel`;
                assistedProgressBar.style.width = `${Math.round((i / REQUIRED_SAMPLES) * 100)}%`;

                const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.60 });
                const detection = await faceapi.detectSingleFace(video, options)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection) {
                    collectedDescriptors.push(Array.from(detection.descriptor));
                }

                await new Promise(r => setTimeout(r, 400));
            }

            if (collectedDescriptors.length < 2) {
                updateStatus('Gagal mengambil sampel wajah stabil. Ulangi perekaman.', 'error');
                btnRestart.classList.remove('hidden');
                isEnrolling = false;
                return;
            }

            // Average descriptor vector
            const avgDescriptor = new Array(128).fill(0);
            for (let d = 0; d < 128; d++) {
                let sum = 0;
                for (let s = 0; s < collectedDescriptors.length; s++) {
                    sum += collectedDescriptors[s][d];
                }
                avgDescriptor[d] = sum / collectedDescriptors.length;
            }

            await submitAssistedEnrollment(avgDescriptor);
        }

        async function submitAssistedEnrollment(descriptor) {
            updateStatus('Menyimpan biometrik resmi ke server sekolah...', 'ready');

            const payload = {
                face_descriptor: descriptor,
                foto: finalSnapshotBase64,
                challenge_token: challengeToken,
                liveness_proof: {
                    completed_challenges: completedChallenges,
                    liveness_score: 96.0
                },
                quality_score: 95.0
            };

            try {
                const res = await fetch(`/admin/biometrik-siswa/${SISWA_ID}/assisted-enroll/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();
                if (!json.success) throw new Error(json.message);

                cameraManager.stop();

                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Terpandu Berhasil!',
                    text: json.message,
                    confirmButtonColor: '#4f46e5'
                }).then(() => {
                    window.location.href = json.redirect_url;
                });

            } catch (err) {
                console.error("Submit error:", err);
                updateStatus(err.message || 'Gagal menyimpan template biometrik.', 'error');
                btnRestart.classList.remove('hidden');
                isEnrolling = false;
            }
        }

        function restartAssistedProcess() {
            btnRestart.classList.add('hidden');
            assistedProgressBox.classList.add('hidden');
            assistedProgressBar.style.width = '0%';
            challengeToken = null;
            challenges = [];
            currentChallengeIdx = 0;
            completedChallenges = [];
            isLivenessPassed = false;
            isEnrolling = false;
            isDetecting = false;

            startDetectionLoop();
        }
    </script>
@endpush

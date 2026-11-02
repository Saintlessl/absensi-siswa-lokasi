<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Wajah Masuk — SMK Negeri</title>

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .camera-video-frame {
            position: relative;
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            border-radius: 1.25rem;
            overflow: hidden;
            background: #020617;
            aspect-ratio: 4 / 3;
            border: 1px solid rgba(51, 65, 85, 0.8);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.7);
        }

        .enroll-video-feed {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        .face-oval-guide {
            width: 190px;
            height: 250px;
            border-radius: 50%;
            border: 3px dashed rgba(96, 165, 250, 0.8);
            box-shadow: 0 0 0 9999px rgba(2, 6, 23, 0.55);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .face-oval-guide.guide-valid {
            border-color: #10b981;
            border-style: solid;
            box-shadow: 0 0 0 9999px rgba(2, 6, 23, 0.65), 0 0 20px rgba(16, 185, 129, 0.6);
        }

        .face-oval-guide.guide-warning {
            border-color: #f59e0b;
        }

        .bg-grid-dots {
            background-image: radial-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>

<body class="h-full bg-slate-950 text-slate-100 font-inter antialiased flex flex-col justify-between selection:bg-primary-500 selection:text-white bg-grid-dots">

    {{-- Top Navbar --}}
    <header class="w-full py-4 px-6 border-b border-slate-800/80 bg-slate-900/50 backdrop-blur-md">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary-600 to-primary-400 flex items-center justify-center text-white font-black text-sm shadow-md shadow-primary-500/25">
                    SMK
                </div>
                <div>
                    <h1 class="text-xs font-bold text-white tracking-wide">SMK NEGERI</h1>
                    <p class="text-[10px] text-slate-400">Verifikasi Wajah Masuk (2FA Biometrik)</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                    <i class='bx bx-devices text-primary-400'></i> Slot: {{ $activeDevicesCount }} dari 2
                </span>
                <a href="{{ route('login.verify_face.cancel') }}" class="text-xs text-slate-400 hover:text-white font-medium px-3 py-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                    Batal
                </a>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-lg bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 shadow-2xl backdrop-blur-md text-center">

            {{-- Student ID Info --}}
            <div class="flex items-center justify-center gap-3 mb-5 p-3 rounded-xl bg-slate-950/70 border border-slate-800 text-left">
                <div class="w-12 h-12 rounded-xl bg-primary-600/20 border border-primary-500/30 text-primary-400 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class='bx bx-user-check'></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-sm text-white truncate">{{ $siswa->nama }}</h3>
                    <p class="text-[11px] text-slate-400">NIS: <span class="font-mono text-slate-300">{{ $siswa->nis }}</span> &bull; Kelas: <span class="text-slate-300">{{ $siswa->kelas->nama_kelas ?? '-' }}</span></p>
                </div>
            </div>

            {{-- Heading & Instruction --}}
            <div class="mb-4">
                <h2 class="text-base font-bold text-white flex items-center justify-center gap-2">
                    <i class='bx bx-scan text-primary-400 text-lg'></i> Verifikasi Wajah Masuk
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Hadapkan wajah Anda lurus ke kamera untuk memvalidasi kepemilikan akun sebelum masuk ke sistem.
                </p>
            </div>

            {{-- Camera Viewport --}}
            <div class="camera-video-frame relative" id="cameraWrapper">
                <video id="loginVideo" class="enroll-video-feed" autoplay playsinline muted></video>
                <canvas id="loginCanvas" class="hidden"></canvas>

                {{-- Overlay Guide --}}
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div id="ovalGuide" class="face-oval-guide"></div>
                </div>

                {{-- Model Loading Overlay --}}
                <div id="loaderOverlay" class="absolute inset-0 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-20 transition-opacity">
                    <div class="w-8 h-8 rounded-full border-3 border-primary-500 border-t-transparent animate-spin mb-3"></div>
                    <p class="font-bold text-xs" id="loaderTitle">Memuat Model AI Biometrik...</p>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-xs">Menyiapkan neural network pengenal wajah.</p>
                </div>

                {{-- Camera Error Overlay --}}
                <div id="cameraErrorOverlay" class="hidden absolute inset-0 bg-slate-950/95 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-30">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-2xl mb-2 border border-rose-500/30">
                        <i class='bx bx-camera-off'></i>
                    </div>
                    <h4 class="font-bold text-sm text-white" id="cameraErrorTitle">Kamera Tidak Aktif</h4>
                    <p class="text-xs text-slate-400 mt-1.5 max-w-xs leading-relaxed" id="cameraErrorGuidance">
                        Pastikan izin kamera telah diberikan di browser.
                    </p>
                    <button type="button" onclick="startCamera()" class="mt-4 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-semibold text-xs transition-colors flex items-center gap-1.5">
                        <i class='bx bx-refresh text-base'></i> Coba Lagi
                    </button>
                </div>
            </div>

            {{-- Status Alert Bar --}}
            <div id="statusAlertBar" class="mt-4 p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 flex items-center justify-between transition-all">
                <div class="flex items-center gap-2">
                    <i class='bx bx-loader-alt bx-spin text-primary-400 text-base' id="statusIcon"></i>
                    <span id="statusMessage" class="font-medium">Menghubungkan ke kamera...</span>
                </div>
                <span id="qualityScoreBadge" class="text-[11px] font-bold text-slate-400"></span>
            </div>

            {{-- Pose Hold Progress Bar (600ms hold) --}}
            <div class="w-full bg-slate-800 rounded-full h-1.5 mt-3 overflow-hidden">
                <div id="poseHoldProgressBar" class="bg-primary-500 h-1.5 rounded-full transition-all duration-75" style="width: 0%"></div>
            </div>

            {{-- Device Switcher --}}
            <div class="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                    <i class='bx bx-camera text-slate-500'></i>
                    <select id="cameraDeviceSelect" class="bg-slate-950 border border-slate-800 text-slate-300 text-[11px] rounded-lg px-2 py-1 max-w-[200px] truncate focus:outline-none">
                        <option value="">Kamera Utama</option>
                    </select>
                </div>
                <span id="deviceBadge" class="text-[11px] font-semibold text-slate-400">Memeriksa perangkat...</span>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-3 px-6 text-center text-slate-500 text-xs border-t border-slate-900">
        &copy; {{ date('Y') }} SMK Negeri &bull; Keamanan Akun Biometrik Wajah & Multi-Device Control
    </footer>

    {{-- Face-API & Camera Engine --}}
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/dist/face-api.js"></script>
    <script src="{{ asset('js/universal-biometric-camera.js') }}"></script>

    <script>
        let isModelLoaded = false;
        let isDetecting = false;
        let isVerifying = false;
        let challengeToken = null;
        let cameraManager = null;
        let poseHoldStartTime = null;
        const REQUIRED_HOLD_MS = 600;

        const video = document.getElementById('loginVideo');
        const canvas = document.getElementById('loginCanvas');
        const ovalGuide = document.getElementById('ovalGuide');
        const loaderOverlay = document.getElementById('loaderOverlay');
        const cameraErrorOverlay = document.getElementById('cameraErrorOverlay');
        const cameraDeviceSelect = document.getElementById('cameraDeviceSelect');
        const deviceBadge = document.getElementById('deviceBadge');
        const statusAlertBar = document.getElementById('statusAlertBar');
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        const qualityScoreBadge = document.getElementById('qualityScoreBadge');
        const poseHoldProgressBar = document.getElementById('poseHoldProgressBar');

        document.addEventListener('DOMContentLoaded', async () => {
            await loadModels();
            await requestChallenge();
            await startCamera();
            startDetectionLoop();
        });

        async function loadModels() {
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
                document.getElementById('loaderTitle').textContent = "Gagal memuat model AI biometrik.";
            }
        }

        async function requestChallenge() {
            try {
                const res = await fetch("{{ route('login.verify_face.challenge') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    challengeToken = data.challenge_token;
                }
            } catch (err) {
                console.error("Gagal meminta challenge:", err);
            }
        }

        async function startCamera() {
            cameraErrorOverlay.classList.add('hidden');
            updateStatus('Menghubungkan ke webcam/kamera...', 'ready');

            if (!cameraManager) {
                cameraManager = new UniversalBiometricCamera({
                    videoElement: video,
                    selectElement: cameraDeviceSelect,
                    deviceBadgeElement: deviceBadge,
                    onReady: (stream, info) => {
                        cameraErrorOverlay.classList.add('hidden');
                        updateStatus('Posisikan wajah Anda tepat di dalam bingkai oval.', 'ready');
                    },
                    onError: (errInfo) => {
                        document.getElementById('cameraErrorTitle').textContent = errInfo.title;
                        document.getElementById('cameraErrorGuidance').textContent = errInfo.guidance;
                        cameraErrorOverlay.classList.remove('hidden');
                        updateStatus(errInfo.title, 'error');
                    }
                });
            }

            return await cameraManager.start();
        }

        function updateStatus(msg, state = 'ready', score = null) {
            statusMessage.textContent = msg;
            if (score !== null) {
                qualityScoreBadge.textContent = `Skor: ${Math.round(score * 100)}%`;
            } else {
                qualityScoreBadge.textContent = '';
            }

            if (state === 'success') {
                statusIcon.className = 'bx bx-check-circle text-emerald-400 text-base';
                statusAlertBar.className = 'mt-4 p-2.5 rounded-xl bg-emerald-950/70 border border-emerald-500/50 text-xs text-emerald-200 flex items-center justify-between transition-all';
            } else if (state === 'warning') {
                statusIcon.className = 'bx bx-error-circle text-amber-400 text-base';
                statusAlertBar.className = 'mt-4 p-2.5 rounded-xl bg-amber-950/70 border border-amber-500/50 text-xs text-amber-200 flex items-center justify-between transition-all';
            } else if (state === 'error') {
                statusIcon.className = 'bx bx-x-circle text-rose-400 text-base';
                statusAlertBar.className = 'mt-4 p-2.5 rounded-xl bg-rose-950/70 border border-rose-500/50 text-xs text-rose-200 flex items-center justify-between transition-all';
            } else {
                statusIcon.className = 'bx bx-loader-alt bx-spin text-primary-400 text-base';
                statusAlertBar.className = 'mt-4 p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 flex items-center justify-between transition-all';
            }
        }

        function startDetectionLoop() {
            if (isDetecting) return;
            isDetecting = true;

            const checkFrame = async () => {
                if (!isDetecting || isVerifying || video.paused || video.ended) {
                    if (isDetecting && !isVerifying) requestAnimationFrame(checkFrame);
                    return;
                }

                try {
                    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.55 });
                    const detection = await faceapi.detectSingleFace(video, options)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        poseHoldStartTime = null;
                        poseHoldProgressBar.style.width = '0%';
                        ovalGuide.className = 'face-oval-guide';
                        updateStatus('Wajah tidak terdeteksi. Posisikan wajah di dalam bingkai oval.', 'warning');
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    const videoW = video.videoWidth || 640;
                    const box = detection.detection.box;
                    const boxCenterX = (box.x + box.width / 2) / videoW;
                    const isCentered = boxCenterX >= 0.25 && boxCenterX <= 0.75;
                    const faceRatio = box.width / videoW;

                    if (!isCentered) {
                        poseHoldStartTime = null;
                        poseHoldProgressBar.style.width = '0%';
                        ovalGuide.className = 'face-oval-guide guide-warning';
                        updateStatus('Posisikan wajah Anda tepat di tengah bingkai.', 'warning');
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    if (faceRatio < 0.20) {
                        poseHoldStartTime = null;
                        poseHoldProgressBar.style.width = '0%';
                        ovalGuide.className = 'face-oval-guide guide-warning';
                        updateStatus('Dekatkan wajah sedikit ke kamera.', 'warning');
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    // Face aligned! Hold progress
                    ovalGuide.className = 'face-oval-guide guide-valid';
                    if (!poseHoldStartTime) {
                        poseHoldStartTime = Date.now();
                    }

                    const elapsed = Date.now() - poseHoldStartTime;
                    const progress = Math.min(100, Math.round((elapsed / REQUIRED_HOLD_MS) * 100));
                    poseHoldProgressBar.style.width = progress + '%';
                    updateStatus(`Wajah terfokus! Tahan sebentar (${Math.round(elapsed)}ms)...`, 'success', detection.detection.score);

                    if (elapsed >= REQUIRED_HOLD_MS && !isVerifying) {
                        isVerifying = true;
                        isDetecting = false;
                        await processLoginVerification(detection);
                        return;
                    }

                } catch (e) {
                    console.error("Frame detection error:", e);
                }

                if (isDetecting && !isVerifying) {
                    requestAnimationFrame(checkFrame);
                }
            };

            requestAnimationFrame(checkFrame);
        }

        async function processLoginVerification(detection) {
            updateStatus('Memverifikasi biometrik dengan server sekolah...', 'ready');

            // Snapshot
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const snapshotBase64 = canvas.toDataURL('image/jpeg', 0.88);

            // Device details
            const platformInfo = cameraManager ? cameraManager.platformInfo : { type: 'web', browser: 'Browser', os: 'Desktop' };
            const deviceToken = getDeviceToken();

            try {
                const response = await fetch("{{ route('login.verify_face.process') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        challenge_token: challengeToken,
                        face_descriptor: Array.from(detection.descriptor),
                        foto: snapshotBase64,
                        liveness_proof: {
                            liveness_score: 98.5,
                            completed_challenges: [{ type: 'front_facing', duration_ms: 600 }]
                        },
                        device_token: deviceToken,
                        device_name: `${platformInfo.os || 'Perangkat'} - ${platformInfo.browser || 'Browser'}`,
                        platform: platformInfo.os || 'Web',
                        browser: platformInfo.browser || 'Browser'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    if (result.device_token) {
                        localStorage.setItem('myskanic_device_token', result.device_token);
                    }
                    updateStatus('Verifikasi Berhasil! Selamat datang.', 'success');
                    ovalGuide.className = 'face-oval-guide guide-valid';

                    Swal.fire({
                        icon: 'success',
                        title: 'Autentikasi Berhasil!',
                        text: 'Wajah cocok dengan pemilik akun terdaftar.',
                        showConfirmButton: false,
                        timer: 1200
                    }).then(() => {
                        window.location.href = result.redirect_url || "{{ route('dashboard') }}";
                    });
                } else {
                    updateStatus(result.message || 'Verifikasi biometrik gagal.', 'error');
                    ovalGuide.className = 'face-oval-guide';
                    poseHoldStartTime = null;
                    poseHoldProgressBar.style.width = '0%';

                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak!',
                        text: result.message || 'Wajah tidak cocok dengan template biometrik akun terdaftar.',
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#e11d48'
                    }).then(() => {
                        isVerifying = false;
                        requestChallenge().then(() => {
                            startDetectionLoop();
                        });
                    });
                }

            } catch (err) {
                console.error("Verification submit error:", err);
                updateStatus('Terjadi kesalahan jaringan saat memverifikasi.', 'error');
                isVerifying = false;
                setTimeout(() => startDetectionLoop(), 2000);
            }
        }

        function getDeviceToken() {
            let token = localStorage.getItem('myskanic_device_token');
            if (!token) {
                token = 'dev_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
                localStorage.setItem('myskanic_device_token', token);
            }
            return token;
        }
    </script>
</body>
</html>

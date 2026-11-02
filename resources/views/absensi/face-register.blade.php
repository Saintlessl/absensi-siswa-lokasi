@extends('layouts.app')

@section('title', 'Registrasi Biometrik Wajah')
@section('page-title', 'Registrasi Biometrik Wajah')
@section('page-subtitle', 'Pendaftaran template wajah untuk verifikasi presensi kehadiran')

@section('breadcrumb')
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li><a href="{{ route('absensi') }}" class="text-dark-500 hover:text-primary-600">Absensi</a></li>
    <li><i class='bx bx-chevron-right text-dark-400'></i></li>
    <li class="text-primary-600 font-medium">Registrasi Wajah</li>
@endsection

@push('styles')
    <style>
        .camera-wrapper {
            position: relative;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            border-radius: 1.5rem;
            overflow: hidden;
            background: #0f172a;
            aspect-ratio: 3 / 4;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.25);
        }

        .camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* mirror preview */
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

        /* Minimalist Face Alignment Guide */
        .face-oval-guide {
            width: 230px;
            height: 300px;
            border-radius: 50%;
            border: 2.5px dashed rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.45);
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

        .face-oval-guide.guide-error {
            border-color: #ef4444;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Status Registrasi Saat Ini --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold shadow-md {{ $biometric && $biometric->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                    <i class='bx {{ $biometric && $biometric->is_active ? 'bx-check-shield' : 'bx-scan' }}'></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-dark-900">
                        {{ $biometric && $biometric->is_active ? 'Biometrik Wajah Terdaftar' : 'Biometrik Wajah Belum Terdaftar' }}
                    </h3>
                    <p class="text-xs text-dark-500 mt-0.5">
                        @if ($biometric && $biometric->is_active)
                            Terakhir didaftarkan pada {{ $biometric->registered_at ? $biometric->registered_at->translatedFormat('d F Y, H:i') : '-' }} WIB &bull; Skor Kualitas: {{ round($biometric->quality_score) }}%
                        @else
                            Silakan lakukan perekaman wajah sekali untuk mengaktifkan fitur absensi biometrik.
                        @endif
                    </p>
                </div>
            </div>

            @if ($biometric && $biometric->is_active)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class='bx bxs-check-circle'></i> Siap Digunakan
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <i class='bx bx-time'></i> Menunggu Pendaftaran
                </span>
            @endif
        </div>

        {{-- Panel Pendaftaran Biometrik Wajah --}}
        <div class="glass-card rounded-2xl border border-dark-200/50 p-6 sm:p-8">
            <div class="max-w-xl mx-auto text-center mb-6">
                <h4 class="text-lg font-bold text-dark-900">
                    {{ $biometric && $biometric->is_active ? 'Perbarui Template Wajah' : 'Perekaman Template Biometrik' }}
                </h4>
                <p class="text-xs text-dark-500 mt-1">
                    Posisikan wajah Anda tepat di dalam bingkai oval dengan pencahayaan yang cukup dan ekspresi natural.
                </p>
            </div>

            {{-- Camera Viewport --}}
            <div class="camera-wrapper" id="cameraWrapper">
                <video id="videoElement" class="camera-video" autoplay playsinline muted></video>
                <canvas id="snapshotCanvas" class="hidden"></canvas>

                {{-- Overlay Panduan Wajah --}}
                <div class="camera-overlay">
                    <div class="face-oval-guide" id="ovalGuide">
                    </div>
                </div>

                {{-- Loading Model Indicator Overlay --}}
                <div id="modelLoaderOverlay" class="absolute inset-0 bg-dark-950/85 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-20 transition-opacity">
                    <div class="w-12 h-12 rounded-full border-4 border-primary-500 border-t-transparent animate-spin mb-4"></div>
                    <p class="font-bold text-sm" id="loaderStatusText">Memuat Model AI Biometrik...</p>
                    <p class="text-xs text-dark-400 mt-1 max-w-xs">Mengunduh neural network biometrik untuk deteksi wajah & landmark.</p>
                </div>
            </div>

            {{-- Feedback Status Bar --}}
            <div class="max-w-md mx-auto mt-5 space-y-3">
                <div id="statusAlert" class="p-3.5 rounded-xl bg-dark-50 border border-dark-200 text-xs font-medium text-dark-700 flex items-center justify-between transition-all">
                    <div class="flex items-center gap-2.5">
                        <i class='bx bx-loader-alt bx-spin text-primary-600 text-lg' id="statusIcon"></i>
                        <span id="statusMessage">Menginisialisasi kamera...</span>
                    </div>
                    <span id="qualityIndicator" class="text-[11px] font-bold text-dark-500">Kualitas: 0%</span>
                </div>

                {{-- Multi-Sample Progress Bar --}}
                <div id="sampleProgressContainer" class="hidden space-y-1.5 p-3 rounded-xl bg-primary-50/50 border border-primary-100">
                    <div class="flex items-center justify-between text-xs font-semibold text-primary-900">
                        <span>Perekaman Sampel Wajah (Multi-Frame)</span>
                        <span id="sampleCounter">0 / 3</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-primary-100 overflow-hidden">
                        <div id="sampleProgressBar" class="h-full bg-primary-600 rounded-full transition-all duration-300" style="width: 0%;"></div>
                    </div>
                    <p class="text-[11px] text-primary-700 text-center">Tetap diam di depan kamera selama proses perekaman...</p>
                </div>

                {{-- Action Controls --}}
                <div class="pt-2 flex items-center justify-center gap-3">
                    <button type="button" id="btnStartCapture" onclick="startBiometricEnrollment()" disabled
                        class="w-full py-3 px-6 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold text-sm shadow-lg shadow-primary-500/25 transition-all flex items-center justify-center gap-2 opacity-50 cursor-not-allowed">
                        <i class='bx bx-camera text-lg'></i>
                        <span id="btnCaptureText">Mulai Rekam Wajah</span>
                    </button>
                </div>
            </div>

            {{-- Privacy Notice --}}
            <div class="mt-8 pt-6 border-t border-dark-100/70 max-w-xl mx-auto text-center text-xs text-dark-400 space-y-1">
                <p class="font-semibold text-dark-600 flex items-center justify-center gap-1.5">
                    <i class='bx bx-shield-quarter text-primary-600'></i> Perlindungan Privasi & Keamanan Biometrik
                </p>
                <p class="text-[11px] leading-relaxed">
                    Sistem hanya menyimpan <em>vektor biometrik terenkripsi (128-d floating point embedding)</em> dan tidak membagikan data wajah Anda kepada pihak ketiga. Data biometrik digunakan semata-mata untuk verifikasi presensi sekolah.
                </p>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    {{-- Vlad Mandic Face-API Distribution (Modern TensorFlow.js based Face Recognition) --}}
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
    {{-- Universal Biometric Camera Engine --}}
    <script src="{{ asset('js/universal-biometric-camera.js') }}"></script>

    <script>
        const MODEL_URL_CDN = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        const MODEL_URL_MIRROR = 'https://justadudewhohacks.github.io/face-api.js/models/';

        let isModelLoaded = false;
        let isDetecting = false;
        let isEnrolling = false;
        let videoStream = null;
        let biometricCamera = null;
        let collectedDescriptors = [];
        const REQUIRED_SAMPLES = 3;

        const video = document.getElementById('videoElement');
        const ovalGuide = document.getElementById('ovalGuide');
        const cameraWrapper = document.getElementById('cameraWrapper');
        const statusAlert = document.getElementById('statusAlert');
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        const qualityIndicator = document.getElementById('qualityIndicator');
        const btnStart = document.getElementById('btnStartCapture');
        const modelLoader = document.getElementById('modelLoaderOverlay');
        const loaderText = document.getElementById('loaderStatusText');

        document.addEventListener('DOMContentLoaded', async () => {
            await loadAiModels();
            await startCamera();
            startLiveDetectionLoop();
        });

        // 1. Load Face-API models
        async function loadAiModels() {
            try {
                loaderText.textContent = 'Memuat TinyFaceDetector...';
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL_CDN);

                loaderText.textContent = 'Memuat FaceLandmark68Net...';
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL_CDN);

                loaderText.textContent = 'Memuat FaceRecognitionNet...';
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL_CDN);

                isModelLoaded = true;
                modelLoader.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => modelLoader.classList.add('hidden'), 300);

                updateStatus('Model AI siap. Posisikan wajah di tengah bingkai.', 'ready');
            } catch (err) {
                console.warn('Gagal memuat model dari primary CDN, mencoba mirror...', err);
                try {
                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL_MIRROR);
                    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL_MIRROR);
                    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL_MIRROR);

                    isModelLoaded = true;
                    modelLoader.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => modelLoader.classList.add('hidden'), 300);

                    updateStatus('Model AI siap. Posisikan wajah di tengah bingkai.', 'ready');
                } catch (fallbackErr) {
                    console.error('Model load failure:', fallbackErr);
                    loaderText.innerHTML = `<span class="text-rose-400">Gagal memuat model AI.</span><br><small class="text-xs">Pastikan koneksi internet stabil untuk pertama kali memuat model.</small>`;
                    updateStatus('Gagal memuat model AI biometrik.', 'error');
                }
            }
        }

        // 2. Start WebCam
        async function startCamera() {
            if (!biometricCamera) {
                biometricCamera = new UniversalBiometricCamera({
                    videoElement: video,
                    onReady: (stream, info) => {
                        videoStream = stream;
                    },
                    onError: (errInfo) => {
                        console.error('Camera error:', errInfo);
                        updateStatus(errInfo.title + ': ' + errInfo.guidance, 'error');
                        Swal.fire({
                            icon: 'warning',
                            title: errInfo.title,
                            text: errInfo.guidance,
                            confirmButtonColor: '#2563eb'
                        });
                    }
                });
            }

            await biometricCamera.start();
        }

        // 3. Live Detection & Quality Assessment Loop
        function startLiveDetectionLoop() {
            isDetecting = true;

            const checkFrame = async () => {
                if (!isDetecting || !isModelLoaded || video.paused || video.ended) {
                    requestAnimationFrame(checkFrame);
                    return;
                }

                try {
                    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
                    const detection = await faceapi.detectSingleFace(video, options)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!isEnrolling) {
                        evaluateDetectionQuality(detection);
                    }
                } catch (e) {
                    // ignore occasional frame drop
                }

                requestAnimationFrame(checkFrame);
            };

            requestAnimationFrame(checkFrame);
        }

        // 4. Quality evaluation (Position, Size, Angle, Brightness)
        function evaluateDetectionQuality(detection) {
            if (!detection) {
                ovalGuide.className = 'face-oval-guide face-oval-guide.guide-warning';
                updateStatus('Wajah tidak terdeteksi. Tatap langsung ke arah kamera.', 'warning', 0);
                disableCaptureButton();
                return;
            }

            const box = detection.detection.box;
            const landmarks = detection.landmarks;
            const videoW = video.videoWidth || 640;
            const videoH = video.videoHeight || 480;

            // 1. Face Size Check (Not too tiny, not too close)
            const faceRatio = box.width / videoW;
            if (faceRatio < 0.22) {
                ovalGuide.className = 'face-oval-guide guide-warning';
                updateStatus('Dekatkan wajah Anda sedikit ke kamera.', 'warning', 35);
                disableCaptureButton();
                return;
            }
            if (faceRatio > 0.70) {
                ovalGuide.className = 'face-oval-guide guide-warning';
                updateStatus('Jauhkan wajah Anda sedikit dari kamera.', 'warning', 40);
                disableCaptureButton();
                return;
            }

            // 2. Head Pose Yaw Check (Compare nose to eyes symmetry)
            const nose = landmarks.getNose()[3]; // nose tip
            const leftEye = landmarks.getLeftEye()[0];
            const rightEye = landmarks.getRightEye()[3];
            const distLeft = Math.abs(nose.x - leftEye.x);
            const distRight = Math.abs(nose.x - rightEye.x);
            const symmetryRatio = Math.min(distLeft, distRight) / Math.max(distLeft, distRight);

            if (symmetryRatio < 0.45) {
                ovalGuide.className = 'face-oval-guide guide-warning';
                updateStatus('Arahkan kepala lurus menghadap kamera.', 'warning', 50);
                disableCaptureButton();
                return;
            }

            // 3. High Quality Face Detected!
            const qualityScore = Math.min(98, Math.round(detection.detection.score * 70 + symmetryRatio * 30));
            ovalGuide.className = 'face-oval-guide guide-valid';
            updateStatus('Wajah terdeteksi optimal. Klik "Mulai Rekam Wajah".', 'success', qualityScore);
            enableCaptureButton();
        }

        function updateStatus(msg, type = 'ready', quality = null) {
            statusMessage.textContent = msg;

            if (quality !== null) {
                qualityIndicator.textContent = `Kualitas: ${quality}%`;
            }

            if (type === 'success') {
                statusIcon.className = 'bx bx-check-circle text-emerald-600 text-lg';
                statusAlert.className = 'p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center justify-between transition-all';
            } else if (type === 'warning') {
                statusIcon.className = 'bx bx-info-circle text-amber-600 text-lg';
                statusAlert.className = 'p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-800 flex items-center justify-between transition-all';
            } else if (type === 'error') {
                statusIcon.className = 'bx bx-error-circle text-rose-600 text-lg';
                statusAlert.className = 'p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 flex items-center justify-between transition-all';
            } else {
                statusIcon.className = 'bx bx-loader-alt bx-spin text-primary-600 text-lg';
                statusAlert.className = 'p-3.5 rounded-xl bg-dark-50 border border-dark-200 text-xs font-medium text-dark-700 flex items-center justify-between transition-all';
            }
        }

        function enableCaptureButton() {
            btnStart.disabled = false;
            btnStart.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        function disableCaptureButton() {
            btnStart.disabled = true;
            btnStart.classList.add('opacity-50', 'cursor-not-allowed');
        }

        // 5. Start Multi-Sample Enrollment (Captures 3 coherent clean frames)
        async function startBiometricEnrollment() {
            if (isEnrolling) return;
            isEnrolling = true;
            collectedDescriptors = [];

            disableCaptureButton();
            document.getElementById('sampleProgressContainer').classList.remove('hidden');
            cameraWrapper.classList.add('scanning');

            let sampleIndex = 0;
            let sampleSnapshots = [];

            while (sampleIndex < REQUIRED_SAMPLES) {
                updateStatus(`Mengambil sampel biometrik ${sampleIndex + 1} dari ${REQUIRED_SAMPLES}... Tetap tenang.`, 'ready');
                document.getElementById('sampleCounter').textContent = `${sampleIndex} / ${REQUIRED_SAMPLES}`;
                document.getElementById('sampleProgressBar').style.width = `${(sampleIndex / REQUIRED_SAMPLES) * 100}%`;

                await new Promise(r => setTimeout(r, 600));

                const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.6 });
                const detection = await faceapi.detectSingleFace(video, options)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection && detection.detection.score >= 0.65) {
                    collectedDescriptors.push(Array.from(detection.descriptor));
                    sampleIndex++;

                    // Capture photo snapshot on 2nd sample
                    if (sampleIndex === 2) {
                        const snap = biometricCamera ? biometricCamera.captureSnapshot(640, 480) : null;
                        if (snap) {
                            sampleSnapshots.push(snap);
                        } else {
                            const canvas = document.getElementById('snapshotCanvas');
                            canvas.width = video.videoWidth || 640;
                            canvas.height = video.videoHeight || 480;
                            const ctx = canvas.getContext('2d');
                            ctx.translate(canvas.width, 0);
                            ctx.scale(-1, 1);
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                            sampleSnapshots.push(canvas.toDataURL('image/jpeg', 0.88));
                        }
                    }
                }
            }

            document.getElementById('sampleCounter').textContent = `${REQUIRED_SAMPLES} / ${REQUIRED_SAMPLES}`;
            document.getElementById('sampleProgressBar').style.width = '100%';
            cameraWrapper.classList.remove('scanning');

            // 6. Compute Average Descriptor across 3 samples for high precision
            const averagedDescriptor = new Array(128).fill(0);
            for (let d = 0; d < 128; d++) {
                let sum = 0;
                for (let s = 0; s < REQUIRED_SAMPLES; s++) {
                    sum += collectedDescriptors[s][d];
                }
                averagedDescriptor[d] = sum / REQUIRED_SAMPLES;
            }

            // 7. Submit to Backend
            updateStatus('Menyimpan template biometrik terenkripsi ke server...', 'ready');

            fetch("{{ route('absensi.face.register') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    face_descriptor: averagedDescriptor,
                    foto: sampleSnapshots[0] || '',
                    quality_score: 92.5
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registrasi Berhasil!',
                        text: data.message,
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        window.location.href = "{{ route('absensi') }}";
                    });
                } else {
                    isEnrolling = false;
                    document.getElementById('sampleProgressContainer').classList.add('hidden');
                    Swal.fire({
                        icon: 'error',
                        title: 'Registrasi Gagal',
                        text: data.message,
                        confirmButtonColor: '#e11d48'
                    });
                }
            })
            .catch(err => {
                isEnrolling = false;
                document.getElementById('sampleProgressContainer').classList.add('hidden');
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Gagal menghubungi server untuk menyimpan biometrik.',
                    confirmButtonColor: '#e11d48'
                });
            });
        }

        window.addEventListener('beforeunload', () => {
            if (videoStream) {
                videoStream.getTracks().forEach(t => t.stop());
            }
        });
    </script>
@endpush

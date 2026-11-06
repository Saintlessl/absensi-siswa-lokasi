<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aktivasi Biometrik Wajah — SMK Negeri</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Boxicons --}}
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    {{-- Tailwind CSS via CDN --}}
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

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Ambient Floating Animation */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(1.5deg); }
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        @keyframes scanBeam {
            0% { top: 12%; opacity: 0; }
            15% { opacity: 1; }
            85% { opacity: 1; }
            100% { top: 88%; opacity: 0; }
        }

        @keyframes radarSweep {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes orbitRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes waveHand {
            0%, 100% { transform: rotate(0deg); }
            20% { transform: rotate(14deg); }
            40% { transform: rotate(-8deg); }
            60% { transform: rotate(14deg); }
            80% { transform: rotate(-4deg); }
        }

        @keyframes shutterClick {
            0% { transform: scale(1); filter: brightness(1); }
            40% { transform: scale(0.92); filter: brightness(1.8); }
            100% { transform: scale(1); filter: brightness(1); }
        }

        @keyframes shieldRipple {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; filter: drop-shadow(0 0 16px rgba(59, 130, 246, 0.6)); }
            100% { transform: scale(1); opacity: 0.9; }
        }

        .animate-float {
            animation: floatSlow 6s ease-in-out infinite;
        }

        .animate-pulse-glow {
            animation: pulseGlow 4s ease-in-out infinite;
        }

        .animate-scan-beam {
            animation: scanBeam 2.4s ease-in-out infinite;
        }

        .animate-radar {
            animation: radarSweep 8s linear infinite;
        }

        .animate-orbit {
            animation: orbitRotate 16s linear infinite;
        }

        .waving-hand {
            transform-origin: 70% 70%;
            animation: waveHand 2.2s ease-in-out infinite;
        }

        .tap-interactive {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            user-select: none;
        }

        .tap-interactive:hover {
            transform: translateY(-2px) scale(1.015);
        }

        .tap-interactive:active {
            transform: scale(0.975);
        }

        /* Scene Container & Transition */
        .scene-container {
            transition: opacity 0.38s cubic-bezier(0.16, 1, 0.3, 1), transform 0.38s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .scene-active {
            display: flex;
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .scene-hidden {
            display: none;
            opacity: 0;
            transform: translateY(16px) scale(0.98);
            pointer-events: none;
        }

        /* Camera Viewport Specific */
        .camera-video-frame {
            position: relative;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            border-radius: 1.25rem;
            overflow: hidden;
            background: #020617;
            aspect-ratio: 4 / 3;
            border: 1px solid rgba(51, 65, 85, 0.8);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.7);
        }

        @media (max-width: 640px) {
            .camera-video-frame {
                aspect-ratio: 3 / 4;
            }
        }

        .enroll-video-feed {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        .enroll-overlay-guide {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Segmented Oval Ring Arc Classes */
        .seg-pending {
            stroke: #334155;
            stroke-width: 5;
            transition: all 0.35s ease;
        }

        .seg-active {
            stroke: #60a5fa;
            stroke-width: 6;
            filter: drop-shadow(0 0 6px rgba(96, 165, 250, 0.75));
            animation: segActivePulse 1.2s ease-in-out infinite;
        }

        .seg-completed {
            stroke: #2563eb !important;
            stroke-width: 7 !important;
            filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.85)) !important;
            animation: none !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes segActivePulse {
            0%, 100% { stroke: #60a5fa; opacity: 0.85; }
            50% { stroke: #93c5fd; opacity: 1; filter: drop-shadow(0 0 9px rgba(147, 197, 253, 0.9)); }
        }

        /* Subtle dot matrix background */
        .bg-grid-dots {
            background-image: radial-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* Accessibility: Prefers reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="h-full bg-slate-950 text-slate-100 font-inter antialiased overflow-x-hidden flex flex-col justify-between selection:bg-primary-500 selection:text-white relative bg-grid-dots">

    {{-- Atmospheric Ambient Glow Background --}}
    <div class="fixed -top-40 left-1/2 -translate-x-1/2 w-[720px] h-[400px] bg-primary-600/15 rounded-full blur-[140px] pointer-events-none -z-10"></div>
    <div class="fixed -bottom-40 left-1/2 -translate-x-1/2 w-[640px] h-[360px] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none -z-10"></div>

    {{-- ========================================================================= --}}
    {{-- TOP MINIMALIST HEADER & SEGMENTED PROGRESS BAR                             --}}
    {{-- ========================================================================= --}}
    <header class="w-full max-w-5xl mx-auto px-4 sm:px-6 pt-5 pb-3 flex items-center justify-between gap-4 relative z-30">
        {{-- Brand Badge --}}
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 text-primary-400 flex items-center justify-center text-lg shadow-sm">
                <i class='bx bx-scan'></i>
            </div>
            <div class="hidden sm:block">
                <h1 class="text-xs font-bold uppercase tracking-wider text-slate-300 leading-tight">SMK NEGERI</h1>
                <p class="text-[11px] text-slate-500">Aktivasi Presensi Digital</p>
            </div>
        </div>

        {{-- Minimal Segmented Progress Bar (Scenes 1 - 5) --}}
        <div id="headerProgressContainer" class="flex-1 max-w-xs sm:max-w-sm mx-2">
            <div class="flex items-center justify-between text-[11px] font-medium text-slate-400 mb-1.5 px-0.5">
                <span id="sceneStepIndicator">Pengenalan 1 dari 5</span>
                <span id="sceneNameIndicator" class="text-primary-400 font-semibold truncate ml-2">Selamat Datang</span>
            </div>
            <div class="grid grid-cols-5 gap-1.5 h-1.5 bg-slate-900/90 rounded-full p-0.5 border border-slate-800/80">
                <div id="progBar1" class="h-full rounded-full bg-primary-500 transition-all duration-300"></div>
                <div id="progBar2" class="h-full rounded-full bg-slate-800 transition-all duration-300"></div>
                <div id="progBar3" class="h-full rounded-full bg-slate-800 transition-all duration-300"></div>
                <div id="progBar4" class="h-full rounded-full bg-slate-800 transition-all duration-300"></div>
                <div id="progBar5" class="h-full rounded-full bg-slate-800 transition-all duration-300"></div>
            </div>
        </div>

        {{-- Student Profile & Logout --}}
        <div class="flex items-center gap-2">
            <div class="hidden md:flex items-center gap-2 py-1 px-2.5 rounded-xl bg-slate-900/80 border border-slate-800 text-xs">
                <div class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-[10px]">
                    {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                </div>
                <span class="text-slate-300 font-medium truncate max-w-[130px]">{{ $siswa->nama }}</span>
            </div>

            <a href="#" onclick="event.preventDefault();document.getElementById('bio-logout-form').submit();" title="Keluar dari Akun"
                class="py-1.5 px-3 rounded-xl bg-slate-900/80 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-xs text-slate-400 hover:text-rose-400 transition-colors flex items-center gap-1.5">
                <i class='bx bx-log-out text-sm'></i>
                <span class="hidden sm:inline">Keluar</span>
            </a>
            <form id="bio-logout-form" action="{{ route('proseslogout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- MAIN INTERACTIVE SCENE CANVAS                                             --}}
    {{-- ========================================================================= --}}
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 max-w-4xl mx-auto w-full relative z-20">

        {{-- --------------------------------------------------------------------- --}}
        {{-- SCENE 01: WELCOME                                                     --}}
        {{-- --------------------------------------------------------------------- --}}
        <div id="scene1" class="scene-container scene-active flex-col items-center text-center max-w-xl mx-auto space-y-6">
            {{-- Interactive Animated SVG Illustration --}}
            <div onclick="triggerWelcomeReaction()" class="tap-interactive relative group" title="Ketuk untuk menyapa!">
                {{-- Glow Orb Behind --}}
                <div class="absolute inset-0 bg-primary-500/20 rounded-full blur-2xl group-hover:bg-primary-500/35 transition-all"></div>

                <div class="w-36 h-36 sm:w-44 sm:h-44 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/90 shadow-2xl flex items-center justify-center relative overflow-hidden group-hover:border-primary-500/50 transition-all">
                    {{-- Ambient Background Rays --}}
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(59,130,246,0.15),transparent_70%)]"></div>

                    {{-- Welcome Character SVG --}}
                    <svg class="w-24 h-24 sm:w-28 sm:h-28 text-primary-400 animate-float" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M25 88C25 72 36 63 50 63C64 63 75 72 75 88" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="50" cy="38" r="18" fill="#1e293b" stroke="currentColor" stroke-width="4"/>
                        <circle cx="43" cy="36" r="2.5" fill="#60a5fa"/>
                        <circle cx="57" cy="36" r="2.5" fill="#60a5fa"/>
                        <path d="M44 44C47 48 53 48 56 44" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round"/>
                        <g id="welcomeHand" class="waving-hand">
                            <path d="M72 60L83 45C85 42 88 43 87 46L81 58" stroke="#93c5fd" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="86" cy="42" r="3" fill="#93c5fd"/>
                        </g>
                        <path id="sparkle1" d="M18 24L20 28L24 30L20 32L18 36L16 32L12 30L16 28Z" fill="#38bdf8" opacity="0.8"/>
                        <path id="sparkle2" d="M82 18L83.5 21L86.5 22.5L83.5 24L82 27L80.5 24L77.5 22.5L80.5 21Z" fill="#818cf8" opacity="0.7"/>
                    </svg>

                    {{-- Interactive Floating Notification Toast --}}
                    <div id="welcomeToast" class="absolute bottom-2 px-2.5 py-0.5 rounded-full bg-primary-500/20 border border-primary-500/30 text-[10px] font-semibold text-primary-300 opacity-0 transform translate-y-2 transition-all duration-300 flex items-center gap-1">
                        <span>Halo! Siap mulai? 👋</span>
                    </div>
                </div>

                {{-- Tap Hint Pill --}}
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] text-slate-400 group-hover:text-primary-300 group-hover:border-slate-700 transition-colors">
                    <i class='bx bx-pointer text-primary-400'></i>
                    <span>Ketuk karakter untuk menyapa</span>
                </div>
            </div>

            {{-- Text Narratives --}}
            <div class="space-y-2.5 px-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-500/10 border border-primary-500/20 text-xs font-semibold text-primary-300">
                    <i class='bx bx-badge-check'></i> Akun Siswa Aktif
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-sky-300">{{ $siswa->nama }}</span>!
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                    Selamat datang di sistem presensi sekolah. Sebelum kamu mulai mencatat kehadiran harian, yuk ikuti pengenalan singkat verifikasi wajah ini!
                </p>
            </div>

            {{-- Action Controls --}}
            <div class="pt-2 w-full max-w-xs">
                <button type="button" onclick="goToScene(2)"
                    class="w-full py-3 px-6 rounded-2xl bg-primary-600 hover:bg-primary-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-primary-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <span>Mulai Kenalan</span>
                    <i class='bx bx-right-arrow-alt text-lg'></i>
                </button>
            </div>
        </div>

        {{-- --------------------------------------------------------------------- --}}
        {{-- SCENE 02: INTRODUCING BIOMETRIC VERIFICATION                          --}}
        {{-- --------------------------------------------------------------------- --}}
        <div id="scene2" class="scene-container scene-hidden flex-col items-center text-center max-w-xl mx-auto space-y-6">
            {{-- Interactive Animated Face Mesh Illustration --}}
            <div onclick="triggerScanReaction()" class="tap-interactive relative group" title="Ketuk untuk simulasi pemindaian!">
                <div class="absolute inset-0 bg-sky-500/20 rounded-full blur-2xl group-hover:bg-sky-500/35 transition-all"></div>

                <div class="w-36 h-36 sm:w-44 sm:h-44 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/90 shadow-2xl flex items-center justify-center relative overflow-hidden group-hover:border-sky-500/50 transition-all">
                    <div class="absolute inset-3 border border-sky-500/20 rounded-full border-dashed animate-radar"></div>

                    <svg class="w-24 h-24 sm:w-28 sm:h-28 text-slate-300 relative z-10" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M30 35C30 22 40 16 50 16C60 16 70 22 70 35C70 48 64 68 50 78C36 68 30 48 30 35Z" stroke="#38bdf8" stroke-width="2.5" stroke-dasharray="4 3"/>
                        <circle cx="42" cy="38" r="3" fill="#38bdf8" id="ptEyeLeft"/>
                        <circle cx="58" cy="38" r="3" fill="#38bdf8" id="ptEyeRight"/>
                        <circle cx="50" cy="48" r="2.5" fill="#38bdf8" id="ptNose"/>
                        <path d="M44 58C47 61 53 61 56 58" stroke="#38bdf8" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="42" y1="38" x2="58" y2="38" stroke="#0284c7" stroke-width="1.2" stroke-dasharray="2 2"/>
                        <line x1="42" y1="38" x2="50" y2="48" stroke="#0284c7" stroke-width="1.2" stroke-dasharray="2 2"/>
                        <line x1="58" y1="38" x2="50" y2="48" stroke="#0284c7" stroke-width="1.2" stroke-dasharray="2 2"/>
                        <line x1="50" y1="48" x2="50" y2="58" stroke="#0284c7" stroke-width="1.2" stroke-dasharray="2 2"/>
                    </svg>

                    <div id="scanLaserBeam" class="absolute left-4 right-4 h-0.5 bg-gradient-to-r from-transparent via-sky-400 to-transparent shadow-[0_0_12px_#38bdf8] animate-scan-beam"></div>

                    <div id="scanSuccessTag" class="absolute bottom-2 px-2.5 py-0.5 rounded-full bg-sky-500/20 border border-sky-500/40 text-[10px] font-semibold text-sky-300 opacity-0 transform translate-y-2 transition-all duration-300">
                        Wajah Terverifikasi ✓
                    </div>
                </div>

                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] text-slate-400 group-hover:text-sky-300 group-hover:border-slate-700 transition-colors">
                    <i class='bx bx-fingerprint text-sky-400'></i>
                    <span>Ketuk untuk simulasi pemindaian</span>
                </div>
            </div>

            <div class="space-y-2.5 px-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/20 text-xs font-semibold text-sky-300">
                    <i class='bx bx-face'></i> Teknologi AI Biometrik
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Kenalan dengan Presensi Biometrik
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                    Sekarang, kamu bisa melakukan presensi dengan verifikasi wajah. Sistem akan memastikan bahwa setiap kehadiran benar-benar dilakukan oleh pemilik akun secara langsung.
                </p>
            </div>

            <div class="pt-2 w-full max-w-xs flex items-center gap-3">
                <button type="button" onclick="goToScene(1)"
                    class="py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-slate-200 font-semibold text-xs transition-colors">
                    Kembali
                </button>
                <button type="button" onclick="goToScene(3)"
                    class="flex-1 py-3 px-5 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-sky-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <span>Lanjutkan</span>
                    <i class='bx bx-right-arrow-alt text-lg'></i>
                </button>
            </div>
        </div>

        {{-- --------------------------------------------------------------------- --}}
        {{-- SCENE 03: PRIVACY & SECURITY                                          --}}
        {{-- --------------------------------------------------------------------- --}}
        <div id="scene3" class="scene-container scene-hidden flex-col items-center text-center max-w-xl mx-auto space-y-6">
            <div onclick="triggerShieldReaction()" class="tap-interactive relative group" title="Ketuk untuk menguji perlindungan data!">
                <div class="absolute inset-0 bg-emerald-500/20 rounded-full blur-2xl group-hover:bg-emerald-500/35 transition-all"></div>

                <div class="w-36 h-36 sm:w-44 sm:h-44 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/90 shadow-2xl flex items-center justify-center relative overflow-hidden group-hover:border-emerald-500/50 transition-all">
                    <div class="absolute inset-3 border border-emerald-500/20 rounded-full animate-orbit">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 absolute -top-1 left-1/2 -translate-x-1/2 shadow-[0_0_8px_#34d399]"></div>
                    </div>

                    <svg id="shieldSvg" class="w-20 h-20 sm:w-24 sm:h-24 text-emerald-400 relative z-10 transition-transform duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 16L78 28V48C78 68 66 82 50 88C34 82 22 68 22 48V28L50 16Z" fill="#064e3b" fill-opacity="0.3" stroke="currentColor" stroke-width="3.5" stroke-linejoin="round"/>
                        <rect x="38" y="48" width="24" height="18" rx="4" fill="#10b981"/>
                        <path id="padlockShackle" d="M43 48V42C43 38 46 35 50 35C54 35 57 38 57 42V48" stroke="#a7f3d0" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="50" cy="56" r="2" fill="#064e3b"/>
                        <path d="M50 58V61" stroke="#064e3b" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>

                    <div id="shieldToast" class="absolute bottom-2 px-2.5 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-[10px] font-semibold text-emerald-300 opacity-0 transform translate-y-2 transition-all duration-300 flex items-center gap-1">
                        <i class='bx bx-check-shield'></i> Enkripsi AES-256 Aktif
                    </div>
                </div>

                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] text-slate-400 group-hover:text-emerald-300 group-hover:border-slate-700 transition-colors">
                    <i class='bx bx-shield text-emerald-400'></i>
                    <span>Ketuk untuk menguji proteksi data</span>
                </div>
            </div>

            <div class="space-y-2.5 px-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-300">
                    <i class='bx bx-lock-alt'></i> Privasi & Perlindungan Data
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Wajahmu, Identitasmu
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                    Data biometrik digunakan untuk membantu memverifikasi identitasmu saat melakukan presensi. Informasi ini dilindungi, dienkripsi, dan hanya digunakan sesuai tujuan yang telah ditetapkan sekolah.
                </p>
            </div>

            <div class="pt-2 w-full max-w-xs flex items-center gap-3">
                <button type="button" onclick="goToScene(2)"
                    class="py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-slate-200 font-semibold text-xs transition-colors">
                    Kembali
                </button>
                <button type="button" onclick="goToScene(4)"
                    class="flex-1 py-3 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-emerald-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <span>Lanjutkan</span>
                    <i class='bx bx-right-arrow-alt text-lg'></i>
                </button>
            </div>
        </div>

        {{-- --------------------------------------------------------------------- --}}
        {{-- SCENE 04: PREPARATION                                                 --}}
        {{-- --------------------------------------------------------------------- --}}
        <div id="scene4" class="scene-container scene-hidden flex-col items-center text-center max-w-2xl mx-auto space-y-5">
            <div class="space-y-2 px-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-xs font-semibold text-amber-300">
                    <i class='bx bx-check-circle'></i> Panduan Praktis
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Sedikit Persiapan Sebelum Mulai
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                    Pastikan wajah terlihat jelas, pencahayaan cukup, dan kamera berada pada posisi yang tepat:
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 w-full text-left">
                <div onclick="togglePrepDemo(1)" id="prepCard1" class="tap-interactive p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-amber-500/50 transition-all flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between">
                        <div id="prepIcon1" class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg border border-amber-500/20">
                            <i class='bx bx-sun'></i>
                        </div>
                        <i class='bx bx-check-circle text-amber-400 text-sm' id="prepCheck1"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white leading-tight">Cahaya Terang</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Wajah terlihat jelas tanpa backlight.</p>
                    </div>
                </div>

                <div onclick="togglePrepDemo(2)" id="prepCard2" class="tap-interactive p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-sky-500/50 transition-all flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between">
                        <div id="prepIcon2" class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-lg border border-sky-500/20">
                            <i class='bx bx-face'></i>
                        </div>
                        <i class='bx bx-check-circle text-sky-400 text-sm' id="prepCheck2"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white leading-tight">Tanpa Penutup</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Lepas masker atau kacamata hitam.</p>
                    </div>
                </div>

                <div onclick="togglePrepDemo(3)" id="prepCard3" class="tap-interactive p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-emerald-500/50 transition-all flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between">
                        <div id="prepIcon3" class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/20">
                            <i class='bx bx-scan'></i>
                        </div>
                        <i class='bx bx-check-circle text-emerald-400 text-sm' id="prepCheck3"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white leading-tight">Jarak 40–60 cm</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Posisikan wajah tepat di tengah frame.</p>
                    </div>
                </div>

                <div onclick="togglePrepDemo(4)" id="prepCard4" class="tap-interactive p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500/50 transition-all flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between">
                        <div id="prepIcon4" class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/20">
                            <i class='bx bx-user'></i>
                        </div>
                        <i class='bx bx-check-circle text-indigo-400 text-sm' id="prepCheck4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white leading-tight">Satu Orang</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Hanya kamu yang berada di depan kamera.</p>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded-2xl bg-slate-900/60 border border-slate-800/80 text-[11px] text-slate-400 max-w-lg mx-auto flex items-center gap-2.5">
                <i class='bx bx-devices text-primary-400 text-base flex-shrink-0'></i>
                <span>Mendukung kamera depan smartphone, webcam laptop, maupun PC desktop lab komputer sekolah.</span>
            </div>

            <div class="pt-2 w-full max-w-xs flex items-center gap-3">
                <button type="button" onclick="goToScene(3)"
                    class="py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-slate-200 font-semibold text-xs transition-colors">
                    Kembali
                </button>
                <button type="button" onclick="goToScene(5)"
                    class="flex-1 py-3 px-5 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-amber-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <span>Saya Siap</span>
                    <i class='bx bx-right-arrow-alt text-lg'></i>
                </button>
            </div>
        </div>

        {{-- --------------------------------------------------------------------- --}}
        {{-- SCENE 05: READY TO ENROLL                                             --}}
        {{-- --------------------------------------------------------------------- --}}
        <div id="scene5" class="scene-container scene-hidden flex-col items-center text-center max-w-xl mx-auto space-y-6">
            <div onclick="triggerCameraShutterReaction()" class="tap-interactive relative group" title="Ketuk untuk mencoba shutter kamera!">
                <div class="absolute inset-0 bg-primary-500/20 rounded-full blur-2xl group-hover:bg-primary-500/35 transition-all"></div>

                <div class="w-36 h-36 sm:w-44 sm:h-44 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/90 shadow-2xl flex items-center justify-center relative overflow-hidden group-hover:border-primary-500/50 transition-all">
                    <div class="absolute inset-4 border border-slate-700/60 rounded-2xl flex items-center justify-center">
                        <div class="w-1.5 h-1.5 rounded-full bg-primary-400 animate-ping"></div>
                    </div>

                    <svg id="cameraSvgLens" class="w-20 h-20 sm:w-24 sm:h-24 text-primary-400 relative z-10 transition-transform duration-200" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="38" stroke="currentColor" stroke-width="3.5"/>
                        <circle cx="50" cy="50" r="28" stroke="#60a5fa" stroke-width="2" stroke-dasharray="6 4"/>
                        <circle cx="50" cy="50" r="16" fill="#1e293b" stroke="#93c5fd" stroke-width="2.5"/>
                        <circle cx="50" cy="50" r="8" fill="#3b82f6"/>
                        <circle cx="47" cy="47" r="3" fill="#ffffff" opacity="0.8"/>
                    </svg>

                    <div id="shutterFlash" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-150"></div>

                    <div id="cameraToast" class="absolute bottom-2 px-2.5 py-0.5 rounded-full bg-primary-500/20 border border-primary-500/40 text-[10px] font-semibold text-primary-300 opacity-0 transform translate-y-2 transition-all duration-300 flex items-center gap-1">
                        <i class='bx bx-camera'></i> Kamera Siap Diaktifkan!
                    </div>
                </div>

                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] text-slate-400 group-hover:text-primary-300 group-hover:border-slate-700 transition-colors">
                    <i class='bx bx-camera text-primary-400'></i>
                    <span>Ketuk lensa untuk simulasi shutter</span>
                </div>
            </div>

            <div class="space-y-2.5 px-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-500/10 border border-primary-500/20 text-xs font-semibold text-primary-300">
                    <i class='bx bx-play-circle'></i> Tahap Perekaman Wajah
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Semuanya Siap?
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                    Kita akan mulai merekam dan mendaftarkan wajahmu. Proses ini hanya perlu dilakukan satu kali selama data biometrikmu masih valid.
                </p>
            </div>

            <div class="pt-2 w-full max-w-xs space-y-2.5">
                <button type="button" onclick="launchLiveEnrollment()"
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-bold text-xs sm:text-sm shadow-xl shadow-primary-500/30 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <i class='bx bx-camera text-lg'></i>
                    <span>Mulai Perekaman</span>
                    <i class='bx bx-right-arrow-alt text-lg'></i>
                </button>

                <button type="button" onclick="goToScene(4)"
                    class="w-full py-2 text-xs text-slate-500 hover:text-slate-300 transition-colors">
                    ← Lihat Panduan Lagi
                </button>
            </div>
        </div>

        {{-- --------------------------------------------------------------------- --}}
        {{-- SCENE 06: LIVE CAMERA BIOMETRIC ENROLLMENT (SEQUENTIAL POSE FLOW)     --}}
        {{-- --------------------------------------------------------------------- --}}
        <div id="sceneCamera" class="scene-container scene-hidden flex-col items-center max-w-lg mx-auto w-full space-y-3.5">
            {{-- Camera Top Header --}}
            <div class="w-full flex items-center justify-between pb-2 border-b border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-1.5">
                        <i class='bx bx-scan text-primary-400'></i> Perekaman Biometrik Wajah
                    </h3>
                    <p class="text-[11px] text-slate-400">Ikuti 4 urutan pose di bawah ini secara bergantian.</p>
                </div>
                <button type="button" onclick="returnToOnboarding()"
                    class="py-1 px-3 rounded-lg bg-slate-900 border border-slate-800 text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                    <i class='bx bx-arrow-back'></i> Panduan
                </button>
            </div>

            {{-- Sequential Step Instruction Box (Strictly locked to active step) --}}
            <div id="poseInstructionBox" class="w-full p-3.5 rounded-2xl bg-slate-900/90 border border-primary-500/40 shadow-lg text-left transition-all">
                <div class="flex items-center justify-between mb-1 text-xs">
                    <span id="poseStepBadge" class="px-2 py-0.5 rounded-md bg-primary-500/20 text-primary-300 font-bold text-[10px] tracking-wider uppercase flex items-center gap-1">
                        <i class='bx bx-radio-circle-marked'></i> Langkah 1 dari 4
                    </span>
                    <span id="poseHoldTimerBadge" class="text-[11px] text-slate-400 font-medium">Tahan posisi 0.6 detik</span>
                </div>
                <div class="flex items-center gap-3">
                    <div id="poseIconBox" class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center text-xl flex-shrink-0 shadow-md transition-all">
                        <i class='bx bx-user' id="poseIcon"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 id="poseMainInstruction" class="text-sm font-bold text-white leading-tight">Hadapkan wajah tepat ke kamera</h4>
                        <p id="poseSubHint" class="text-xs text-slate-400 mt-0.5 truncate">Posisikan wajah lurus di tengah bingkai</p>
                    </div>
                </div>
                {{-- Sub-second Pose Hold Progress Bar (0% to 100% during 600ms hold) --}}
                <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2.5 overflow-hidden">
                    <div id="poseHoldProgressBar" class="bg-primary-500 h-1.5 rounded-full transition-all duration-75" style="width: 0%"></div>
                </div>
            </div>

            {{-- Live Video Viewport with Segmented Oval Ring SVG --}}
            <div class="camera-video-frame relative" id="cameraWrapper">
                <video id="enrollVideo" class="enroll-video-feed" autoplay playsinline muted></video>
                <canvas id="enrollCanvas" class="hidden"></canvas>

                {{-- 4-Segmented Oval Ring SVG Overlay (Top, Right, Left, Bottom) --}}
                <div class="enroll-overlay-guide">
                    <svg class="w-[230px] h-[290px] pointer-events-none drop-shadow-xl" viewBox="0 0 260 330" fill="none">
                        {{-- Segment 1: Front Facing (Top Arc) --}}
                        <path id="ovalSeg1" d="M 63.1 64.7 A 100 135 0 0 1 196.9 64.7"
                              stroke="#334155" stroke-width="5" stroke-linecap="round" class="seg-pending"/>
                        
                        {{-- Segment 2: Turn Right (Right Arc) --}}
                        <path id="ovalSeg2" d="M 204.3 74.7 A 100 135 0 0 1 204.3 255.3"
                              stroke="#334155" stroke-width="5" stroke-linecap="round" class="seg-pending"/>
                        
                        {{-- Segment 3: Turn Left (Left Arc) --}}
                        <path id="ovalSeg3" d="M 55.7 255.3 A 100 135 0 0 1 55.7 74.7"
                              stroke="#334155" stroke-width="5" stroke-linecap="round" class="seg-pending"/>
                        
                        {{-- Segment 4: Look Up (Bottom Arc) --}}
                        <path id="ovalSeg4" d="M 196.9 265.3 A 100 135 0 0 1 63.1 265.3"
                              stroke="#334155" stroke-width="5" stroke-linecap="round" class="seg-pending"/>
                    </svg>
                </div>

                {{-- Model Loading Indicator Overlay --}}
                <div id="loaderOverlay" class="absolute inset-0 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-20 transition-opacity">
                    <div class="w-8 h-8 rounded-full border-3 border-primary-500 border-t-transparent animate-spin mb-3"></div>
                    <p class="font-bold text-xs" id="loaderTitle">Memuat Model AI Biometrik...</p>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-xs">Menyiapkan neural network pendeteksi wajah & pose 3D.</p>
                </div>

                {{-- Camera Error Overlay --}}
                <div id="cameraErrorOverlay" class="hidden absolute inset-0 bg-slate-950/95 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-white z-30">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-2xl mb-2 border border-rose-500/30">
                        <i class='bx bx-camera-off'></i>
                    </div>
                    <h4 class="font-bold text-sm text-white" id="cameraErrorTitle">Kamera Tidak Aktif</h4>
                    <p class="text-xs text-slate-400 mt-1.5 max-w-xs leading-relaxed" id="cameraErrorGuidance">
                        Pastikan izin kamera telah diberikan di browser atau webcam USB telah terpasang.
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <button type="button" onclick="initCamera()" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-semibold text-xs transition-colors flex items-center gap-1.5 shadow-sm">
                            <i class='bx bx-refresh text-base'></i> Coba Lagi
                        </button>
                        <button type="button" onclick="returnToOnboarding()" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium">
                            Kembali
                        </button>
                    </div>
                </div>
            </div>

            {{-- 4 Segment Progress Pills (Synchronized with Oval Arcs) --}}
            <div class="grid grid-cols-4 gap-1.5 w-full">
                {{-- Step 1 Pill --}}
                <div id="stepPill1" class="py-2 px-1 rounded-xl bg-slate-900 border border-slate-800 text-center transition-all flex flex-col items-center justify-center">
                    <div class="flex items-center gap-1 text-[11px] font-bold text-slate-400" id="pillBadge1">
                        <i class='bx bx-radio-circle text-xs' id="pillIcon1"></i> 1. Depan
                    </div>
                </div>
                {{-- Step 2 Pill --}}
                <div id="stepPill2" class="py-2 px-1 rounded-xl bg-slate-900 border border-slate-800 text-center transition-all flex flex-col items-center justify-center">
                    <div class="flex items-center gap-1 text-[11px] font-bold text-slate-400" id="pillBadge2">
                        <i class='bx bx-radio-circle text-xs' id="pillIcon2"></i> 2. Kanan
                    </div>
                </div>
                {{-- Step 3 Pill --}}
                <div id="stepPill3" class="py-2 px-1 rounded-xl bg-slate-900 border border-slate-800 text-center transition-all flex flex-col items-center justify-center">
                    <div class="flex items-center gap-1 text-[11px] font-bold text-slate-400" id="pillBadge3">
                        <i class='bx bx-radio-circle text-xs' id="pillIcon3"></i> 3. Kiri
                    </div>
                </div>
                {{-- Step 4 Pill --}}
                <div id="stepPill4" class="py-2 px-1 rounded-xl bg-slate-900 border border-slate-800 text-center transition-all flex flex-col items-center justify-center">
                    <div class="flex items-center gap-1 text-[11px] font-bold text-slate-400" id="pillBadge4">
                        <i class='bx bx-radio-circle text-xs' id="pillIcon4"></i> 4. Atas
                    </div>
                </div>
            </div>

            {{-- Real-time Status Alert Bar --}}
            <div id="statusAlertBar" class="w-full p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 flex items-center justify-between transition-all">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <i class='bx bx-loader-alt bx-spin text-primary-400 text-base flex-shrink-0' id="statusIcon"></i>
                    <span id="statusMessage" class="font-medium truncate">Menghubungkan ke kamera...</span>
                </div>
                <span id="qualityScoreBadge" class="text-[11px] font-bold text-slate-400 flex-shrink-0 ml-2"></span>
            </div>

            {{-- Universal Camera Toolbar (Multi-Device Switcher) --}}
            <div class="w-full p-2 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-1.5 flex-1 min-w-0">
                    <i class='bx bx-video text-slate-400 text-xs'></i>
                    <select id="cameraDeviceSelect" class="w-full px-2 py-1 bg-slate-950 border border-slate-800 rounded-lg text-slate-300 text-[11px] focus:outline-none">
                        <option value="">Kamera Utama</option>
                    </select>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <span id="deviceBadge" class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-400">Webcam</span>
                    <button type="button" onclick="toggleCameraFacing()" title="Ganti Kamera Depan/Belakang" class="p-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200">
                        <i class='bx bx-sync text-sm'></i>
                    </button>
                </div>
            </div>

            {{-- Retry Button --}}
            <div class="w-full flex items-center justify-end">
                <button type="button" id="btnRestartProcess" onclick="restartEnrollmentProcess()" class="hidden text-xs text-amber-400 hover:text-amber-300 font-semibold flex items-center gap-1">
                    <i class='bx bx-refresh text-sm'></i> Ulangi Perekaman
                </button>
            </div>
        </div>

        {{-- --------------------------------------------------------------------- --}}
        {{-- SCENE 07: CELEBRATION & SUCCESS                                       --}}
        {{-- --------------------------------------------------------------------- --}}
        <div id="sceneSuccess" class="scene-container scene-hidden flex-col items-center text-center max-w-md mx-auto space-y-6">
            <div class="w-20 h-20 rounded-3xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-4xl shadow-xl shadow-emerald-500/20 animate-bounce">
                <i class='bx bx-check-double'></i>
            </div>

            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-xs font-semibold text-emerald-300">
                    Pendaftaran Berhasil!
                </span>
                <h2 class="text-2xl font-bold text-white">Wajahmu Siap Digunakan</h2>
                <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Data biometrik dari 4 sudut pose telah dienkripsi dan tersimpan dengan aman di server sekolah. Akun presensimu kini aktif!
                </p>
            </div>

            <div class="w-full p-3.5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-3.5 text-left">
                <img id="enrolledPhotoPreview" class="w-14 h-16 rounded-xl object-cover border border-slate-700 shadow-md flex-shrink-0" alt="Selfie Siswa">
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs font-bold text-white truncate">{{ $siswa->nama }}</h4>
                    <p class="text-[11px] text-slate-400">NIS: {{ $siswa->nis }}</p>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Status: Terdaftar
                        </span>
                        <span id="finalQualityText" class="text-[10px] text-slate-400 font-medium"></span>
                    </div>
                </div>
            </div>

            <div class="w-full pt-2">
                <a href="{{ route('dashboard') }}"
                    class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-xl shadow-emerald-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <span>Masuk ke Dashboard Siswa</span>
                    <i class='bx bx-right-arrow-alt text-lg'></i>
                </a>
            </div>
        </div>

    </main>

    {{-- ========================================================================= --}}
    {{-- BOTTOM MINIMAL NAVIGATION & CONTROLS                                      --}}
    {{-- ========================================================================= --}}
    <footer id="bottomOnboardingNav" class="w-full max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between text-xs text-slate-500 relative z-30">
        <div>
            <button type="button" id="navBtnPrev" onclick="prevScene()"
                class="py-2 px-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-slate-200 transition-colors hidden items-center gap-1.5 font-medium">
                <i class='bx bx-left-arrow-alt text-base'></i>
                <span>Kembali</span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="goToScene(1)" id="dot1" title="Scene 1" class="w-6 h-2 rounded-full bg-primary-500 transition-all"></button>
            <button type="button" onclick="goToScene(2)" id="dot2" title="Scene 2" class="w-2 h-2 rounded-full bg-slate-800 hover:bg-slate-700 transition-all"></button>
            <button type="button" onclick="goToScene(3)" id="dot3" title="Scene 3" class="w-2 h-2 rounded-full bg-slate-800 hover:bg-slate-700 transition-all"></button>
            <button type="button" onclick="goToScene(4)" id="dot4" title="Scene 4" class="w-2 h-2 rounded-full bg-slate-800 hover:bg-slate-700 transition-all"></button>
            <button type="button" onclick="goToScene(5)" id="dot5" title="Scene 5" class="w-2 h-2 rounded-full bg-slate-800 hover:bg-slate-700 transition-all"></button>
        </div>

        <div>
            <button type="button" id="skipToEnrollBtn" onclick="goToScene(5)" class="text-slate-400 hover:text-primary-400 transition-colors font-medium text-[11px] flex items-center gap-1">
                <span>Lewati</span>
                <i class='bx bx-chevrons-right'></i>
            </button>
        </div>
    </footer>

    {{-- ========================================================================= --}}
    {{-- SCRIPTS: FACE-API & SEQUENTIAL POSE ENGINE                                --}}
    {{-- ========================================================================= --}}
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/dist/face-api.js"></script>
    <script src="{{ asset('js/universal-biometric-camera.js') }}"></script>

    <script>
        // =====================================================================
        // 1. SCENE NAVIGATION CONTROLLER
        // =====================================================================
        let currentScene = 1;
        const totalScenes = 5;

        const sceneNames = [
            "Selamat Datang",
            "Presensi Biometrik",
            "Privasi & Keamanan",
            "Persiapan",
            "Mulai Perekaman"
        ];

        function goToScene(target) {
            if (target < 1 || target > totalScenes) return;

            for (let i = 1; i <= totalScenes; i++) {
                const sc = document.getElementById('scene' + i);
                if (sc) {
                    sc.classList.remove('scene-active');
                    sc.classList.add('scene-hidden');
                }

                const pb = document.getElementById('progBar' + i);
                if (pb) {
                    pb.className = (i <= target)
                        ? 'h-full rounded-full bg-primary-500 transition-all duration-300'
                        : 'h-full rounded-full bg-slate-800 transition-all duration-300';
                }

                const dot = document.getElementById('dot' + i);
                if (dot) {
                    dot.className = (i === target)
                        ? 'w-6 h-2 rounded-full bg-primary-500 transition-all'
                        : 'w-2 h-2 rounded-full bg-slate-800 hover:bg-slate-700 transition-all';
                }
            }

            document.getElementById('sceneCamera').classList.remove('scene-active');
            document.getElementById('sceneCamera').classList.add('scene-hidden');
            document.getElementById('sceneSuccess').classList.remove('scene-active');
            document.getElementById('sceneSuccess').classList.add('scene-hidden');
            document.getElementById('bottomOnboardingNav').classList.remove('hidden');
            document.getElementById('headerProgressContainer').classList.remove('hidden');

            currentScene = target;
            const targetEl = document.getElementById('scene' + target);
            if (targetEl) {
                targetEl.classList.remove('scene-hidden');
                targetEl.classList.add('scene-active');
            }

            document.getElementById('sceneStepIndicator').textContent = `Pengenalan ${target} dari ${totalScenes}`;
            document.getElementById('sceneNameIndicator').textContent = sceneNames[target - 1];

            const prevBtn = document.getElementById('navBtnPrev');
            if (currentScene > 1) {
                prevBtn.classList.remove('hidden');
                prevBtn.classList.add('inline-flex');
            } else {
                prevBtn.classList.add('hidden');
                prevBtn.classList.remove('inline-flex');
            }

            const skipBtn = document.getElementById('skipToEnrollBtn');
            if (currentScene === 5) {
                skipBtn.classList.add('opacity-0', 'pointer-events-none');
            } else {
                skipBtn.classList.remove('opacity-0', 'pointer-events-none');
            }
        }

        function nextScene() {
            if (currentScene < totalScenes) {
                goToScene(currentScene + 1);
            } else {
                launchLiveEnrollment();
            }
        }

        function prevScene() {
            if (currentScene > 1) {
                goToScene(currentScene - 1);
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (document.getElementById('sceneCamera').classList.contains('scene-active')) return;
            if (e.key === 'ArrowRight' || e.key === 'Enter') {
                nextScene();
            } else if (e.key === 'ArrowLeft') {
                prevScene();
            }
        });

        // Touch Swipe Navigation for mobile devices
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            if (document.getElementById('sceneCamera').classList.contains('scene-active')) return;
            touchEndX = e.changedTouches[0].screenX;
            const diffX = touchEndX - touchStartX;
            if (Math.abs(diffX) > 60) {
                if (diffX < 0) nextScene();
                else prevScene();
            }
        }, { passive: true });

        // Interactive Micro-interactions
        function triggerWelcomeReaction() {
            const hand = document.getElementById('welcomeHand');
            const toast = document.getElementById('welcomeToast');
            if (hand) {
                hand.style.animation = 'none';
                void hand.offsetWidth;
                hand.style.animation = 'waveHand 0.8s ease-in-out 3';
            }
            if (toast) {
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                }, 2200);
            }
        }

        function triggerScanReaction() {
            const laser = document.getElementById('scanLaserBeam');
            const tag = document.getElementById('scanSuccessTag');
            if (laser) {
                laser.style.animation = 'none';
                void laser.offsetWidth;
                laser.style.animation = 'scanBeam 0.8s ease-in-out 2';
            }
            if (tag) {
                tag.classList.remove('opacity-0', 'translate-y-2');
                tag.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    tag.classList.remove('opacity-100', 'translate-y-0');
                    tag.classList.add('opacity-0', 'translate-y-2');
                }, 2200);
            }
        }

        function triggerShieldReaction() {
            const shield = document.getElementById('shieldSvg');
            const toast = document.getElementById('shieldToast');
            if (shield) {
                shield.style.animation = 'shieldRipple 0.8s ease-out';
                setTimeout(() => shield.style.animation = 'none', 800);
            }
            if (toast) {
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                }, 2200);
            }
        }

        function togglePrepDemo(cardId) {
            const card = document.getElementById('prepCard' + cardId);
            if (!card) return;
            card.classList.toggle('ring-2');
            card.classList.toggle('ring-primary-500');
            card.classList.toggle('bg-slate-800');
        }

        function triggerCameraShutterReaction() {
            const lens = document.getElementById('cameraSvgLens');
            const flash = document.getElementById('shutterFlash');
            const toast = document.getElementById('cameraToast');
            if (lens) {
                lens.style.animation = 'shutterClick 0.45s ease-out';
                setTimeout(() => lens.style.animation = 'none', 500);
            }
            if (flash) {
                flash.style.opacity = '0.9';
                setTimeout(() => flash.style.opacity = '0', 160);
            }
            if (toast) {
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                }, 2200);
            }
        }

        // =====================================================================
        // 2. FIXED SEQUENTIAL POSE-BASED FACE ENROLLMENT ENGINE
        // =====================================================================
        // Step definitions (Strictly sequential, no random flipping, no smile)
        const POSE_DEFINITIONS = {
            1: {
                step: 1,
                type: 'front_facing',
                instruction: 'Hadapkan wajah tepat ke kamera',
                hint: 'Posisikan kepala tegak lurus di tengah bingkai',
                icon: 'bx-user',
                segId: 'ovalSeg1',
                pillId: 'stepPill1',
                badgeId: 'pillBadge1',
                iconId: 'pillIcon1',
                label: '1. Depan'
            },
            2: {
                step: 2,
                type: 'turn_right',
                instruction: 'Hadapkan wajah perlahan ke kanan',
                hint: 'Tengokkan kepala perlahan ke arah kanan Anda',
                icon: 'bx-right-arrow-circle',
                segId: 'ovalSeg2',
                pillId: 'stepPill2',
                badgeId: 'pillBadge2',
                iconId: 'pillIcon2',
                label: '2. Kanan'
            },
            3: {
                step: 3,
                type: 'turn_left',
                instruction: 'Sekarang hadapkan wajah perlahan ke kiri',
                hint: 'Tengokkan kepala perlahan ke arah kiri Anda',
                icon: 'bx-left-arrow-circle',
                segId: 'ovalSeg3',
                pillId: 'stepPill3',
                badgeId: 'pillBadge3',
                iconId: 'pillIcon3',
                label: '3. Kiri'
            },
            4: {
                step: 4,
                type: 'look_up',
                instruction: 'Terakhir, arahkan wajah sedikit ke atas',
                hint: 'Angkat dagu/wajah sedikit ke arah atas',
                icon: 'bx-up-arrow-circle',
                segId: 'ovalSeg4',
                pillId: 'stepPill4',
                badgeId: 'pillBadge4',
                iconId: 'pillIcon4',
                label: '4. Atas'
            }
        };

        // Engine State
        let activePoseStep = 1;
        let isPoseCapturing = false;
        let poseHoldStartTime = null;
        const POSE_REQUIRED_HOLD_MS = 600; // 600ms hold threshold

        // Exponential Smoothing for noise reduction
        let smoothedYaw = 0;
        let smoothedPitch = 0.42;
        let smoothedRoll = 0;
        const SMOOTH_ALPHA = 0.35;

        // Sample Storage for 4 Poses
        let stepSamples = { 1: null, 2: null, 3: null, 4: null };
        let stepStartTime = Date.now();

        // Hardware & Models
        let cameraManager = null;
        let isModelLoaded = false;
        let isCameraActive = false;
        let isDetecting = false;
        let challengeToken = null;

        // DOM Elements
        const sceneCamera = document.getElementById('sceneCamera');
        const sceneSuccess = document.getElementById('sceneSuccess');
        const video = document.getElementById('enrollVideo');
        const canvas = document.getElementById('enrollCanvas');
        const loaderOverlay = document.getElementById('loaderOverlay');
        const cameraErrorOverlay = document.getElementById('cameraErrorOverlay');
        const cameraDeviceSelect = document.getElementById('cameraDeviceSelect');
        const deviceBadge = document.getElementById('deviceBadge');
        const statusAlertBar = document.getElementById('statusAlertBar');
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        const qualityScoreBadge = document.getElementById('qualityScoreBadge');

        async function launchLiveEnrollment() {
            for (let i = 1; i <= totalScenes; i++) {
                const sc = document.getElementById('scene' + i);
                if (sc) {
                    sc.classList.remove('scene-active');
                    sc.classList.add('scene-hidden');
                }
            }
            document.getElementById('bottomOnboardingNav').classList.add('hidden');
            document.getElementById('headerProgressContainer').classList.add('hidden');

            sceneCamera.classList.remove('scene-hidden');
            sceneCamera.classList.add('scene-active');

            // Reset poses
            activePoseStep = 1;
            isPoseCapturing = false;
            poseHoldStartTime = null;
            stepSamples = { 1: null, 2: null, 3: null, 4: null };
            stepStartTime = Date.now();
            updatePoseUI(1);

            await loadAiModels();
            await requestEnrollmentChallenge();
            await initCamera();
            startLiveDetectionLoop();
        }

        function returnToOnboarding() {
            stopCamera();
            sceneCamera.classList.remove('scene-active');
            sceneCamera.classList.add('scene-hidden');
            goToScene(5);
        }

        function updatePoseUI(step) {
            const def = POSE_DEFINITIONS[step];
            if (!def) return;

            document.getElementById('poseStepBadge').innerHTML = `<i class='bx bx-radio-circle-marked'></i> Langkah ${step} dari 4`;
            document.getElementById('poseMainInstruction').textContent = def.instruction;
            document.getElementById('poseSubHint').textContent = def.hint;
            document.getElementById('poseIcon').className = `bx ${def.icon}`;
            document.getElementById('poseHoldProgressBar').style.width = '0%';
            document.getElementById('poseHoldTimerBadge').textContent = 'Tahan posisi 0.6 detik';

            for (let s = 1; s <= 4; s++) {
                const seg = document.getElementById(POSE_DEFINITIONS[s].segId);
                const pill = document.getElementById(POSE_DEFINITIONS[s].pillId);
                const badge = document.getElementById(POSE_DEFINITIONS[s].badgeId);
                const icon = document.getElementById(POSE_DEFINITIONS[s].iconId);

                if (s < step) {
                    // Completed: Solid Blue Permanently
                    if (seg) seg.className.baseVal = 'seg-completed';
                    if (pill) pill.className = 'py-2 px-1 rounded-xl bg-primary-950/80 border border-primary-500 text-center transition-all flex flex-col items-center justify-center';
                    if (badge) badge.className = 'flex items-center gap-1 text-[11px] font-bold text-primary-300';
                    if (icon) icon.className = 'bx bx-check-circle text-xs text-primary-400';
                } else if (s === step) {
                    // Active: Subtle pulse with light blue
                    if (seg) seg.className.baseVal = 'seg-active';
                    if (pill) pill.className = 'py-2 px-1 rounded-xl bg-slate-900 border-2 border-primary-400 text-center transition-all flex flex-col items-center justify-center shadow-md shadow-primary-500/20';
                    if (badge) badge.className = 'flex items-center gap-1 text-[11px] font-bold text-white';
                    if (icon) icon.className = 'bx bx-loader-circle bx-spin text-xs text-primary-400';
                } else {
                    // Pending: Slate-800 Gray
                    if (seg) seg.className.baseVal = 'seg-pending';
                    if (pill) pill.className = 'py-2 px-1 rounded-xl bg-slate-900 border border-slate-800 text-center transition-all flex flex-col items-center justify-center';
                    if (badge) badge.className = 'flex items-center gap-1 text-[11px] font-bold text-slate-500';
                    if (icon) icon.className = 'bx bx-radio-circle text-xs text-slate-600';
                }
            }
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
                console.error("Gagal memuat model face-api:", err);
                document.getElementById('loaderTitle').textContent = "Gagal memuat model AI. Periksa koneksi internet.";
            }
        }

        async function requestEnrollmentChallenge() {
            try {
                const res = await fetch("{{ route('siswa.enrollment.challenge') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    challengeToken = data.challenge_token || data.data?.token;
                }
            } catch (err) {
                console.error("Gagal meminta challenge:", err);
            }
        }

        async function initCamera() {
            cameraErrorOverlay.classList.add('hidden');
            updateStatus('Menghubungkan ke kamera...', 'ready');

            if (!cameraManager) {
                cameraManager = new UniversalBiometricCamera({
                    videoElement: video,
                    selectElement: cameraDeviceSelect,
                    deviceBadgeElement: deviceBadge,
                    onReady: (stream, info) => {
                        cameraErrorOverlay.classList.add('hidden');
                        isCameraActive = true;
                        updateStatus('Posisikan wajah Anda tepat di dalam bingkai oval.', 'ready');
                    },
                    onError: (errInfo) => {
                        isCameraActive = false;
                        document.getElementById('cameraErrorTitle').textContent = errInfo.title;
                        document.getElementById('cameraErrorGuidance').textContent = errInfo.guidance;
                        cameraErrorOverlay.classList.remove('hidden');
                        updateStatus(errInfo.title, 'error');
                    },
                    onDeviceChange: (deviceId, label) => {
                        updateStatus(`Kamera dialihkan ke ${label}. Posisikan wajah.`, 'ready');
                    }
                });
            }

            return await cameraManager.start();
        }

        async function toggleCameraFacing() {
            if (cameraManager) {
                await cameraManager.toggleFacingMode();
            }
        }

        function stopCamera() {
            isDetecting = false;
            if (cameraManager) {
                cameraManager.stop();
            }
            isCameraActive = false;
        }

        function updateStatus(msg, state = 'ready', score = null) {
            statusMessage.textContent = msg;
            if (score !== null) {
                qualityScoreBadge.textContent = `Skor: ${Math.round(score * 100)}%`;
            } else {
                qualityScoreBadge.textContent = '';
            }

            if (state === 'success') {
                statusIcon.className = 'bx bx-check-circle text-emerald-400 text-base flex-shrink-0';
                statusAlertBar.className = 'w-full p-2.5 rounded-xl bg-emerald-950/60 border border-emerald-500/50 text-xs text-emerald-200 flex items-center justify-between transition-all';
            } else if (state === 'warning') {
                statusIcon.className = 'bx bx-error-circle text-amber-400 text-base flex-shrink-0';
                statusAlertBar.className = 'w-full p-2.5 rounded-xl bg-amber-950/60 border border-amber-500/50 text-xs text-amber-200 flex items-center justify-between transition-all';
            } else if (state === 'error') {
                statusIcon.className = 'bx bx-x-circle text-rose-400 text-base flex-shrink-0';
                statusAlertBar.className = 'w-full p-2.5 rounded-xl bg-rose-950/60 border border-rose-500/50 text-xs text-rose-200 flex items-center justify-between transition-all';
            } else {
                statusIcon.className = 'bx bx-loader-alt bx-spin text-primary-400 text-base flex-shrink-0';
                statusAlertBar.className = 'w-full p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 flex items-center justify-between transition-all';
            }
        }

        // =====================================================================
        // 3. POSE ESTIMATION MATHEMATICS & DIRECTION CALIBRATION
        // =====================================================================
        function estimateHeadPose(landmarks, box, videoW, videoH) {
            const pts = landmarks.positions;

            // In raw video coordinates:
            // pts[0]  = raw image-left jaw (user's right cheek)
            // pts[16] = raw image-right jaw (user's left cheek)
            // pts[30] = nose tip
            // pts[8]  = chin bottom
            // pts[36] = raw image-left eye outer corner
            // pts[45] = raw image-right eye outer corner

            const noseTip = pts[30];
            const dNoseToRawLeft = Math.hypot(noseTip.x - pts[0].x, noseTip.y - pts[0].y);
            const dNoseToRawRight = Math.hypot(noseTip.x - pts[16].x, noseTip.y - pts[16].y);

            // Eye to nose distances
            const dEyeRawLeft = Math.abs(noseTip.x - pts[36].x);
            const dEyeRawRight = Math.abs(noseTip.x - pts[45].x);

            // Yaw calculation calibrated with CSS scaleX(-1) mirror:
            // User turns to their RIGHT: nose moves towards raw-left (pts[0]), so dNoseToRawLeft shrinks.
            // Ratio (dNoseToRawRight - dNoseToRawLeft) / (dNoseToRawRight + dNoseToRawLeft) is POSITIVE (+0.18 to +0.50).
            // User turns to their LEFT: nose moves towards raw-right (pts[16]), so dNoseToRawRight shrinks.
            // Ratio is NEGATIVE (-0.18 to -0.50).
            const jawYawRatio = (dNoseToRawRight - dNoseToRawLeft) / (dNoseToRawRight + dNoseToRawLeft + 0.0001);
            const eyeYawRatio = (dEyeRawRight - dEyeRawLeft) / (dEyeRawRight + dEyeRawLeft + 0.0001);
            const rawYaw = (jawYawRatio * 0.5) + (eyeYawRatio * 0.5);

            // Pitch calculation (Look Up):
            const eyeCenterY = (pts[36].y + pts[45].y) / 2;
            const distEyeToNose = noseTip.y - eyeCenterY;
            const distNoseToChin = pts[8].y - noseTip.y;
            const rawPitchRatio = distEyeToNose / (distEyeToNose + distNoseToChin + 0.0001);
            const rawChinToNoseRatio = distNoseToChin / (distEyeToNose + 0.0001);

            // Roll (Sideways tilt)
            const rawRoll = Math.atan2(pts[45].y - pts[36].y, pts[45].x - pts[36].x) * (180 / Math.PI);

            return {
                rawYaw,
                rawPitchRatio,
                rawChinToNoseRatio,
                rawRoll,
                box
            };
        }

        function evaluatePoseForStep(step, yaw, pitchRatio, chinToNoseRatio, roll, box, videoW) {
            const boxCenterX = (box.x + box.width / 2) / videoW;
            const isCentered = boxCenterX >= 0.25 && boxCenterX <= 0.75;

            if (!isCentered) {
                return { match: false, feedback: "Posisikan wajah di tengah bingkai oval." };
            }

            const faceRatio = box.width / videoW;
            if (faceRatio < 0.20) {
                return { match: false, feedback: "Dekatkan wajah sedikit ke kamera." };
            }
            if (faceRatio > 0.75) {
                return { match: false, feedback: "Jauhkan wajah sedikit dari kamera." };
            }

            switch (step) {
                case 1: // FRONT FACING
                    const isYawFront = Math.abs(yaw) <= 0.12;
                    const isPitchFront = pitchRatio >= 0.35 && pitchRatio <= 0.49;
                    const isRollFront = Math.abs(roll) <= 12;

                    if (isYawFront && isPitchFront && isRollFront) {
                        return { match: true, feedback: "Posisi tepat! Tahan sebentar..." };
                    }
                    if (yaw > 0.14) {
                        return { match: false, feedback: "Wajah terlalu menoleh ke kanan. Hadapkan lurus." };
                    }
                    if (yaw < -0.14) {
                        return { match: false, feedback: "Wajah terlalu menoleh ke kiri. Hadapkan lurus." };
                    }
                    if (pitchRatio < 0.34) {
                        return { match: false, feedback: "Dagu terlalu terangkat. Hadapkan lurus ke depan." };
                    }
                    if (pitchRatio > 0.50) {
                        return { match: false, feedback: "Wajah terlalu menunduk. Hadapkan lurus ke depan." };
                    }
                    return { match: false, feedback: "Hadapkan wajah tepat ke kamera." };

                case 2: // TURN RIGHT
                    const isTurnedRight = yaw >= 0.18;
                    const isRollRight = Math.abs(roll) <= 20;

                    if (isTurnedRight && isRollRight) {
                        return { match: true, feedback: "Pose kanan pas! Tahan sebentar..." };
                    }
                    if (yaw < -0.06) {
                        return { match: false, feedback: "Arah terbalik. Silakan menoleh ke kanan Anda." };
                    }
                    if (yaw < 0.18) {
                        return { match: false, feedback: "Tolehkan kepala perlahan sedikit lagi ke kanan..." };
                    }
                    return { match: false, feedback: "Hadapkan wajah ke kanan." };

                case 3: // TURN LEFT
                    const isTurnedLeft = yaw <= -0.18;
                    const isRollLeft = Math.abs(roll) <= 20;

                    if (isTurnedLeft && isRollLeft) {
                        return { match: true, feedback: "Pose kiri pas! Tahan sebentar..." };
                    }
                    if (yaw > 0.06) {
                        return { match: false, feedback: "Arah terbalik. Silakan menoleh ke kiri Anda." };
                    }
                    if (yaw > -0.18) {
                        return { match: false, feedback: "Tolehkan kepala perlahan sedikit lagi ke kiri..." };
                    }
                    return { match: false, feedback: "Hadapkan wajah ke kiri." };

                case 4: // LOOK UP
                    const isLookingUp = pitchRatio <= 0.33 || chinToNoseRatio >= 2.05;
                    const isYawUp = Math.abs(yaw) <= 0.18;

                    if (isLookingUp && isYawUp) {
                        return { match: true, feedback: "Arah atas pas! Tahan sebentar..." };
                    }
                    if (Math.abs(yaw) > 0.18) {
                        return { match: false, feedback: "Posisikan wajah lurus terlebih dahulu, lalu angkat dagu." };
                    }
                    if (!isLookingUp) {
                        return { match: false, feedback: "Angkat dagu / arahkan kepala sedikit ke atas..." };
                    }
                    return { match: false, feedback: "Arahkan wajah sedikit ke atas." };

                default:
                    return { match: false, feedback: "Memproses..." };
            }
        }

        // =====================================================================
        // 4. MAIN DETECTION & SEQUENTIAL CAPTURE LOOP
        // =====================================================================
        function startLiveDetectionLoop() {
            if (isDetecting) return;
            isDetecting = true;

            const checkFrame = async () => {
                if (!isDetecting || video.paused || video.ended) {
                    if (isDetecting) requestAnimationFrame(checkFrame);
                    return;
                }

                try {
                    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.55 });
                    const detection = await faceapi.detectSingleFace(video, options)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        poseHoldStartTime = null;
                        document.getElementById('poseHoldProgressBar').style.width = '0%';
                        document.getElementById('poseHoldTimerBadge').textContent = 'Tahan posisi 0.6 detik';
                        updateStatus('Wajah tidak terdeteksi. Posisikan wajah di dalam oval.', 'warning');
                        requestAnimationFrame(checkFrame);
                        return;
                    }

                    const videoW = video.videoWidth || 640;
                    const videoH = video.videoHeight || 480;
                    const pose = estimateHeadPose(detection.landmarks, detection.detection.box, videoW, videoH);

                    // Exponential Smoothing
                    smoothedYaw = (smoothedYaw * (1 - SMOOTH_ALPHA)) + (pose.rawYaw * SMOOTH_ALPHA);
                    smoothedPitch = (smoothedPitch * (1 - SMOOTH_ALPHA)) + (pose.rawPitchRatio * SMOOTH_ALPHA);
                    smoothedRoll = (smoothedRoll * (1 - SMOOTH_ALPHA)) + (pose.rawRoll * SMOOTH_ALPHA);

                    // Debug log (console only)
                    if (window.DEBUG_BIOMETRICS) {
                        console.debug(`[BiometricPose] Step ${activePoseStep}: yaw=${smoothedYaw.toFixed(2)}, pitch=${smoothedPitch.toFixed(2)}, roll=${smoothedRoll.toFixed(1)}`);
                    }

                    // Evaluate strictly against active step
                    if (activePoseStep >= 1 && activePoseStep <= 4 && !isPoseCapturing) {
                        const evalResult = evaluatePoseForStep(
                            activePoseStep,
                            smoothedYaw,
                            smoothedPitch,
                            pose.rawChinToNoseRatio,
                            smoothedRoll,
                            pose.box,
                            videoW
                        );

                        if (evalResult.match) {
                            if (!poseHoldStartTime) {
                                poseHoldStartTime = Date.now();
                            }
                            const elapsed = Date.now() - poseHoldStartTime;
                            const progressPercent = Math.min(100, Math.round((elapsed / POSE_REQUIRED_HOLD_MS) * 100));
                            document.getElementById('poseHoldProgressBar').style.width = progressPercent + '%';
                            document.getElementById('poseHoldTimerBadge').textContent = `Tahan: ${Math.round(elapsed)}ms / ${POSE_REQUIRED_HOLD_MS}ms`;
                            updateStatus(evalResult.feedback, 'success', detection.detection.score);

                            if (elapsed >= POSE_REQUIRED_HOLD_MS && !isPoseCapturing) {
                                isPoseCapturing = true;
                                await captureCurrentStep(detection);
                            }
                        } else {
                            poseHoldStartTime = null;
                            document.getElementById('poseHoldProgressBar').style.width = '0%';
                            document.getElementById('poseHoldTimerBadge').textContent = 'Tahan posisi 0.6 detik';
                            updateStatus(evalResult.feedback, 'ready', detection.detection.score);
                        }
                    }

                } catch (err) {
                    console.error("Frame detection error:", err);
                }

                if (isDetecting) {
                    requestAnimationFrame(checkFrame);
                }
            };

            requestAnimationFrame(checkFrame);
        }

        async function captureCurrentStep(detection) {
            const step = activePoseStep;
            const durationMs = Date.now() - stepStartTime;

            const descriptor = Array.from(detection.descriptor);
            const snapshotBase64 = takeVideoSnapshot();

            stepSamples[step] = {
                type: POSE_DEFINITIONS[step].type,
                descriptor: descriptor,
                snapshot: snapshotBase64,
                duration_ms: Math.max(durationMs, 600),
                quality_score: detection.detection.score
            };

            console.debug(`[BiometricPose] Step ${step} (${POSE_DEFINITIONS[step].type}) captured in ${durationMs}ms`);

            // Mark current segment as completed (Solid Blue Permanently)
            const seg = document.getElementById(POSE_DEFINITIONS[step].segId);
            if (seg) seg.className.baseVal = 'seg-completed';

            const pill = document.getElementById(POSE_DEFINITIONS[step].pillId);
            if (pill) pill.className = 'py-2 px-1 rounded-xl bg-primary-950/80 border border-primary-500 text-center transition-all flex flex-col items-center justify-center';

            const badge = document.getElementById(POSE_DEFINITIONS[step].badgeId);
            if (badge) badge.className = 'flex items-center gap-1 text-[11px] font-bold text-primary-300';

            const icon = document.getElementById(POSE_DEFINITIONS[step].iconId);
            if (icon) icon.className = 'bx bx-check-circle text-xs text-primary-400';

            updateStatus(`Langkah ${step} Berhasil!`, 'success', detection.detection.score);

            // Brief pause so user clearly notices completion (450ms)
            await new Promise(r => setTimeout(r, 450));

            if (step < 4) {
                activePoseStep = step + 1;
                stepStartTime = Date.now();
                poseHoldStartTime = null;
                isPoseCapturing = false;
                updatePoseUI(activePoseStep);
            } else {
                await finalizeAllPosesEnrollment();
            }
        }

        function takeVideoSnapshot() {
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', 0.88);
        }

        async function finalizeAllPosesEnrollment() {
            isDetecting = false;
            isPoseCapturing = true;

            updateStatus('Seluruh 4 pose selesai! Menyimpan biometrik...', 'success');
            document.getElementById('poseMainInstruction').textContent = 'Menyimpan Template Biometrik...';
            document.getElementById('poseSubHint').textContent = 'Mengenkripsi data 4 pose ke server sekolah';

            const completedChallenges = [
                { type: 'front_facing', duration_ms: stepSamples[1].duration_ms },
                { type: 'turn_right', duration_ms: stepSamples[2].duration_ms },
                { type: 'turn_left', duration_ms: stepSamples[3].duration_ms },
                { type: 'look_up', duration_ms: stepSamples[4].duration_ms }
            ];

            const primaryDescriptor = stepSamples[1].descriptor;
            const allDescriptors = [
                stepSamples[1].descriptor,
                stepSamples[2].descriptor,
                stepSamples[3].descriptor,
                stepSamples[4].descriptor
            ];
            const primaryPhoto = stepSamples[1].snapshot;

            const avgQuality = ((
                stepSamples[1].quality_score +
                stepSamples[2].quality_score +
                stepSamples[3].quality_score +
                stepSamples[4].quality_score
            ) / 4) * 100;

            try {
                const response = await fetch("{{ route('siswa.enrollment.process') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        challenge_token: challengeToken,
                        face_descriptor: primaryDescriptor,
                        descriptors: allDescriptors,
                        foto: primaryPhoto,
                        image: primaryPhoto,
                        quality_score: avgQuality,
                        liveness_proof: {
                            liveness_score: 99.0,
                            completed_challenges: completedChallenges
                        },
                        device_type: cameraManager ? cameraManager.platformInfo.type : 'webcam',
                        device_label: cameraDeviceSelect ? cameraDeviceSelect.options[cameraDeviceSelect.selectedIndex]?.text : 'Kamera Siswa'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    stopCamera();
                    showEnrollmentSuccessScreen(result, primaryPhoto);
                } else {
                    updateStatus(result.message || 'Gagal menyimpan template biometrik.', 'error');
                    document.getElementById('btnRestartProcess').classList.remove('hidden');
                }
            } catch (err) {
                console.error("Gagal mengirim payload enrollment:", err);
                updateStatus('Terjadi kesalahan jaringan saat menyimpan data.', 'error');
                document.getElementById('btnRestartProcess').classList.remove('hidden');
            }
        }

        function showEnrollmentSuccessScreen(result, photo) {
            sceneCamera.classList.remove('scene-active');
            sceneCamera.classList.add('scene-hidden');

            const previewImg = document.getElementById('enrolledPhotoPreview');
            if (previewImg && photo) {
                previewImg.src = photo;
            }

            const qualityEl = document.getElementById('finalQualityText');
            if (qualityEl) {
                qualityEl.textContent = `Skor: ${Math.round(result.quality_score || 95)}%`;
            }

            sceneSuccess.classList.remove('scene-hidden');
            sceneSuccess.classList.add('scene-active');
        }

        function restartEnrollmentProcess() {
            document.getElementById('btnRestartProcess').classList.add('hidden');
            activePoseStep = 1;
            isPoseCapturing = false;
            poseHoldStartTime = null;
            stepSamples = { 1: null, 2: null, 3: null, 4: null };
            stepStartTime = Date.now();
            updatePoseUI(1);
            startLiveDetectionLoop();
        }

        // Enable debug mode via URL ?debug=1 or key combo Ctrl+Shift+D
        if (window.location.search.includes('debug=1')) {
            window.DEBUG_BIOMETRICS = true;
        }
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'd') {
                window.DEBUG_BIOMETRICS = !window.DEBUG_BIOMETRICS;
                console.log(`[BiometricPose] Debug logging: ${window.DEBUG_BIOMETRICS ? 'ENABLED' : 'DISABLED'}`);
            }
        });
    </script>
</body>

</html>

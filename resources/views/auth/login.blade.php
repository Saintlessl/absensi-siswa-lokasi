<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MySKANIC — Sistem Presensi, Informasi & Manajemen Akademik SMK Negeri 1 Ciomas">
    <title>Login — MySkanic</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
                        brand: {
                            primary: '#003FA9',
                            deep: '#002B75',
                            lightBg: '#E4EEFF',
                            inputBg: '#F0F5FF',
                            textPrimary: '#003580',
                            textSecondary: '#7894C4',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    {{-- Boxicons --}}
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        /* -------------------------------------------------------------------------
         * Panel Swap Sliding Transitions (Desktop 50:50)
         * Uses GPU-accelerated transform: translateX() and border-radius with smooth easing
         * ------------------------------------------------------------------------- */
        .panel-transition {
            transition: transform 700ms cubic-bezier(0.22, 1, 0.36, 1),
                        border-radius 700ms cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform, border-radius;
        }

        /* -------------------------------------------------------------------------
         * Character Idle Animations: Subtle Float & Gentle Sway
         * Anchored to bottom stance with center bottom transform-origin
         * ------------------------------------------------------------------------- */
        @keyframes floatSway1 {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-6px) rotate(0.6deg);
            }
        }

        @keyframes floatSway2 {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-5px) rotate(-0.6deg);
            }
        }

        .char-idle-1 {
            animation: floatSway1 5.4s ease-in-out infinite;
            transform-origin: center bottom;
            will-change: transform;
        }

        .char-idle-2 {
            animation: floatSway2 6.0s ease-in-out infinite 0.4s;
            transform-origin: center bottom;
            will-change: transform;
        }

        /* -------------------------------------------------------------------------
         * Layer 4: Progressive Bottom Blur & Gradient Blending
         * Blends character lower thighs smoothly into the deep royal blue background
         * while keeping the chest, uniform, and faces 100% sharp and crisp.
         * ------------------------------------------------------------------------- */
        .bottom-blend-overlay {
            position: absolute;
            inset-inline: 0;
            bottom: 0;
            height: 28%;
            z-index: 15;
            pointer-events: none;
            background: linear-gradient(
                to bottom,
                rgba(0, 43, 117, 0) 0%,
                rgba(0, 43, 117, 0.25) 25%,
                rgba(0, 35, 95, 0.65) 55%,
                rgba(0, 31, 92, 0.92) 80%,
                #001F5C 100%
            );
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            mask-image: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0) 0%,
                rgba(0, 0, 0, 0.25) 25%,
                rgba(0, 0, 0, 0.8) 60%,
                rgba(0, 0, 0, 1) 100%
            );
            -webkit-mask-image: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0) 0%,
                rgba(0, 0, 0, 0.25) 25%,
                rgba(0, 0, 0, 0.8) 60%,
                rgba(0, 0, 0, 1) 100%
            );
        }

        /* Subtle background pulse for the ornamental overlay */
        @keyframes bgPulse {
            0%, 100% {
                opacity: 0.06;
            }
            50% {
                opacity: 0.09;
            }
        }

        .bg-pattern-subtle {
            animation: bgPulse 8s ease-in-out infinite;
        }

        /* -------------------------------------------------------------------------
         * Accessibility: Respect prefers-reduced-motion
         * ------------------------------------------------------------------------- */
        @media (prefers-reduced-motion: reduce) {
            .char-idle-1,
            .char-idle-2,
            .bg-pattern-subtle {
                animation: none !important;
            }
            .panel-transition,
            .role-pill-transition {
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="h-full bg-[#003FA9] text-[#003580] antialiased select-none selection:bg-[#003FA9] selection:text-white"
      x-data="loginPage()">

    @php
        // Centralized asset paths for convenient replacement
        $assets = [
            'logo_svg'       => 'images/login/myskanic-logo.svg',
            'logo_png'       => 'images/login/myskanic-logo.png',
            'pattern_svg'    => 'images/login/school-pattern.svg',
            'pattern'        => 'images/login/school-pattern.png',
            'emblem'         => 'images/login/school-emblem-white.png',
            'student_chars'  => 'images/login/student-characters.png',
            'teacher_chars'  => 'images/login/teacher-characters.png',
            'partner_logos'  => 'images/login/school-partner-logos.png',
            'fallback_crest' => 'img/school-crest.png',
            'fallback_batik' => 'img/batik-megamendung.png',
            'logo_pplg'      => 'images/jurusan/pplg.png',
            'logo_animasi'   => 'images/jurusan/animasi.png',
            'logo_tpfl'      => 'images/jurusan/tpfl.png',
            'logo_to'        => 'images/jurusan/to.png',
            'logo_bcf'       => 'images/jurusan/bcf.png',
        ];

        // Resolve logo URL
        $logoUrl = file_exists(public_path($assets['logo_svg']))
            ? asset($assets['logo_svg'])
            : (file_exists(public_path($assets['logo_png'])) ? asset($assets['logo_png']) : asset('img/logo.png'));

        // Resolve pattern URL (prefers seamless emblem pattern tile)
        $patternUrl = file_exists(public_path($assets['pattern']))
            ? asset($assets['pattern'])
            : (file_exists(public_path($assets['pattern_svg']))
                ? asset($assets['pattern_svg'])
                : (file_exists(public_path($assets['fallback_batik'])) ? asset($assets['fallback_batik']) : null));
    @endphp

    {{-- Main Container: Full viewport height, split 50:50 on desktop, stacked on mobile --}}
    <main class="relative w-full min-h-screen lg:h-screen lg:overflow-hidden flex flex-col lg:flex-row">

        {{-- FULL-SCREEN ROYAL BLUE GRADIENT & WATERMARK PATTERN (Continuous behind all panels) --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#003FA9] via-[#003580] to-[#002B75] z-0 pointer-events-none"></div>
        <div class="absolute inset-0 pointer-events-none bg-pattern-subtle z-0"
             style="background-image: @if($patternUrl) url('{{ $patternUrl }}') @else url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.08' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E&quot;) @endif; background-size: 280px 240px; background-repeat: repeat;">
        </div>

        {{-- =========================================================================
             PANEL BRANDING (TRANSPARENT CONTAINER, SHOWS FULL BLUE BACKGROUND BEHIND)
             Desktop: Translates from 0% (Siswa) to +100% (Guru)
             Mobile: Top hero section
             ========================================================================= --}}
        <section class="w-full lg:w-1/2 min-h-[500px] sm:min-h-[560px] lg:min-h-0 lg:h-full relative z-10 flex flex-col justify-between panel-transition bg-transparent"
                 :class="role === 'siswa' ? 'lg:translate-x-0' : 'lg:translate-x-full'"
                 aria-label="Panel Identitas Sekolah MySKANIC">

            {{-- LAYER 3: Dynamic Character Illustrations Anchored to Bottom --}}
            <div class="absolute inset-x-0 bottom-0 z-10 flex items-end justify-center pointer-events-none overflow-hidden h-[74%] sm:h-[78%] lg:h-[82%] max-h-[calc(100vh-175px)]">

                {{-- Siswa Characters (Male & Female Batik Uniform) --}}
                <div class="absolute inset-0 flex justify-center items-end transition-all duration-600 ease-[cubic-bezier(0.22,1,0.36,1)]"
                     :class="role === 'siswa' ? 'opacity-100 translate-y-0 scale-100 pointer-events-auto' : 'opacity-0 translate-y-8 scale-95 pointer-events-none'">
                    <div class="char-idle-1 w-full h-full flex justify-center items-end">
                        <img src="{{ asset($assets['student_chars']) }}"
                             alt="Ilustrasi Siswa SMK Negeri 1 Ciomas"
                             class="h-full w-auto max-w-[75%] sm:max-w-[70%] lg:max-w-[65%] object-contain object-bottom drop-shadow-[0_20px_45px_rgba(0,15,60,0.55)] select-none pointer-events-none">
                    </div>
                </div>

                {{-- Guru Characters (Male & Female Khaki Uniform) --}}
                <div class="absolute inset-0 flex justify-center items-end transition-all duration-600 ease-[cubic-bezier(0.22,1,0.36,1)]"
                     :class="role === 'guru' ? 'opacity-100 translate-y-0 scale-100 pointer-events-auto' : 'opacity-0 translate-y-8 scale-95 pointer-events-none'">
                    <div class="char-idle-2 w-full h-full flex justify-center items-end">
                        <img src="{{ asset($assets['teacher_chars']) }}"
                             alt="Ilustrasi Guru SMK Negeri 1 Ciomas"
                             class="h-full w-auto max-w-[85%] sm:max-w-[80%] lg:max-w-[75%] object-contain object-bottom drop-shadow-[0_20px_45px_rgba(0,15,60,0.55)] select-none pointer-events-none">
                    </div>
                </div>
            </div>

            {{-- LAYER 4: Bottom Blur & Gradient Blending Layer --}}
            <div class="bottom-blend-overlay"></div>

            {{-- LAYER 5: Content (Header & Footer) --}}
            {{-- 1. TOP HEADER: Logo & School Tagline --}}
            <header class="relative z-20 flex flex-col items-center text-center pt-5 sm:pt-6 lg:pt-8 px-4 sm:px-6">
                <div class="mb-2.5 sm:mb-3.5">
                    <img src="{{ $logoUrl }}"
                         alt="Logo MySKANIC"
                         class="h-14 sm:h-18 lg:h-[126px] xl:h-[134px] w-auto max-w-[85%] object-contain mx-auto drop-shadow-md"
                         onerror="this.onerror=null; this.src='{{ asset($assets['logo_png']) }}';">
                </div>

                <p class="text-white text-xs sm:text-sm font-semibold tracking-wide drop-shadow-sm mb-1">
                    Sistem Presensi, Informasi &amp; Manajemen Akademik
                </p>
                <p class="text-white/85 text-[11px] sm:text-xs uppercase tracking-widest font-bold drop-shadow-sm">
                    SMK Negeri 1 Ciomas
                </p>
            </header>

            {{-- Spacer in flex flow to ensure minimum height on mobile without disrupting layout --}}
            <div class="relative z-0 min-h-[260px] sm:min-h-[300px] lg:min-h-0 flex-1 pointer-events-none"></div>

            {{-- 3. BOTTOM FOOTER: Badges, Social Media, Copyright --}}
            <footer class="relative z-20 w-full flex flex-col items-center gap-2.5 pb-5 sm:pb-6 lg:pb-7 px-6 sm:px-8">

                {{-- Official School & 5 Department Badges matching SMKN 1 Ciomas --}}
                <div class="flex items-center justify-center gap-2 sm:gap-2.5">

                    {{-- 2. PPLG (Pengembangan Perangkat Lunak & Gim) --}}
                    <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-full items-center justify-center shadow-md shadow-black/25 hover:scale-115 transition-transform duration-300 cursor-pointer"
                         title="Pengembangan Perangkat Lunak &amp; Gim (PPLG)">
                        <img src="{{ asset($assets['logo_pplg']) }}" alt="PPLG" class="w-full h-full object-contain rounded-full">
                    </div>

                    {{-- 3. Animasi (ANIMASI) --}}
                    <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-full items-center justify-center shadow-md shadow-black/25 hover:scale-115 transition-transform duration-300 cursor-pointer"
                         title="Animasi">
                        <img src="{{ asset($assets['logo_animasi']) }}" alt="Animasi" class="w-full h-full object-contain rounded-full">
                    </div>

                    {{-- 4. TPFL (Teknik Pengelasan & Fabrikasi Logam) --}}
                    <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-full items-center justify-center shadow-md shadow-black/25 hover:scale-115 transition-transform duration-300 cursor-pointer"
                         title="Teknik Pengelasan &amp; Fabrikasi Logam (TPFL)">
                        <img src="{{ asset($assets['logo_tpfl']) }}" alt="TPFL" class="w-full h-full object-contain rounded-full">
                    </div>

                    {{-- 5. TO (Teknik Otomotif) --}}
                    <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-full items-center justify-center shadow-md shadow-black/25 hover:scale-115 transition-transform duration-300 cursor-pointer"
                         title="Teknik Otomotif (TO)">
                        <img src="{{ asset($assets['logo_to']) }}" alt="TO" class="w-full h-full object-contain rounded-full">
                    </div>

                    {{-- 6. BCF (Broadcasting & Perfilman) --}}
                    <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-full items-center justify-center shadow-md shadow-black/25 hover:scale-115 transition-transform duration-300 cursor-pointer"
                         title="Broadcasting &amp; Perfilman (BCF)">
                        <img src="{{ asset($assets['logo_bcf']) }}" alt="BCF" class="w-full h-full object-contain">
                    </div>
                </div>

                {{-- Copyright Line --}}
                <div class="w-full flex items-center justify-center relative mt-1">
                    <p class="text-white/70 text-[11px] sm:text-xs font-normal tracking-wide text-center">
                        &copy; {{ date('Y') }} MySKANIC &bull; SMK Negeri 1 Ciomas
                    </p>
                </div>

                {{-- Social Icons on Mobile (< lg, without background and enlarged) --}}
                <div class="flex lg:hidden items-center justify-center gap-5 mt-1">
                    <a href="https://instagram.com/smkn1ciomas_official" target="_blank" rel="noopener"
                       aria-label="Instagram SMKN 1 Ciomas"
                       class="text-white/80 hover:text-white text-xl transition-all duration-300 hover:scale-115 p-1"
                       title="Instagram">
                        <i class="bx bxl-instagram"></i>
                    </a>
                    <a href="https://tiktok.com/@smkn1ciomas" target="_blank" rel="noopener"
                       aria-label="TikTok SMKN 1 Ciomas"
                       class="text-white/80 hover:text-white text-xl transition-all duration-300 hover:scale-115 p-1"
                       title="TikTok">
                        <i class="bx bxl-tiktok"></i>
                    </a>
                    <a href="https://youtube.com/@smknegeri1ciomas" target="_blank" rel="noopener"
                       aria-label="YouTube SMKN 1 Ciomas"
                       class="text-white/80 hover:text-white text-xl transition-all duration-300 hover:scale-115 p-1"
                       title="YouTube">
                        <i class="bx bxl-youtube"></i>
                    </a>
                </div>
            </footer>

            {{-- Social Icons on Desktop (Aligned dynamically near inner edge with generous margin, never cut off) --}}
            <div class="hidden lg:flex flex-col items-center gap-3.5 absolute bottom-6 transition-all duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] z-30"
                 :class="role === 'siswa' ? 'right-6 xl:right-8' : 'left-6 xl:left-8'">
                <a href="https://instagram.com/smkn1ciomas_official" target="_blank" rel="noopener"
                   aria-label="Instagram Resmi SMKN 1 Ciomas"
                   class="text-white/80 hover:text-white transition-all duration-300 hover:scale-120 text-2xl drop-shadow-sm flex items-center justify-center p-1.5"
                   title="Instagram">
                    <i class="bx bxl-instagram"></i>
                </a>
                <a href="https://tiktok.com/@smkn1ciomas" target="_blank" rel="noopener"
                   aria-label="TikTok Resmi SMKN 1 Ciomas"
                   class="text-white/80 hover:text-white transition-all duration-300 hover:scale-120 text-2xl drop-shadow-sm flex items-center justify-center p-1.5"
                   title="TikTok">
                    <i class="bx bxl-tiktok"></i>
                </a>
                <a href="https://youtube.com/@smknegeri1ciomas" target="_blank" rel="noopener"
                   aria-label="YouTube Resmi SMKN 1 Ciomas"
                   class="text-white/80 hover:text-white transition-all duration-300 hover:scale-120 text-2xl drop-shadow-sm flex items-center justify-center p-1.5"
                   title="YouTube">
                    <i class="bx bxl-youtube"></i>
                </a>
            </div>
        </section>


        {{-- =========================================================================
             PANEL FORM (LIGHT BLUE)
             Desktop: Translates from 0% (Siswa) to -100% (Guru)
             Rounded corner morphs: left edge when on the right, right edge when on the left
             Mobile: Bottom sheet with rounded-t-[32px]
             ========================================================================= --}}
        <section class="w-full lg:w-1/2 lg:h-full relative z-20 flex items-center justify-center p-6 sm:p-10 lg:p-12 bg-[#E4EEFF] panel-transition shadow-2xl"
                 :class="role === 'siswa'
                    ? 'lg:translate-x-0 lg:rounded-tl-[48px] lg:rounded-bl-[48px] lg:rounded-tr-none lg:rounded-br-none rounded-t-[32px] lg:rounded-t-none'
                    : 'lg:-translate-x-full lg:rounded-tr-[48px] lg:rounded-br-[48px] lg:rounded-tl-none lg:rounded-bl-none rounded-t-[32px] lg:rounded-t-none'"
                 aria-label="Formulir Autentikasi MySKANIC">

            <div class="w-full max-w-[460px] mx-auto flex flex-col justify-center">

                {{-- Heading: "Selamat Datang" --}}
                <header class="text-center mb-2">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003580] tracking-tight mb-2.5">
                        Selamat Datang
                    </h1>

                    {{-- Dynamic Description with Smooth Crossfade --}}
                    <div class="min-h-[46px] flex items-center justify-center">
                        <p x-show="role === 'siswa'"
                           x-transition:enter="transition ease-out duration-300"
                           x-transition:enter-start="opacity-0 -translate-y-1"
                           x-transition:enter-end="opacity-100 translate-y-0"
                           x-transition:leave="transition ease-in duration-200"
                           x-transition:leave-start="opacity-100 translate-y-0"
                           x-transition:leave-end="opacity-0 translate-y-1"
                           class="text-xs sm:text-sm text-[#7894C4] leading-relaxed max-w-[420px]">
                            Silahkan masukkan akun kamu untuk memantau kehadiran, jadwal, nilai, dan aktivitas sekolahmu dengan mudah.
                        </p>
                        <p x-show="role === 'guru'"
                           x-cloak
                           x-transition:enter="transition ease-out duration-300"
                           x-transition:enter-start="opacity-0 -translate-y-1"
                           x-transition:enter-end="opacity-100 translate-y-0"
                           x-transition:leave="transition ease-in duration-200"
                           x-transition:leave-start="opacity-100 translate-y-0"
                           x-transition:leave-end="opacity-0 translate-y-1"
                           class="text-xs sm:text-sm text-[#7894C4] leading-relaxed max-w-[420px]">
                            Kelola pembelajaran, presensi, jurnal, dan perkembangan siswa dalam satu tempat.
                        </p>
                    </div>
                </header>

                {{-- Role Selector: Segmented Pill Control with Sliding Indicator --}}
                <div class="relative w-full max-w-[340px] mx-auto bg-white/90 p-1.5 rounded-2xl flex items-center border border-blue-100/70 shadow-sm mt-4 mb-7"
                     role="tablist"
                     aria-label="Pilih Peran Login">

                    {{-- Sliding Active Indicator --}}
                    <div class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] bg-[#003FA9] rounded-xl shadow-md transition-all duration-400 ease-[cubic-bezier(0.22,1,0.36,1)] pointer-events-none"
                         :style="role === 'siswa' ? 'left: 6px;' : 'left: calc(50%);'">
                    </div>

                    {{-- Tab Siswa --}}
                    <button type="button"
                            role="tab"
                            :aria-selected="role === 'siswa'"
                            @click="setRole('siswa')"
                            class="relative z-10 w-1/2 py-2.5 text-sm font-bold text-center transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003FA9]"
                            :class="role === 'siswa' ? 'text-white' : 'text-[#003580] hover:text-[#003FA9]'">
                        Siswa
                    </button>

                    {{-- Tab Guru --}}
                    <button type="button"
                            role="tab"
                            :aria-selected="role === 'guru'"
                            @click="setRole('guru')"
                            class="relative z-10 w-1/2 py-2.5 text-sm font-bold text-center transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003FA9]"
                            :class="role === 'guru' ? 'text-white' : 'text-[#003580] hover:text-[#003FA9]'">
                        Guru
                    </button>
                </div>

                {{-- Authentication Form --}}
                <form id="loginForm"
                      method="POST"
                      action="{{ route('proseslogin') }}"
                      @submit="handleSubmit($event)"
                      class="w-full">
                    @csrf

                    {{-- Client Telemetry & Device Registration --}}
                    <input type="hidden" name="device_token" id="clientDeviceToken">
                    <input type="hidden" name="device_name" id="clientDeviceName">
                    <input type="hidden" name="platform" id="clientPlatform">
                    <input type="hidden" name="browser" id="clientBrowser">
                    <input type="hidden" name="portal_role" :value="role">

                    {{-- Inline Alerts for Errors --}}
                    @if ((isset($errors) && $errors->any()) || session('error'))
                        <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200/80 text-red-700 text-xs flex items-start gap-2.5 shadow-sm">
                            <i class="bx bx-error-circle text-lg shrink-0 mt-0.5 text-red-500"></i>
                            <div class="flex-1">
                                @if (session('error'))
                                    <p class="font-semibold">{{ session('error') }}</p>
                                @endif
                                @if (isset($errors) && $errors->any())
                                    <ul class="list-disc list-inside space-y-0.5 mt-0.5">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mb-5 p-3.5 rounded-xl bg-blue-50 border border-blue-200/80 text-blue-800 text-xs flex items-start gap-2.5 shadow-sm">
                            <i class="bx bx-info-circle text-lg shrink-0 mt-0.5 text-blue-600"></i>
                            <p class="font-medium flex-1">{{ session('info') }}</p>
                        </div>
                    @endif

                    {{-- Email Field --}}
                    <div class="mb-4">
                        <label for="email" class="text-xs font-semibold text-[#003580] mb-2 block tracking-wide">
                            Email
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               spellcheck="false"
                               enterkeyhint="next"
                               placeholder="Masukan email MySkanic Kamu"
                               class="w-full bg-[#F0F5FF] text-[#003580] placeholder-[#7894C4] font-medium text-sm rounded-xl px-4 py-3.5 border border-transparent focus:border-[#003FA9] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 transition-all shadow-sm">
                    </div>

                    {{-- Password Field with Toggle --}}
                    <div class="mb-4">
                        <label for="password" class="text-xs font-semibold text-[#003580] mb-2 block tracking-wide">
                            Password
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'"
                                   id="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   enterkeyhint="done"
                                   placeholder="Masukan password"
                                   class="w-full bg-[#F0F5FF] text-[#003580] placeholder-[#7894C4] font-medium text-sm rounded-xl pl-4 pr-12 py-3.5 border border-transparent focus:border-[#003FA9] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 transition-all shadow-sm">

                            <button type="button"
                                    @click="togglePassword()"
                                    :aria-pressed="showPassword"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7894C4] hover:text-[#003FA9] p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003FA9]/30 transition-colors">
                                <i class="bx text-xl leading-none" :class="showPassword ? 'bx-show' : 'bx-hide'"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me Checkbox --}}
                    <div class="flex items-center justify-between mb-6">
                        <label for="remember" class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox"
                                   id="remember"
                                   name="remember"
                                   value="1"
                                   {{ old('remember') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-[#003FA9] border-blue-200 focus:ring-[#003FA9] focus:ring-offset-0 bg-[#F0F5FF]">
                            <span class="text-xs font-medium text-[#7894C4] hover:text-[#003580] transition-colors">
                                Ingat saya
                            </span>
                        </label>
                    </div>

                    {{-- Primary Submit Button --}}
                    <button type="submit"
                            id="btnLogin"
                            :disabled="isLoading"
                            class="w-full bg-[#003FA9] hover:bg-[#002B75] active:scale-[0.99] text-white font-bold text-sm md:text-base py-3.5 md:py-4 rounded-xl shadow-lg shadow-[#003FA9]/25 hover:shadow-[#002B75]/30 transition-all flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#003FA9] focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed">
                        <template x-if="!isLoading">
                            <span class="flex items-center gap-2">
                                <span>Login</span>
                                <i class="bx bx-right-arrow-alt text-xl"></i>
                            </span>
                        </template>
                        <template x-if="isLoading">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </template>
                    </button>
                </form>

            </div>
        </section>

    </main>

    {{-- =========================================================================
         SCRIPTS: Client Telemetry, SweetAlert2, and Alpine State
         ========================================================================= --}}
    <script>
        // Alpine Component Definition
        function loginPage() {
            return {
                // Initialize role based on URL query parameter or default to 'siswa'
                role: new URLSearchParams(window.location.search).get('role') === 'guru' ? 'guru' : 'siswa',
                showPassword: false,
                isLoading: false,

                setRole(newRole) {
                    if (this.role === newRole) return;
                    this.role = newRole;
                    // Update URL silently without reload for convenient bookmarking/sharing
                    const url = new URL(window.location);
                    url.searchParams.set('role', newRole);
                    window.history.replaceState({}, '', url);
                },

                togglePassword() {
                    this.showPassword = !this.showPassword;
                },

                handleSubmit(e) {
                    this.isLoading = true;
                }
            }
        }

        // Fill Demo Helper (Available globally for developers/automated testing)
        window.fillDemo = function(email, password) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput) {
                emailInput.value = email;
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (passInput) {
                passInput.value = password;
                passInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        // Client Device Token & Environment Detection (Preserving MySKANIC biometric & multi-device security)
        document.addEventListener('DOMContentLoaded', function() {
            let token = localStorage.getItem('myskanic_device_token');
            if (!token) {
                token = 'dev_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
                localStorage.setItem('myskanic_device_token', token);
            }
            const tokenInput = document.getElementById('clientDeviceToken');
            if (tokenInput) tokenInput.value = token;

            let ua = navigator.userAgent;
            let platform = 'Desktop';
            if (/Android/i.test(ua)) platform = 'Android';
            else if (/iPhone|iPad|iPod/i.test(ua)) platform = 'iOS';
            else if (/Windows/i.test(ua)) platform = 'Windows';
            else if (/Macintosh|Mac OS/i.test(ua)) platform = 'macOS';
            else if (/Linux/i.test(ua)) platform = 'Linux';

            let browser = 'Browser';
            if (/Chrome/i.test(ua)) browser = 'Chrome';
            else if (/Safari/i.test(ua)) browser = 'Safari';
            else if (/Firefox/i.test(ua)) browser = 'Firefox';
            else if (/Edge/i.test(ua)) browser = 'Edge';

            const pInput = document.getElementById('clientPlatform');
            const bInput = document.getElementById('clientBrowser');
            const dInput = document.getElementById('clientDeviceName');
            if (pInput) pInput.value = platform;
            if (bInput) bInput.value = browser;
            if (dInput) dInput.value = platform + ' - ' + browser;
        });
    </script>

    {{-- SweetAlert2 Flash Messages --}}
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#003FA9',
                confirmButtonText: 'Coba Lagi',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-2.5 text-sm font-semibold'
                }
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-2xl'
                }
            });
        </script>
    @endif

</body>

</html>

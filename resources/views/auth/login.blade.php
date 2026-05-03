<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Login - Absensi Siswa SMP IT Yapura">
    <title>Login — Absensi Siswa SMP IT Yapura</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

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
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Boxicons --}}
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Animated gradient background */
        .animated-bg {
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #1e40af, #172554);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            animation: float linear infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-10vh) rotate(720deg);
                opacity: 0;
            }
        }

        /* Grid pattern overlay */
        .grid-pattern {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Glass card */
        .glass-login {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Input focus glow */
        .input-glow:focus-within {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* Fade-in up animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.7s ease-out forwards;
        }

        .fade-in-up-delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .fade-in-up-delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .fade-in-up-delay-3 {
            animation-delay: 0.3s;
            opacity: 0;
        }

        .fade-in-up-delay-4 {
            animation-delay: 0.4s;
            opacity: 0;
        }

        .fade-in-up-delay-5 {
            animation-delay: 0.5s;
            opacity: 0;
        }

        /* Scale in animation */
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .scale-in {
            animation: scaleIn 0.5s ease-out forwards;
        }

        /* Button shimmer */
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }

        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-shimmer:hover::after {
            left: 100%;
        }

        /* Custom checkbox */
        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .custom-checkbox:checked {
            background: #2563eb;
            border-color: #2563eb;
        }

        .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 4px;
            top: 1px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        /* Password toggle */
        .password-toggle {
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }
    </style>
</head>

<body class="antialiased">

    {{-- Full Page Background --}}
    <div class="animated-bg grid-pattern min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

        {{-- Floating Particles --}}
        <div class="particle" style="width:6px;height:6px;left:10%;animation-duration:25s;animation-delay:0s;"></div>
        <div class="particle" style="width:8px;height:8px;left:20%;animation-duration:20s;animation-delay:2s;"></div>
        <div class="particle" style="width:4px;height:4px;left:35%;animation-duration:28s;animation-delay:4s;"></div>
        <div class="particle" style="width:10px;height:10px;left:50%;animation-duration:22s;animation-delay:1s;"></div>
        <div class="particle" style="width:5px;height:5px;left:65%;animation-duration:30s;animation-delay:3s;"></div>
        <div class="particle" style="width:7px;height:7px;left:75%;animation-duration:18s;animation-delay:5s;"></div>
        <div class="particle" style="width:6px;height:6px;left:85%;animation-duration:24s;animation-delay:2s;"></div>
        <div class="particle" style="width:9px;height:9px;left:92%;animation-duration:26s;animation-delay:6s;"></div>

        {{-- Decorative Blurs --}}
        <div class="absolute top-1/4 -left-20 w-72 h-72 bg-primary-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-primary-400/10 rounded-full blur-3xl"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary-600/5 rounded-full blur-3xl">
        </div>

        {{-- Login Container --}}
        <div class="relative z-10 w-full max-w-[460px]">

            {{-- Logo & Title --}}
            <div class="text-center mb-8 fade-in-up">
                {{-- Logo Icon --}}
                <div
                    class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 shadow-2xl shadow-primary-500/30 mb-5 scale-in">
                    <img src="{{ asset('img/logo.png') }}" alt="">
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-black tracking-tight fade-in-up fade-in-up-delay-1">
                    SMP IT Yapura
                </h1>
                <p
                    class="text-primary-300/80 text-sm mt-2 max-w-xs mx-auto leading-relaxed fade-in-up fade-in-up-delay-2">
                    Absensi Siswa SMP IT Yapura
                </p>
                <div class="flex items-center justify-center gap-2 mt-3 fade-in-up fade-in-up-delay-3">
                    <div class="w-8 h-px bg-primary-400/30"></div>

                    <div class="w-8 h-px bg-primary-400/30"></div>
                </div>
            </div>

            {{-- Login Card --}}
            <div
                class="glass-login rounded-3xl shadow-2xl shadow-dark-950/20 p-8 sm:p-10 fade-in-up fade-in-up-delay-3">

                {{-- Card Header --}}
                <div class="mb-7">
                    <h2 class="text-xl font-bold text-dark-800 tracking-tight">Masuk ke Sistem</h2>
                    <p class="text-dark-400 text-sm mt-1">Silakan masukkan kredensial Anda untuk melanjutkan</p>
                </div>

                {{-- Login Form --}}
                <form action="{{ route('proseslogin') }}" method="POST" id="loginForm" class="space-y-5">
                    @csrf

                    {{-- Email Input --}}
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-semibold text-dark-700">
                            Alamat Email
                        </label>
                        <div class="relative input-glow rounded-xl transition-all duration-200">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class='bx bx-envelope text-lg text-dark-400'></i>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                autocomplete="email" autofocus
                                class="w-full pl-11 pr-4 py-3.5 bg-dark-50/80 border border-dark-200 rounded-xl text-sm text-dark-800 placeholder-dark-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all duration-200"
                                placeholder="contoh@email.com">
                        </div>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class='bx bx-error-circle'></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password Input --}}
                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-semibold text-dark-700">
                            Password
                        </label>
                        <div class="relative input-glow rounded-xl transition-all duration-200">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class='bx bx-lock-alt text-lg text-dark-400'></i>
                            </div>
                            <input type="password" id="password" name="password" required
                                autocomplete="current-password"
                                class="w-full pl-11 pr-12 py-3.5 bg-dark-50/80 border border-dark-200 rounded-xl text-sm text-dark-800 placeholder-dark-400 focus:outline-none focus:border-primary-500 focus:bg-white transition-all duration-200"
                                placeholder="Masukkan password">
                            <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center password-toggle text-dark-400">
                                <i class='bx bx-hide text-lg' id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class='bx bx-error-circle'></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between">
                        <label for="remember" class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                                class="custom-checkbox">
                            <span class="text-sm text-dark-500">Ingat saya</span>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="btnLogin"
                        class="btn-shimmer w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 focus:outline-none focus:ring-2 focus:ring-primary-500/30 active:scale-[0.98] transition-all duration-200">
                        <i class='bx bx-log-in text-lg'></i>
                        <span>Login</span>
                    </button>
                </form>

            </div>

            {{-- Footer --}}
            <div class="text-center mt-8 space-y-2 fade-in-up fade-in-up-delay-5">
                <p class="text-primary-300/50 text-xs">
                    &copy; {{ date('Y') }} SPK SMART BPNT — Desa Piloliyanga
                </p>
                <p class="text-primary-300/30 text-[11px]">
                    Sistem Pendukung Keputusan Menggunakan Metode SMART
                </p>
            </div>

        </div>
    </div>

    {{-- Scripts --}}
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bx-hide');
                toggleIcon.classList.add('bx-show');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bx-show');
                toggleIcon.classList.add('bx-hide');
            }
        }

        // Loading state on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memproses...</span>
            `;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
        });
    </script>

    {{-- SweetAlert for error messages --}}
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#2563eb',
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

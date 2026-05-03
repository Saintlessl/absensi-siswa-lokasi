<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Absensi Siswa - SMP IT Yapura">
    <title>@yield('title', 'Absensi Siswa') — SMP IT Yapura</title>

    {{-- Google Fonts: Inter (Formal & Professional) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}" type="image/x-icon">
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
                        accent: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Sidebar scrollbar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 2px;
        }

        /* Smooth transitions */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .content-transition {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Glass morphism card */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Gradient background */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Sidebar gradient */
        .sidebar-gradient {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }

        /* Hover lift effect */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* Pulse animation for notifications */
        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }

            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .pulse-ring::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid #ef4444;
            animation: pulse-ring 1.5s ease-out infinite;
        }

        /* Fade in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }

        /* Stagger children animation */
        .stagger-children>* {
            opacity: 0;
            animation: fadeIn 0.4s ease-out forwards;
        }

        .stagger-children>*:nth-child(1) {
            animation-delay: 0.05s;
        }

        .stagger-children>*:nth-child(2) {
            animation-delay: 0.1s;
        }

        .stagger-children>*:nth-child(3) {
            animation-delay: 0.15s;
        }

        .stagger-children>*:nth-child(4) {
            animation-delay: 0.2s;
        }

        .stagger-children>*:nth-child(5) {
            animation-delay: 0.25s;
        }

        .stagger-children>*:nth-child(6) {
            animation-delay: 0.3s;
        }

        /* Active menu indicator */
        .menu-active {
            position: relative;
        }

        .menu-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: linear-gradient(180deg, #3b82f6, #60a5fa);
            border-radius: 0 4px 4px 0;
        }

        /* Dropdown animation */
        .dropdown-enter {
            animation: dropdownEnter 0.2s ease-out forwards;
        }

        @keyframes dropdownEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-5px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>

    @stack('styles')
</head>

<body class="font-inter bg-dark-50 text-dark-800 antialiased">

    <div id="app" class="flex min-h-screen">

        {{-- Sidebar Overlay (Mobile) --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-dark-950/50 backdrop-blur-sm z-40 hidden lg:hidden"
            onclick="toggleSidebar()">
        </div>

        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Main Content Area --}}
        <div id="main-content" class="flex-1 lg:ml-[272px] content-transition min-h-screen flex flex-col">

            {{-- Header --}}
            @include('layouts.header')

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{-- Breadcrumb --}}
                @hasSection('breadcrumb')
                    <nav class="mb-6 fade-in" aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-sm text-dark-500">
                            <li class="flex items-center gap-1">
                                <i class='bx bx-home text-base'></i>
                                <a href="{{ route('dashboard') }}"
                                    class="hover:text-primary-600 transition-colors">Dashboard</a>
                            </li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                @endif

                {{-- Page Header --}}
                @hasSection('page-header')
                    <div class="mb-8 fade-in">
                        @yield('page-header')
                    </div>
                @endif

                {{-- Main Content --}}
                <div class="fade-in" style="animation-delay: 0.1s;">
                    @yield('content')
                </div>
            </main>

            {{-- Footer --}}
            <footer class="border-t border-dark-200 bg-white/60 backdrop-blur-sm px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-dark-400">
                    <p>&copy; {{ date('Y') }} Sistem Absensi - SMP IT Yapura.</p>

                </div>
            </footer>
        </div>
    </div>

    {{-- Global Scripts --}}
    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const mainContent = document.getElementById('main-content');

            if (window.innerWidth < 1024) {
                // Mobile: slide in/out
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');
            } else {
                // Desktop: collapse/expand
                sidebar.classList.toggle('lg:w-[272px]');
                sidebar.classList.toggle('lg:w-[72px]');
                mainContent.classList.toggle('lg:ml-[272px]');
                mainContent.classList.toggle('lg:ml-[72px]');

                const isCollapsed = sidebar.classList.contains('lg:w-[72px]');
                document.querySelectorAll('.sidebar-label').forEach(el => {
                    el.classList.toggle('lg:hidden', isCollapsed);
                    el.classList.toggle('lg:opacity-0', isCollapsed);
                });
                document.querySelectorAll('.sidebar-section-title').forEach(el => {
                    el.classList.toggle('lg:hidden', isCollapsed);
                });
                document.querySelectorAll('.sidebar-logo-text').forEach(el => {
                    el.classList.toggle('lg:hidden', isCollapsed);
                });
            }
        }

        // Close sidebar on window resize (mobile → desktop)
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                const overlay = document.getElementById('sidebar-overlay');
                const sidebar = document.getElementById('sidebar');
                overlay.classList.add('hidden');
                sidebar.classList.remove('-translate-x-full');
                document.body.classList.remove('overflow-hidden');
            }
        });

        // User Dropdown Toggle
        function toggleUserDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('dropdown-enter');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('user-dropdown');
            const trigger = document.getElementById('user-dropdown-trigger');
            if (dropdown && trigger && !trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

    {{-- Modals Stack --}}
    @stack('modals')

    {{-- SweetAlert Flash Messages --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                customClass: {
                    popup: 'font-inter'
                }
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                showConfirmButton: true,
                confirmButtonColor: '#2563eb',
                customClass: {
                    popup: 'font-inter'
                }
            });
        </script>
    @endif

    @stack('scripts')
</body>

</html>

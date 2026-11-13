<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun Guru — MySkanic</title>
    <link rel="shortcut icon" href="{{ \App\Models\SchoolSetting::getFaviconUrl() }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8'
                        },
                        dark: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>
    {{-- SweetAlert2 & MySkanic Design System Alerts --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/myskanic-alerts.css') }}">
</head>
<body class="font-inter bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-8 border border-dark-100 relative overflow-hidden">
        {{-- Decorative accent --}}
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-primary-500 via-indigo-500 to-purple-600"></div>

        <div class="text-center mb-6 pt-2">
            <div class="w-14 h-14 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center text-3xl mb-3 shadow-sm border border-primary-100">
                <i class='bx bx-user-check'></i>
            </div>
            <h1 class="text-xl font-bold text-dark-900">Aktivasi Akun Guru</h1>
            <p class="text-xs text-dark-500 mt-1">Sistem Presensi & Akademik Sekolah</p>
        </div>

        @if($isInvalid)
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-center">
                <i class='bx bx-error-circle text-4xl text-rose-500 mb-2'></i>
                <h3 class="text-sm font-bold text-rose-800">Tautan Tidak Valid atau Kedaluwarsa</h3>
                <p class="text-xs text-rose-600 mt-1">
                    Tautan aktivasi ini sudah pernah digunakan atau telah melewati batas waktu berlaku (72 jam).
                </p>
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-dark-800 text-white rounded-xl text-xs font-semibold hover:bg-dark-900 transition">
                        <i class='bx bx-arrow-back'></i> Kembali ke Halaman Login
                    </a>
                </div>
            </div>
        @else
            {{-- Teacher Info Card --}}
            <div class="mb-5 p-3.5 rounded-2xl bg-dark-50 border border-dark-200/70 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($guru->nama ?? 'G', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-dark-900 truncate">{{ $guru->nama }}</p>
                    <p class="text-[11px] text-dark-500 font-mono">{{ $user->email }}</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary-100 text-primary-800">GURU</span>
            </div>

            @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('teacher.activate.store', $token) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Buat Password Baru <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-dark-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-dark-700 mb-1">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password baru"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-dark-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white rounded-xl text-sm font-semibold shadow-lg shadow-primary-500/30 transition duration-200">
                        Aktifkan Akun & Masuk
                    </button>
                </div>
            </form>
        @endif

        <div class="mt-6 text-center text-[11px] text-dark-400">
            &copy; {{ date('Y') }} MySkanic. All rights reserved.
        </div>
    </div>

    {{-- Global MySkanic SweetAlert2 Integration --}}
    <script src="{{ asset('js/myskanic-alerts.js') }}"></script>
    <x-notifications />
</body>
</html>

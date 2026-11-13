<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Buat Password Baru — MySkanic</title>
    <link rel="shortcut icon" href="{{ \App\Models\SchoolSetting::getFaviconUrl() }}" type="image/x-icon">

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    {{-- Tailwind & Alpine --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    {{-- SweetAlert2 & MySkanic Design System Alerts --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/myskanic-alerts.css') }}">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#003FA9] via-[#003580] to-[#002B75] flex items-center justify-center p-4 sm:p-6 select-none"
      x-data="{ showPass1: false, showPass2: false, loading: false }">

    <div class="w-full max-w-md bg-white rounded-3xl sm:rounded-[32px] p-6 sm:p-8 shadow-2xl border border-blue-100/30 relative">

        {{-- Top Header / Brand --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-50 text-[#003FA9] mb-3 shadow-inner">
                <i class='bx bx-lock-open text-3xl'></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#003580] tracking-tight">
                Buat Password Baru
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                Verifikasi OTP berhasil. Masukkan password baru yang kuat untuk akun MySkanic Anda.
            </p>
        </div>

        {{-- Progress Step Indicator --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                <i class='bx bx-check'></i> 1 Identifikasi
            </span>
            <span class="w-4 h-0.5 bg-emerald-400"></span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                <i class='bx bx-check'></i> 2 OTP
            </span>
            <span class="w-4 h-0.5 bg-[#003FA9]"></span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#003FA9] text-white">
                <span>3</span> Reset Password
            </span>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-start gap-2.5">
                <i class='bx bx-check-circle text-lg shrink-0 mt-0.5 text-emerald-500'></i>
                <div class="flex-1 font-medium leading-relaxed">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-700 text-xs flex items-start gap-2.5">
                <i class='bx bx-error-circle text-lg shrink-0 mt-0.5 text-rose-500'></i>
                <div class="flex-1 font-medium leading-relaxed">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-700 text-xs flex items-start gap-2.5">
                <i class='bx bx-error-circle text-lg shrink-0 mt-0.5 text-rose-500'></i>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Reset Password --}}
        <form method="POST" action="{{ route('password.reset.store') }}" class="space-y-4" @submit="loading = true">
            @csrf

            {{-- Password Baru --}}
            <div>
                <label for="password" class="text-xs font-bold text-[#003580] mb-2 block tracking-wide">
                    Password Baru
                </label>
                <div class="relative">
                    <input :type="showPass1 ? 'text' : 'password'"
                           id="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           placeholder="Minimal 8 karakter"
                           class="w-full bg-[#F0F5FF] text-[#003580] placeholder-slate-400 font-medium text-sm rounded-2xl pl-11 pr-11 py-3.5 border border-transparent focus:border-[#003FA9] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 transition shadow-inner">
                    <i class='bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                    <button type="button"
                            @click="showPass1 = !showPass1"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        <i class='bx' :class="showPass1 ? 'bx-hide' : 'bx-show'"></i>
                    </button>
                </div>
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_confirmation" class="text-xs font-bold text-[#003580] mb-2 block tracking-wide">
                    Konfirmasi Password Baru
                </label>
                <div class="relative">
                    <input :type="showPass2 ? 'text' : 'password'"
                           id="password_confirmation"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="Ulangi password baru Anda"
                           class="w-full bg-[#F0F5FF] text-[#003580] placeholder-slate-400 font-medium text-sm rounded-2xl pl-11 pr-11 py-3.5 border border-transparent focus:border-[#003FA9] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 transition shadow-inner">
                    <i class='bx bx-check-double absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                    <button type="button"
                            @click="showPass2 = !showPass2"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        <i class='bx' :class="showPass2 ? 'bx-hide' : 'bx-show'"></i>
                    </button>
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-2xl text-[11px] text-slate-500 leading-normal flex items-start gap-2 border border-slate-100">
                <i class='bx bx-shield-quarter text-base text-[#003FA9] mt-0.5 shrink-0'></i>
                <span>Gunakan kombinasi minimal 8 karakter dengan huruf dan angka untuk keamanan akun siswa Anda.</span>
            </div>

            <button type="submit"
                    :disabled="loading"
                    class="w-full bg-[#003FA9] hover:bg-[#002B75] active:scale-[0.99] text-white font-bold text-sm py-3.5 rounded-2xl shadow-lg shadow-[#003FA9]/25 transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#003FA9] focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed mt-2">
                <template x-if="!loading">
                    <span class="flex items-center gap-2">
                        <i class='bx bx-save text-xl'></i>
                        <span>Simpan Password Baru</span>
                    </span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Menyimpan Password...</span>
                    </span>
                </template>
            </button>
        </form>

        {{-- Footer Link: Kembali ke Login --}}
        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                <i class='bx bx-arrow-back text-sm'></i>
                <span>Batal & Kembali ke Halaman Login</span>
            </a>
        </div>

    </div>

    {{-- Global MySkanic SweetAlert2 Integration --}}
    <script src="{{ asset('js/myskanic-alerts.js') }}"></script>
    <x-notifications />
</body>
</html>

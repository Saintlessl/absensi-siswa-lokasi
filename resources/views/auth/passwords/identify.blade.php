<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lupa Password — MySkanic SMK Negeri 1 Ciomas</title>
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}" type="image/x-icon">

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

<body class="min-h-screen bg-gradient-to-b from-[#003FA9] via-[#003580] to-[#002B75] flex items-center justify-center p-4 sm:p-6 select-none">

    <div class="w-full max-w-md bg-white rounded-3xl sm:rounded-[32px] p-6 sm:p-8 shadow-2xl border border-blue-100/30 relative">

        {{-- Top Header / Brand --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#EFF5FF] text-[#003FA9] mb-3 shadow-inner">
                <i class='bx bx-key text-3xl'></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#003580] tracking-tight">
                Pemulihan Password
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                Masukkan Email akun atau NISN siswa untuk menerima kode verifikasi OTP melalui WhatsApp.
            </p>
        </div>

        {{-- Progress Step Indicator --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#003FA9] text-white">
                <span>1</span> Identifikasi
            </span>
            <span class="w-4 h-0.5 bg-slate-200"></span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-400">
                <span>2</span> Verifikasi OTP
            </span>
            <span class="w-4 h-0.5 bg-slate-200"></span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-400">
                <span>3</span> Reset Password
            </span>
        </div>

        {{-- Flash Messages --}}
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

        {{-- Form Identifikasi --}}
        <form method="POST" action="{{ route('password.identify') }}" class="space-y-4" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div>
                <label for="identifier" class="text-xs font-bold text-[#003580] mb-2 block tracking-wide">
                    Email Akun atau NISN Siswa
                </label>
                <div class="relative">
                    <input type="text"
                           id="identifier"
                           name="identifier"
                           value="{{ old('identifier') }}"
                           required
                           autofocus
                           autocomplete="username"
                           placeholder="contoh: siswa@myskanic.com atau 0081234567"
                           class="w-full bg-[#F0F5FF] text-[#003580] placeholder-slate-400 font-medium text-sm rounded-2xl pl-11 pr-4 py-3.5 border border-transparent focus:border-[#003FA9] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20 transition shadow-inner">
                    <i class='bx bx-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5 leading-normal">
                    * Pastikan nomor WhatsApp pribadi kamu telah terdaftar di database sekolah untuk menerima kode OTP 6 digit.
                </p>
            </div>

            <button type="submit"
                    :disabled="loading"
                    class="w-full bg-[#003FA9] hover:bg-[#002B75] active:scale-[0.99] text-white font-bold text-sm py-3.5 rounded-2xl shadow-lg shadow-[#003FA9]/25 transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#003FA9] focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed mt-2">
                <template x-if="!loading">
                    <span class="flex items-center gap-2">
                        <i class='bx bxl-whatsapp text-xl'></i>
                        <span>Kirim Kode OTP WhatsApp</span>
                    </span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses Pengiriman OTP...</span>
                    </span>
                </template>
            </button>
        </form>

        {{-- Footer Link: Kembali ke Login --}}
        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-[#003FA9] hover:text-[#002B75] hover:underline transition">
                <i class='bx bx-arrow-back text-sm'></i>
                <span>Kembali ke Halaman Login</span>
            </a>
        </div>

    </div>

    {{-- Global MySkanic SweetAlert2 Integration --}}
    <script src="{{ asset('js/myskanic-alerts.js') }}"></script>
    <x-notifications />
</body>
</html>

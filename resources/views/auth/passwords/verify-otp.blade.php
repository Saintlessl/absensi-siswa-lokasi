<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verifikasi OTP WhatsApp — MySkanic</title>
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
        /* Hide number spin arrows */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#003FA9] via-[#003580] to-[#002B75] flex items-center justify-center p-4 sm:p-6 select-none"
      x-data="otpVerifier({{ $remainingSeconds }}, {{ $resendCooldown }})">

    <div class="w-full max-w-md bg-white rounded-3xl sm:rounded-[32px] p-6 sm:p-8 shadow-2xl border border-blue-100/30 relative">

        {{-- Top Header / Brand --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 mb-3 shadow-inner">
                <i class='bx bxl-whatsapp text-3xl'></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#003580] tracking-tight">
                Verifikasi Kode OTP
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                Kode verifikasi 6 digit telah dikirimkan ke nomor WhatsApp:
            </p>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-mono font-bold text-xs mt-2">
                <i class='bx bxl-whatsapp text-base'></i>
                <span>{{ $maskedPhone }}</span>
            </div>
        </div>

        {{-- Progress Step Indicator --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                <i class='bx bx-check'></i> 1 Identifikasi
            </span>
            <span class="w-4 h-0.5 bg-[#003FA9]"></span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#003FA9] text-white">
                <span>2</span> Verifikasi OTP
            </span>
            <span class="w-4 h-0.5 bg-slate-200"></span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-400">
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

        {{-- Verification Form --}}
        <form method="POST" action="{{ route('password.otp.verify') }}" @submit="handleSubmit($event)">
            @csrf
            <input type="hidden" name="otp" :value="fullOtp">

            {{-- 6 Individual Digit Input Boxes --}}
            <div class="mb-5">
                <label class="text-xs font-bold text-[#003580] mb-3 block text-center tracking-wide">
                    MASUKKAN 6 DIGIT KODE
                </label>
                <div class="flex items-center justify-between gap-1.5 sm:gap-2" @paste="handlePaste($event)">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input type="text"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="1"
                               x-ref="otpInput"
                               :id="'digit_' + index"
                               x-model="digits[index]"
                               @input="handleInput(index, $event)"
                               @keydown="handleKeydown(index, $event)"
                               class="w-12 h-14 sm:w-13 sm:h-16 text-center text-xl sm:text-2xl font-bold font-mono rounded-2xl border-2 transition-all shadow-inner focus:outline-none focus:ring-2 focus:ring-[#003FA9]/20"
                               :class="digits[index] ? 'border-[#003FA9] bg-white text-[#003FA9]' : 'border-slate-200 bg-[#F0F5FF] text-slate-800'">
                    </template>
                </div>
            </div>

            {{-- Expiration Countdown --}}
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <i class='bx bx-time-five text-slate-400'></i>
                    <span>Berlaku hingga:</span>
                    <strong class="font-mono text-slate-800" :class="remaining <= 60 ? 'text-rose-600 font-bold' : ''" x-text="formatTime(remaining)">05:00</strong>
                </div>
            </div>

            {{-- Submit Verification Button --}}
            <button type="submit"
                    :disabled="loading || fullOtp.length !== 6 || remaining <= 0"
                    class="w-full bg-[#003FA9] hover:bg-[#002B75] active:scale-[0.99] text-white font-bold text-sm py-3.5 rounded-2xl shadow-lg shadow-[#003FA9]/25 transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#003FA9] focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <template x-if="!loading">
                    <span class="flex items-center gap-2">
                        <i class='bx bx-check-shield text-xl'></i>
                        <span>Verifikasi Kode OTP</span>
                    </span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memverifikasi...</span>
                    </span>
                </template>
            </button>
        </form>

        {{-- Resend OTP Section with Cooldown --}}
        <div class="mt-5 text-center">
            <form method="POST" action="{{ route('password.otp.resend') }}">
                @csrf
                <p class="text-xs text-slate-500 mb-2">Tidak menerima kode verifikasi?</p>
                <button type="submit"
                        :disabled="resendCooldown > 0"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#003FA9] hover:text-[#002B75] disabled:text-slate-400 disabled:cursor-not-allowed transition">
                    <i class='bx bx-refresh text-base'></i>
                    <template x-if="resendCooldown > 0">
                        <span>Kirim Ulang Kode (<span x-text="resendCooldown"></span>s)</span>
                    </template>
                    <template x-if="resendCooldown <= 0">
                        <span>Kirim Ulang Kode Sekarang</span>
                    </template>
                </button>
            </form>
        </div>

        {{-- Back Link --}}
        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a href="{{ route('password.forgot') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                <i class='bx bx-arrow-back text-sm'></i>
                <span>Ganti Email atau Nomor Telepon</span>
            </a>
        </div>

    </div>

    <script>
        function otpVerifier(initialExpirySeconds, initialResendCooldown) {
            return {
                digits: ['', '', '', '', '', ''],
                remaining: initialExpirySeconds,
                resendCooldown: initialResendCooldown,
                loading: false,

                get fullOtp() {
                    return this.digits.join('');
                },

                init() {
                    // Auto-focus first input box
                    this.$nextTick(() => {
                        const firstInput = document.getElementById('digit_0');
                        if (firstInput) firstInput.focus();
                    });

                    // Countdown timer
                    setInterval(() => {
                        if (this.remaining > 0) this.remaining--;
                        if (this.resendCooldown > 0) this.resendCooldown--;
                    }, 1000);
                },

                handleInput(index, event) {
                    const val = event.target.value.replace(/[^0-9]/g, '');
                    this.digits[index] = val ? val.slice(-1) : '';

                    if (val && index < 5) {
                        const next = document.getElementById('digit_' + (index + 1));
                        if (next) next.focus();
                    }
                },

                handleKeydown(index, event) {
                    if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                        const prev = document.getElementById('digit_' + (index - 1));
                        if (prev) {
                            prev.focus();
                            this.digits[index - 1] = '';
                        }
                    }
                },

                handlePaste(event) {
                    event.preventDefault();
                    const pastedData = (event.clipboardData || window.clipboardData).getData('text');
                    const clean = pastedData.replace(/[^0-9]/g, '').slice(0, 6);

                    for (let i = 0; i < 6; i++) {
                        this.digits[i] = clean[i] || '';
                    }

                    const targetIndex = Math.min(clean.length, 5);
                    const targetEl = document.getElementById('digit_' + targetIndex);
                    if (targetEl) targetEl.focus();
                },

                formatTime(sec) {
                    const m = Math.floor(sec / 60).toString().padStart(2, '0');
                    const s = (sec % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                handleSubmit(e) {
                    if (this.fullOtp.length !== 6 || this.remaining <= 0) {
                        e.preventDefault();
                        return;
                    }
                    this.loading = true;
                }
            }
        }
    </script>

    {{-- Global MySkanic SweetAlert2 Integration --}}
    <script src="{{ asset('js/myskanic-alerts.js') }}"></script>
    <x-notifications />
</body>
</html>

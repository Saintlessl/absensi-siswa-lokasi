<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun Orang Tua — MySkanic</title>
    <link rel="shortcut icon" href="{{ \App\Models\SchoolSetting::getFaviconUrl() }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    {{-- SweetAlert2 & MySkanic Alerts --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/myskanic-alerts.css') }}?v={{ time() }}">
    <script src="{{ asset('js/myskanic-alerts.js') }}?v={{ time() }}"></script>
</head>
<body class="font-inter bg-[#003EA7] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-[28px] shadow-2xl p-8 border border-slate-100 relative overflow-hidden text-slate-800">
        {{-- Logo Section --}}
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-[#003FA9] text-white mx-auto flex items-center justify-center text-3xl mb-3 shadow-md shadow-blue-900/20">
                <i class='bx bx-key'></i>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Aktivasi Akun Orang Tua</h1>
            <p class="text-xs text-slate-500 mt-1">Portal Monitoring Kehadiran & Akademik Siswa</p>
        </div>

        {{-- Error Alerts --}}
        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="flex items-center gap-1.5"><i class='bx bx-error-circle text-sm'></i> {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p class="text-xs text-slate-500 leading-relaxed mb-5">
            Tetapkan kata sandi baru untuk akun portal orang tua Anda. Gunakan minimal 8 karakter dengan kombinasi huruf dan angka untuk keamanan optimal.
        </p>

        <form class="space-y-4" method="POST" action="{{ route('parent.activate.store', $token) }}">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                <input class="w-full h-11 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs sm:text-sm px-4 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                    type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi <span class="text-rose-500">*</span></label>
                <input class="w-full h-11 rounded-xl bg-[#EFF5FF] border-0 text-slate-800 text-xs sm:text-sm px-4 focus:ring-2 focus:ring-[#003FA9] outline-none transition"
                    type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Ketik ulang kata sandi">
            </div>
            <button type="submit" class="w-full h-11 rounded-xl bg-[#003FA9] hover:bg-[#002D7A] text-white font-bold text-xs sm:text-sm shadow-sm transition">
                Aktifkan Akun Sekarang
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-[#003FA9] hover:underline">
                &larr; Kembali ke Halaman Masuk
            </a>
        </div>
    </div>
</body>
</html>

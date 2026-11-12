<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesi Berakhir — MySkanic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/myskanic-alerts.css') }}">
    <script src="{{ asset('js/myskanic-alerts.js') }}"></script>
</head>
<body style="background:#003FA9; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif;">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            MySkanicAlert.warning(
                'Halaman sudah terlalu lama terbuka sehingga token keamanan kedaluwarsa. Data Anda belum dikirim. Klik tombol di bawah untuk kembali dan ulangi proses.',
                'Sesi Keamanan Kedaluwarsa (419)'
            ).then(() => {
                const back = document.referrer || '{{ url()->previous() }}';
                window.location.href = back;
            });
        });
    </script>
    <noscript>
        <p style="color:#fff;font-family:sans-serif;padding:2rem">
            Sesi Anda telah berakhir. <a href="{{ url('/') }}" style="color:#9cf">Kembali ke beranda</a>.
        </p>
    </noscript>
</body>
</html>

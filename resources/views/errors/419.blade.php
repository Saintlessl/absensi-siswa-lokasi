<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesi Berakhir</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background:#003EA7">
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Sesi berakhir',
            text: 'Halaman sudah terlalu lama terbuka sehingga token keamanan kedaluwarsa. Data Anda belum dikirim. Klik Berikutnya untuk kembali dan ulangi penyimpanan.',
            confirmButtonText: 'Kembali',
            confirmButtonColor: '#2563eb',
            allowOutsideClick: false,
            customClass: { popup: 'font-inter' }
        }).then(() => {
            const back = document.referrer || '{{ url()->previous() }}';
            window.location.href = back;
        });
    </script>
    <noscript>
        <p style="color:#fff;font-family:sans-serif;padding:2rem">
            Sesi Anda telah berakhir. <a href="{{ url('/') }}" style="color:#9cf">Kembali ke beranda</a>.
        </p>
    </noscript>
</body>
</html>

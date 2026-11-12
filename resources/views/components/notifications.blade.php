@if (session('success') || session('error') || session('warning') || session('info') || session('status') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                MySkanicAlert.success(@js(session('success')));
            @endif

            @if (session('error'))
                MySkanicAlert.error(@js(session('error')));
            @endif

            @if (session('warning'))
                MySkanicAlert.warning(@js(session('warning')));
            @endif

            @if (session('info'))
                MySkanicAlert.info(@js(session('info')));
            @endif

            @if (session('status'))
                MySkanicAlert.toast(@js(session('status')), 'info');
            @endif

            @if ($errors->any())
                MySkanicAlert.validation(@js($errors->all()));
            @endif
        });
    </script>
@endif

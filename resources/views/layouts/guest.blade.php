<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Recruitment App')</title>
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-body">
    <div class="container py-5">
        @yield('content')
    </div>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: true,
                confirmButtonColor: '#0d6efd',
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: '{{ session('error') }}',
                showConfirmButton: true,
                confirmButtonColor: '#dc3545'
            });
        });
    </script>
    @endif

    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                html: '{!! implode('<br>', $errors->all()) !!}',
                showConfirmButton: true,
                confirmButtonColor: '#dc3545'
            });
        });
    </script>
    @endif

    {{-- Loading untuk form submission --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[data-loading="true"]').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        return;
                    }
                    
                    Swal.fire({
                        title: 'Mohon Tunggu...',
                        text: 'Sedang memproses',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>

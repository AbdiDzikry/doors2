<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @auth
        @if(!auth()->user()->has_seen_tour)
            <script>
                window.shouldStartTour = true;
            </script>
        @endif
    @endauth
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Success
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#089244',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            // Error
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#d33',
                });
            @endif

                // Status (Standard Laravel Status)
                @if(session('status'))
                    let status = "{{ session('status') }}";
                    if (status === 'profile-updated') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Profile Updated',
                            text: 'Informasi akun berhasil diperbarui.',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    } else if (status === 'password-updated') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Updated',
                            text: 'Password berhasil diubah.',
                            timer: 3000,
                        });
                    } else {
                        // Generic status
                        Swal.fire({
                            icon: 'info',
                            text: status,
                        });
                    }
                @endif
            });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'SIMOPANG - Sistem Monitoring & Prediksi Harga Pangan')</title>
    <meta name="description" content="@yield('meta_desc', 'Pantau harga pangan real-time dan prediksi tren masa depan dengan AI canggih.')">
    
    {{-- Tailwind CSS v3 CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Font Awesome 6 untuk Icon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    {{-- Konfigurasi Kustom Tailwind --}}
    <style type="text/tailwindcss">
        @layer utilities {
            .text-gradient {
                @apply bg-clip-text text-transparent bg-gradient-to-r from-blue-900 to-blue-500;
            }
            .bg-glass {
                @apply bg-white/70 backdrop-blur-md border border-white/20;
            }
            .shadow-soft {
                box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            }
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-white text-gray-700 overflow-x-hidden">

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Optional JavaScript untuk Mobile Menu --}}
    <script>
        // Toggle Mobile Menu
        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        
        if(btn) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }

        // Tutup menu saat link di klik
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.add('hidden');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
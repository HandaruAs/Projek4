@extends('layouts.landing')

@section('title', 'SIMOPANG - Pantau & Prediksi Harga Pangan dengan AI')

@section('content')

{{-- ==================== NAVBAR ==================== --}}
<nav class="fixed top-0 left-0 w-full z-50 bg-white/40 backdrop-blur-sm border-b border-white/30">
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12">
        <div class="flex justify-between items-center h-16 md:h-20">

            {{-- Logo & Brand --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl overflow-hidden shadow-md transform transition hover:scale-105">
                    <img src="{{ asset('images/LOGO-2.png') }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <span class="text-xl md:text-2xl font-bold tracking-tight text-gray-900">SIMOPANG</span>
            </div>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center space-x-8 font-medium text-gray-700">
                <a href="#home" class="hover:text-blue-700 transition">Home</a>
                <a href="#fitur" class="hover:text-blue-700 transition">Fitur</a>
                <a href="#tentang" class="hover:text-blue-700 transition">Tentang</a>
                <a href="{{ route('login') }}" class="ml-4 px-5 py-2.5 bg-gradient-to-r from-blue-900 to-blue-600 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    Login
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-900 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu Dropdown --}}
    <div id="mobile-menu" class="hidden md:hidden absolute top-16 left-0 w-full bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-lg py-4 px-4">
        <div class="flex flex-col space-y-4 font-medium text-gray-700">
            <a href="#home" class="px-4 py-2 hover:bg-blue-50 rounded-lg">Home</a>
            <a href="#fitur" class="px-4 py-2 hover:bg-blue-50 rounded-lg">Fitur</a>
            <a href="#tentang" class="px-4 py-2 hover:bg-blue-50 rounded-lg">Tentang</a>
            <a href="{{ route('login') }}" class="mt-2 px-5 py-3 bg-gradient-to-r from-blue-900 to-blue-600 text-white rounded-xl text-center font-semibold shadow-md">
                Login
            </a>
        </div>
    </div>
</nav>

{{-- ==================== HERO SECTION ==================== --}}
<section id="home" class="relative min-h-screen flex items-center justify-center text-center overflow-hidden">

    {{-- Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('images/Commodity.png') }}"
            alt="Background"
            class="w-full h-full object-cover">
        {{-- Overlay gelap --}}
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/70"></div>
    </div>

    {{-- Content --}}
    <div class="relative z-10 px-4 max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight drop-shadow-lg">
            Pantau & Prediksi <br>
            <span class="text-blue-400">Harga Pangan</span> dengan AI
        </h1>
        <p class="mt-6 text-lg md:text-xl text-gray-200 max-w-2xl mx-auto leading-relaxed">
            Sistem ini membantu pengguna dalam mengambil keputusan yang lebih tepat terkait kebutuhan dan pengelolaan pangan.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}"
                class="px-10 py-4 bg-gradient-to-r from-blue-700 to-blue-500 text-white rounded-xl text-lg font-semibold shadow-lg hover:shadow-2xl hover:from-blue-800 hover:to-blue-600 transform hover:-translate-y-1 transition-all duration-200">
                Daftar Sekarang <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 animate-bounce">
        <a href="#fitur">
            <i class="fas fa-chevron-down text-white/70 text-2xl"></i>
        </a>
    </div>
</section>

{{-- ==================== FITUR UTAMA ==================== --}}
<section id="fitur" class="py-16 md:py-24 px-4 bg-gray-50/50">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Fitur <span class="text-gradient">Unggulan</span></h2>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Dilengkapi teknologi AI terkini untuk membantu keputusan Anda lebih akurat.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">

            {{-- Card 1: Monitoring Harga --}}
            <div class="group bg-white p-6 md:p-8 rounded-2xl shadow-soft hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-100 transform hover:-translate-y-2">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-600 to-blue-400 text-white flex items-center justify-center mb-5 shadow-md group-hover:scale-110 transition">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Monitoring Harga</h3>
                <p class="text-gray-600">Pantau pergerakan harga pangan strategis secara real-time dari berbagai pasar di Indonesia.</p>
            </div>

            {{-- Card 2: Prediksi AI --}}
            <div class="group bg-white p-6 md:p-8 rounded-2xl shadow-soft hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-100 transform hover:-translate-y-2">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-700 to-blue-500 text-white flex items-center justify-center mb-5 shadow-md group-hover:scale-110 transition">
                    <i class="fas fa-brain text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Prediksi AI</h3>
                <p class="text-gray-600">Algoritma canggih berbasis Prophet untuk meramal tren harga 1-4 minggu ke depan.</p>
            </div>

            {{-- Card 3: Simulasi Pengeluaran --}}
            <div class="group bg-white p-6 md:p-8 rounded-2xl shadow-soft hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-100 transform hover:-translate-y-2">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-800 to-blue-600 text-white flex items-center justify-center mb-5 shadow-md group-hover:scale-110 transition">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Simulasi Pengeluaran</h3>
                <p class="text-gray-600">Hitung estimasi biaya belanja berdasarkan komoditas yang ingin Anda pantau.</p>
            </div>

            {{-- Card 4: AI Assistant --}}
            <div class="group bg-white p-6 md:p-8 rounded-2xl shadow-soft hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-100 transform hover:-translate-y-2">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-900 to-blue-700 text-white flex items-center justify-center mb-5 shadow-md group-hover:scale-110 transition">
                    <i class="fas fa-robot text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">AI Assistant</h3>
                <p class="text-gray-600">Tanya jawab interaktif seputar data pangan, rekomendasi, dan insight pasar.</p>
            </div>
        </div>
    </div>
</section>

{{-- ==================== PREVIEW DASHBOARD ==================== --}}
<section id="tentang" class="py-16 md:py-24 px-4 max-w-7xl mx-auto">
    <div class="grid md:grid-cols-2 gap-12 items-center">
        <div>
            <span class="text-blue-700 font-semibold tracking-wider text-sm">DASHBOARD INTERAKTIF</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2">Semua data dalam satu <span class="text-gradient">dashboard interaktif</span></h2>
            <p class="mt-4 text-lg text-gray-600">Visualisasi data yang intuitif dan mudah dipahami. Dari peta harga hingga grafik prediksi, semua tersaji rapi.</p>
            <ul class="mt-6 space-y-3">
                <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-600"></i> <span>Filter berdasarkan komoditas & pasar</span></li>
                <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-600"></i> <span>Export data ke CSV/Excel</span></li>
                <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-600"></i> <span>Notifikasi perubahan harga signifikan</span></li>
            </ul>
        </div>
        <div class="relative">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden p-2">
                <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard Preview" class="w-full h-auto rounded-2xl">
            </div>
            <div class="absolute -bottom-5 -right-5 w-32 h-32 bg-blue-100 rounded-full -z-10"></div>
            <div class="absolute -top-5 -left-5 w-32 h-32 bg-indigo-100 rounded-full -z-10"></div>
        </div>
    </div>
</section>

{{-- ==================== KEUNGGULAN ==================== --}}
<section class="py-16 px-4 bg-gradient-to-b from-white to-blue-50/30">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Mengapa <span class="text-gradient">SIMOPANG?</span></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white/60 backdrop-blur-sm p-6 rounded-2xl border border-white/50 shadow-sm">
                <div class="text-blue-700 text-2xl mb-3"><i class="fas fa-database"></i></div>
                <h4 class="text-lg font-bold text-gray-900">Data Historis</h4>
                <p class="text-gray-600 text-sm mt-1">Analisis berbasis data harga historis komoditas yang terstruktur.</p>
            </div>
            <div class="bg-white/60 backdrop-blur-sm p-6 rounded-2xl border border-white/50 shadow-sm">
                <div class="text-blue-700 text-2xl mb-3"><i class="fas fa-chart-line"></i></div>
                <h4 class="text-lg font-bold text-gray-900">Holt-Winters</h4>
                <p class="text-gray-600 text-sm mt-1">Model Triple Exponential Smoothing untuk menangkap tren & musiman.</p>
            </div>
            <div class="bg-white/60 backdrop-blur-sm p-6 rounded-2xl border border-white/50 shadow-sm">
                <div class="text-blue-700 text-2xl mb-3"><i class="fas fa-bullseye"></i></div>
                <h4 class="text-lg font-bold text-gray-900">Akurasi Tinggi</h4>
                <p class="text-gray-600 text-sm mt-1">MAPE < 5% untuk komoditas utama.</p>
            </div>
            <div class="bg-white/60 backdrop-blur-sm p-6 rounded-2xl border border-white/50 shadow-sm">
                <div class="text-blue-700 text-2xl mb-3"><i class="fas fa-bolt"></i></div>
                <h4 class="text-lg font-bold text-gray-900">Groq AI</h4>
                <p class="text-gray-600 text-sm mt-1">Inferensi ultra-cepat dengan LLM Llama via Groq Cloud.</p>
            </div>
        </div>
    </div>
</section>

{{-- ==================== CTA SECTION ==================== --}}
<section class="py-20 md:py-28 px-4">
    <div class="max-w-4xl mx-auto text-center bg-gradient-to-r from-blue-900 to-blue-700 rounded-3xl p-10 md:p-16 shadow-2xl text-white">
        <h2 class="text-3xl md:text-4xl font-bold">Mulai gunakan SIMOPANG sekarang</h2>
        <p class="mt-4 text-blue-100 text-lg">Bergabunglah dengan ribuan pengguna yang telah merasakan manfaat prediksi harga pangan.</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="px-8 py-4 bg-white text-blue-900 rounded-xl text-lg font-bold shadow-lg hover:bg-gray-100 transform hover:-translate-y-1 transition-all duration-200">
                Daftar Sekarang
            </a>
        </div>
    </div>
</section>

{{-- ==================== FOOTER ==================== --}}
<footer id="kontak" class="bg-gray-50 border-t border-gray-100 pt-16 pb-8 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            {{-- Brand --}}
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-900 to-blue-500 flex items-center justify-center shadow-md overflow-hidden">
                        <img src="{{ asset('images/LOGO-2.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-xl font-bold text-gray-900">SIMOPANG</span>
                </div>
                <p class="text-gray-600 text-sm max-w-md">Sistem Monitoring & Prediksi Harga Pangan. Solusi cerdas berbasis data untuk ketahanan pangan Indonesia.</p>
                <div class="flex space-x-4 mt-6">
                    <a href="https://www.facebook.com/profile.php?id=61589738575911" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-blue-700 hover:border-blue-300 transition shadow-sm"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/official.simopang?igsh=MTdpM3czZjludGt0aA==" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-blue-700 hover:border-blue-300 transition shadow-sm"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            {{-- Navigasi --}}
            <div>
                <h4 class="font-semibold text-gray-900 mb-4">Navigasi</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#home" class="text-gray-600 hover:text-blue-700 transition">Home</a></li>
                    <li><a href="#fitur" class="text-gray-600 hover:text-blue-700 transition">Fitur</a></li>
                    <li><a href="#tentang" class="text-gray-600 hover:text-blue-700 transition">Tentang</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-4">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-gray-600 hover:text-blue-700 transition">Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-blue-700 transition">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-blue-700 transition">Bantuan</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-200 mt-12 pt-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} SIMOPANG. All rights reserved.
        </div>
    </div>
</footer>

@endsection
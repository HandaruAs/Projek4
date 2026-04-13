@extends('layouts.landing')

@section('title', 'SIMOPANG - Pantau & Prediksi Harga Pangan dengan AI')

@section('content')
<div class="min-h-screen bg-white">
    
    {{-- ========== NAVBAR ========== --}}
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                
                {{-- Logo --}}
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-900">SIMOPANG</span>
                </a>

                {{-- Desktop Menu --}}
                <div class="hidden lg:flex items-center space-x-1">
                    <nav class="flex items-center space-x-1">
                        <a href="#home" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 transition rounded-lg hover:bg-blue-50">Home</a>
                        <a href="#fitur" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 transition rounded-lg hover:bg-blue-50">Fitur</a>
                        <a href="#preview" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 transition rounded-lg hover:bg-blue-50">Preview</a>
                        <a href="#tentang" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 transition rounded-lg hover:bg-blue-50">Tentang</a>
                        <a href="#kontak" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 transition rounded-lg hover:bg-blue-50">Kontak</a>
                    </nav>
                </div>

                {{-- Right Buttons --}}
                <div class="flex items-center space-x-3">
                    <a href="{{ route('login') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-full hover:shadow-lg hover:shadow-blue-500/30 hover:scale-105 transition-all duration-300">
                        Login
                    </a>
                    
                    {{-- Mobile Menu Button --}}
                    <button id="mobile-menu-button" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition" aria-expanded="false">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="lg:hidden hidden opacity-0 -translate-y-2 transition-all duration-300">
            <div class="glass-effect mx-4 mt-2 rounded-2xl p-4 shadow-xl border border-slate-200">
                <div class="flex flex-col space-y-2">
                    <a href="#home" class="px-4 py-3 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">Home</a>
                    <a href="#fitur" class="px-4 py-3 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">Fitur</a>
                    <a href="#preview" class="px-4 py-3 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">Preview</a>
                    <a href="#tentang" class="px-4 py-3 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">Tentang</a>
                    <a href="#kontak" class="px-4 py-3 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">Kontak</a>
                    <div class="border-t border-slate-200 my-2"></div>
                    <a href="#" class="px-4 py-3 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">Login</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- ========== HERO SECTION ========== --}}
    <section id="home" class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        {{-- Background Elements --}}
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-cyan-50"></div>
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float-slow"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-cyan-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float-medium"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-blue-100 to-cyan-100 rounded-full filter blur-3xl opacity-20"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                {{-- Left Content --}}
                <div class="space-y-8 animate-on-scroll">
                    {{-- Badge --}}
                    <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-gradient-to-r from-blue-600/10 to-cyan-500/10 border border-blue-200">
                        <span class="relative flex h-2 w-2 mr-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-blue-700">Live Data • Update Real-time</span>
                    </div>

                    {{-- Headline --}}
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight">
                        <span class="text-slate-900">Pantau &</span><br/>
                        <span class="bg-gradient-to-r from-blue-600 to-cyan-500 bg-clip-text text-transparent">Prediksi Harga</span><br/>
                        <span class="text-slate-900">Pangan dengan AI</span>
                    </h1>

                    {{-- Description --}}
                    <p class="text-lg md:text-xl text-slate-600 max-w-lg">
                        SIMOPANG (Sistem Monitoring Pangan) Sistem ini membantu pengguna dalam mengambil keputusan yang lebih tepat terkait kebutuhan dan pengelolaan pangan.
                        Solusi cerdas untuk monitoring harga, prediksi tren, dan simulasi pengeluaran kebutuhan pokok Anda.
                    </p>

                    {{-- CTA Buttons --}}
                    <div class="flex flex-wrap gap-4">
                        <a href="#" class="btn-primary flex items-center gap-2 group">
                            Mulai Sekarang
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Right Content - Dashboard Mockup --}}
                <div class="relative animate-on-scroll" data-parallax="0.1">
                    <div class="relative z-10">
                        {{-- Glow Effect --}}
                        <div class="absolute -inset-4 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-3xl blur-2xl opacity-20 animate-glow"></div>
                        
                        {{-- Main Card --}}
                        <div class="relative glass-effect rounded-2xl shadow-2xl border border-white/30 overflow-hidden">
                            {{-- Dashboard Header --}}
                            <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="flex gap-1.5">
                                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                        </div>
                                        <span class="text-xs text-slate-400 ml-2">SIMOPANG Dashboard</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="w-6 h-6 rounded bg-slate-700"></div>
                                        <div class="w-6 h-6 rounded bg-slate-700"></div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Dashboard Content --}}
                            <div class="p-5 space-y-4 bg-white/50">
                                {{-- Price Cards --}}
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-white rounded-xl p-3 shadow-sm">
                                        <div class="text-xs text-slate-500">Beras</div>
                                        <div class="text-lg font-bold text-slate-900">Rp 12.500</div>
                                        <div class="text-xs text-green-600">↓ 2.3%</div>
                                    </div>
                                    <div class="bg-white rounded-xl p-3 shadow-sm">
                                        <div class="text-xs text-slate-500">Minyak</div>
                                        <div class="text-lg font-bold text-slate-900">Rp 15.800</div>
                                        <div class="text-xs text-red-600">↑ 1.2%</div>
                                    </div>
                                    <div class="bg-white rounded-xl p-3 shadow-sm">
                                        <div class="text-xs text-slate-500">Telur</div>
                                        <div class="text-lg font-bold text-slate-900">Rp 28.500</div>
                                        <div class="text-xs text-green-600">↓ 0.8%</div>
                                    </div>
                                </div>
                                
                                {{-- Chart --}}
                                <div class="bg-white rounded-xl p-4 shadow-sm">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-semibold text-slate-700">Tren Harga 7 Hari</span>
                                        <span class="text-xs text-blue-600">+5.2%</span>
                                    </div>
                                    <div class="h-32 flex items-end gap-1">
                                        <div class="flex-1 h-16 bg-gradient-to-t from-blue-500 to-cyan-400 rounded"></div>
                                        <div class="flex-1 h-20 bg-gradient-to-t from-blue-500 to-cyan-400 rounded"></div>
                                        <div class="flex-1 h-14 bg-gradient-to-t from-blue-500 to-cyan-400 rounded"></div>
                                        <div class="flex-1 h-24 bg-gradient-to-t from-blue-500 to-cyan-400 rounded"></div>
                                        <div class="flex-1 h-20 bg-gradient-to-t from-blue-500 to-cyan-400 rounded"></div>
                                        <div class="flex-1 h-28 bg-gradient-to-t from-blue-500 to-cyan-400 rounded"></div>
                                        <div class="flex-1 h-24 bg-gradient-to-t from-blue-500 to-cyan-400 rounded"></div>
                                    </div>
                                </div>
                                
                                {{-- AI Prediction --}}
                                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-4 border border-blue-100">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        <span class="text-sm font-semibold text-slate-700">Prediksi AI (30 Hari)</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl font-bold text-slate-900">Rp 13.200</span>
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Confidence 95%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Floating Elements --}}
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl shadow-xl flex items-center justify-center animate-float-fast z-20">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-600 rounded-2xl shadow-xl flex items-center justify-center animate-float-medium z-20">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Scroll Indicator --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <div class="w-6 h-10 border-2 border-slate-400 rounded-full flex justify-center">
                <div class="w-1 h-3 bg-slate-400 rounded-full mt-2 animate-pulse"></div>
            </div>
        </div>
    </section>

    {{-- ========== FITUR UTAMA ========== --}}
    <section id="fitur" class="py-20 md:py-28 bg-gradient-to-b from-white to-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Section Header --}}
            <div class="text-center max-w-3xl mx-auto mb-16 animate-on-scroll">
                <span class="badge badge-primary mb-4">✨ Fitur Unggulan</span>
                <h2 class="section-title">
                    Kenapa Memilih <span class="text-gradient">SIMOPANG?</span>
                </h2>
                <p class="section-subtitle">
                    Platform lengkap dengan teknologi AI terkini untuk membantu Anda memantau dan memprediksi harga pangan dengan akurat.
                </p>
            </div>

            {{-- Feature Cards Grid --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                
                {{-- Card 1: Monitoring --}}
                <div class="feature-card group animate-on-scroll">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Monitoring Harga</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Pantau grafik harga historis dan data real-time dari berbagai pasar tradisional dan modern.
                    </p>
                    <ul class="space-y-2 mb-4">
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update real-time
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            50+ komoditas
                        </li>
                    </ul>
                </div>

                {{-- Card 2: Prediksi AI --}}
                <div class="feature-card group animate-on-scroll">
                    <div class="w-14 h-14 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-cyan-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Prediksi AI</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Prediksi harga 30-90 hari ke depan dengan algoritma Prophet dan confidence interval.
                    </p>
                    <ul class="space-y-2 mb-4">
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Akurasi hingga 98%
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Confidence interval
                        </li>
                    </ul>
                </div>

                {{-- Card 3: Simulasi --}}
                <div class="feature-card group animate-on-scroll">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-indigo-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Simulasi Pengeluaran</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Hitung estimasi belanja bulanan berdasarkan konsumsi dan dapatkan insight penghematan.
                    </p>
                    <ul class="space-y-2 mb-4">
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Kalkulator interaktif
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Rekomendasi hemat
                        </li>
                    </ul>
                </div>

                {{-- Card 4: AI Assistant --}}
                <div class="feature-card group animate-on-scroll">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-purple-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">AI Assistant</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Chat interaktif 24/7 untuk tanya harga terkini, tren, dan analisis otomatis.
                    </p>
                    <ul class="space-y-2 mb-4">
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Respons instan
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Insight otomatis
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== PREVIEW DASHBOARD ========== --}}
    <section id="preview" class="py-20 md:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                {{-- Left Content --}}
                <div class="animate-on-scroll">
                    <span class="badge badge-primary mb-4">📊 Dashboard Interaktif</span>
                    <h3 class="text-3xl md:text-4xl font-bold mb-6 text-slate-900">
                        Semua data dalam satu <br/>
                        <span class="text-gradient">tampilan modern</span>
                    </h3>
                    <p class="text-lg text-slate-600 mb-8">
                        Pantau harga komoditas, simulasi anggaran, dan tanya AI assistant langsung dari dashboard yang intuitif dan mudah digunakan.
                    </p>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-1">Update Harga Real-time</h4>
                                <p class="text-sm text-slate-600">Data diperbarui setiap 15 menit dari berbagai sumber terpercaya</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-1">Grafik Interaktif</h4>
                                <p class="text-sm text-slate-600">Zoom, filter, dan analisis data historis dengan mudah</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-1">Export Data</h4>
                                <p class="text-sm text-slate-600">Download data dalam format PDF, Excel, atau CSV</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Content - Dashboard Preview --}}
                <div class="relative animate-on-scroll">
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-400/20 to-cyan-400/20 rounded-3xl blur-3xl"></div>
                    
                    <div class="relative bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-700">
                        {{-- Browser Header --}}
                        <div class="bg-slate-800 px-4 py-3 flex items-center gap-2 border-b border-slate-700">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="bg-slate-700 rounded-lg px-4 py-1.5 text-xs text-slate-400 text-center">
                                    app.simopang.id/dashboard
                                </div>
                            </div>
                        </div>
                        
                        {{-- Dashboard Content --}}
                        <div class="p-6 space-y-4">
                            {{-- Stats Row --}}
                            <div class="grid grid-cols-4 gap-3">
                                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700">
                                    <div class="text-xs text-slate-400">Beras Premium</div>
                                    <div class="text-lg font-bold text-white">Rp 12.500</div>
                                    <div class="text-xs text-green-400">↓ 2.3%</div>
                                </div>
                                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700">
                                    <div class="text-xs text-slate-400">Minyak Goreng</div>
                                    <div class="text-lg font-bold text-white">Rp 15.800</div>
                                    <div class="text-xs text-red-400">↑ 1.2%</div>
                                </div>
                                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700">
                                    <div class="text-xs text-slate-400">Telur Ayam</div>
                                    <div class="text-lg font-bold text-white">Rp 28.500</div>
                                    <div class="text-xs text-green-400">↓ 0.8%</div>
                                </div>
                                <div class="bg-slate-800/50 rounded-xl p-3 border border-slate-700">
                                    <div class="text-xs text-slate-400">Cabai Merah</div>
                                    <div class="text-lg font-bold text-white">Rp 45.000</div>
                                    <div class="text-xs text-red-400">↑ 5.6%</div>
                                </div>
                            </div>
                            
                            {{-- Chart Area --}}
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm font-semibold text-white">Tren Harga 30 Hari</span>
                                    <select class="bg-slate-700 text-white text-xs rounded-lg px-2 py-1 border border-slate-600">
                                        <option>Beras</option>
                                        <option>Minyak</option>
                                        <option>Telur</option>
                                    </select>
                                </div>
                                <div class="h-40 flex items-end gap-1">
                                    @for($i = 0; $i < 30; $i++)
                                        <div class="flex-1 bg-gradient-to-t from-blue-500 to-cyan-400 rounded-t" style="height: {{ rand(30, 100) }}%"></div>
                                    @endfor
                                </div>
                                <div class="flex justify-between mt-2 text-xs text-slate-400">
                                    <span>1 Mar</span>
                                    <span>10 Mar</span>
                                    <span>20 Mar</span>
                                    <span>30 Mar</span>
                                </div>
                            </div>
                            
                            {{-- AI Prediction Box --}}
                            <div class="bg-gradient-to-r from-blue-900/50 to-cyan-900/50 rounded-xl p-4 border border-blue-700/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                            </svg>
                                            <span class="text-sm font-semibold text-blue-300">Prediksi AI</span>
                                        </div>
                                        <div class="text-2xl font-bold text-white">Rp 13.200</div>
                                        <div class="text-xs text-blue-300">Harga prediksi 30 hari ke depan</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-blue-300 mb-1">Confidence</div>
                                        <div class="text-lg font-bold text-green-400">95%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== CTA SECTION ========== --}}
    <section class="py-20 md:py-28 bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div class="animate-on-scroll">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                    Mulai Pantau Harga Pangan <br/>
                    <span class="text-gradient">dengan Lebih Cerdas</span>
                </h2>
                <p class="text-lg text-slate-300 mb-8 max-w-2xl mx-auto">
                    Bergabung dengan ribuan pengguna yang sudah merasakan kemudahan memantau dan memprediksi harga pangan.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="bg-gradient-to-r from-blue-600 to-cyan-500 text-white px-8 py-4 rounded-full font-bold text-lg shadow-lg shadow-blue-500/30 hover:scale-105 transition-all duration-300 inline-flex items-center justify-center gap-2 group">
                        Coba Gratis 30 Hari
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    <a href="#" class="bg-transparent border-2 border-white/30 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/10 transition-all duration-300">
                        Hubungi Sales
                    </a>
                </div>
                
                <p class="text-sm text-slate-400 mt-6">Tidak perlu kartu kredit. Batalkan kapan saja.</p>
                
                {{-- Trust Badges --}}
                <div class="flex flex-wrap items-center justify-center gap-8 mt-12 pt-8 border-t border-slate-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-sm text-slate-400">Data Terenkripsi</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm text-slate-400">Support 24/7</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="text-sm text-slate-400">Pembayaran Aman</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FOOTER ========== --}}
    <footer id="kontak" class="bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
                
                {{-- Brand --}}
                <div class="col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-cyan-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <span class="font-bold text-xl text-slate-900">SIMOPANG</span>
                    </div>
                    <p class="text-sm text-slate-600 mb-6 max-w-xs">
                        Sistem Monitoring & Prediksi Harga Pangan berbasis AI untuk Indonesia yang lebih cerdas.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.69 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 14-7.503 14-14 0-.21 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.627 0-12 5.373-12 12 0 5.302 3.438 9.8 8.205 11.387.6.113.82-.26.82-.58 0-.287-.01-1.05-.015-2.06-3.338.726-4.042-1.416-4.042-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.085 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.108-.775.418-1.305.762-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.468-2.38 1.235-3.22-.123-.3-.535-1.52.117-3.16 0 0 1.008-.322 3.3 1.23.96-.267 1.98-.4 3-.405 1.02.005 2.04.138 3 .405 2.29-1.552 3.297-1.23 3.297-1.23.653 1.64.241 2.86.118 3.16.768.84 1.233 1.91 1.233 3.22 0 4.61-2.804 5.62-5.476 5.92.43.37.824 1.102.824 2.22 0 1.602-.015 2.894-.015 3.287 0 .322.216.698.83.578 4.765-1.588 8.2-6.086 8.2-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                {{-- Produk --}}
                <div>
                    <h4 class="font-semibold text-slate-900 mb-4">Produk</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Monitoring</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Prediksi AI</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Simulasi</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">AI Assistant</a></li>
                    </ul>
                </div>
                
                {{-- Perusahaan --}}
                <div>
                    <h4 class="font-semibold text-slate-900 mb-4">Perusahaan</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Tentang</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Blog</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Karir</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Kontak</a></li>
                    </ul>
                </div>
                
                {{-- Legal --}}
                <div>
                    <h4 class="font-semibold text-slate-900 mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Privasi</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Syarat</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Cookie</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-blue-600 transition">Lisensi</a></li>
                    </ul>
                </div>
            </div>
            
            {{-- Copyright --}}
            <div class="border-t border-slate-200 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-slate-500">
                    © {{ date('Y') }} SIMOPANG. All rights reserved.
                </p>
                <p class="text-sm text-slate-500">
                    Made with ❤️ for Indonesia
                </p>
            </div>
        </div>
    </footer>
</div>
@endsection

@push('scripts')
<script>
    // Additional page-specific scripts
    document.addEventListener('DOMContentLoaded', function() {
        console.log('SIMOPANG Landing Page Ready! 🚀');
        
        // Animate elements on load
        setTimeout(() => {
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                if (el.getBoundingClientRect().top < window.innerHeight) {
                    el.classList.add('opacity-100', 'translate-y-0');
                    el.classList.remove('opacity-0', 'translate-y-10');
                }
            });
        }, 100);
    });
</script>
@endpush
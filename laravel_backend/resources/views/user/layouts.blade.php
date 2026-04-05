{{--
  =====================================================
  SIMOPANG — User Layout
  File : resources/views/user/layouts.blade.php
  Desc : Layout utama untuk semua halaman USER
         (top navbar, bukan sidebar)
  =====================================================
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Dashboard') — SIMOPANG</title>

  {{-- Google Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500;700&display=swap" rel="stylesheet">

  {{-- User CSS --}}
  <link rel="stylesheet" href="{{ asset('css/dashboard-user.css') }}">

  {{-- Extra styles per halaman (opsional) --}}
  @stack('styles')
</head>
<body>

  {{-- ══ NAVBAR ══════════════════════════════════════ --}}
  <nav class="u-navbar">
    <div class="u-navbar__inner">

      {{-- Brand --}}
      <a class="u-navbar__brand" href="{{ route('user.home') }}">
        <div class="u-navbar__logo">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
        </div>
        <span class="u-navbar__name">SIMO<em>PANG</em></span>
      </a>

      {{-- Nav Links --}}
      <ul class="u-navbar__links">
        <li>
          <a href="{{ route('user.home') }}"
             class="{{ request()->routeIs('user.home') ? 'active' : '' }}">
            Dashboard
          </a>
        </li>
        <li>
          <a href="{{ route('user.harga') }}"
             class="{{ request()->routeIs('user.harga') ? 'active' : '' }}">
            Data Harga
          </a>
        </li>
        <li>
          <a href="{{ route('user.prediksi') }}"
             class="{{ request()->routeIs('user.prediksi') ? 'active' : '' }}">
            Prediksi
          </a>
        </li>
        <li>
          <a href="{{ route('user.simulasi') }}"
             class="{{ request()->routeIs('user.simulasi') ? 'active' : '' }}">
            Simulasi
          </a>
        </li>
        <li>
          <a href="{{ route('user.tentang') }}"
             class="{{ request()->routeIs('user.tentang') ? 'active' : '' }}">
            Tentang
          </a>
        </li>
      </ul>

      {{-- Navbar Right --}}
      <div class="u-navbar__right">
        <div class="u-update-badge">
          <span class="u-update-badge__dot"></span>
          Terakhir diperbarui: {{ $lastUpdated ?? '10 menit lalu' }}
        </div>
      </div>

    </div>
  </nav>

  {{-- ══ KONTEN HALAMAN ══════════════════════════════ --}}
  <div class="u-page">
    @yield('content')
  </div>

  {{-- ══ FOOTER ══════════════════════════════════════ --}}
  <footer class="u-footer">
    <div class="u-footer__inner">
      <span>© {{ date('Y') }} SIMOPANG. All rights reserved.</span>
      <div class="u-footer__links">
        <a href="#">Kebijakan Privasi</a>
        <a href="#">Syarat &amp; Ketentuan</a>
        <a href="#">Bantuan</a>
      </div>
    </div>
  </footer>

  {{-- Extra scripts per halaman (opsional) --}}
  @stack('scripts')

</body>
</html>
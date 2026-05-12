  <!DOCTYPE html>
  <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/LOGO-2.png') }}">
    <title>@yield('title', 'Dashboard') — SIMOPANG</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    @php
      use Illuminate\Support\Facades\Auth;

      $user     = Auth::user();
      $role     = $user->role ?? 'user';
      $isAdmin  = $role === 'admin';

      $namaUser  = $user->name ?? ($isAdmin ? 'Admin' : 'User');
      $emailUser = $user->email ?? '';
      $initials  = strtoupper(substr($namaUser, 0, 1));

      $avatarUrl = ($user && $user->avatar)
          ? asset('storage/' . $user->avatar)
          : null;
    @endphp

    <link rel="stylesheet" href="{{ asset('css/simopang.css') }}">
    @stack('styles')
  </head>
  <body>

  {{-- ═══════════ SIDEBAR ═══════════ --}}
  <aside class="sidebar" id="appSidebar">

  <div class="sidebar-logo">
      <div class="logo-badge">
          <div class="logo-icon"
              style="border-radius:12px; overflow:hidden; transition:transform 0.2s;"
              onmouseover="this.style.transform='scale(1.05)'"
              onmouseout="this.style.transform='scale(1)'">
              <img src="{{ asset('images/LOGO-2.png') }}"
                  alt="SIMOPANG"
                  style="width:100%; height:100%; object-fit:contain;">
          </div>
          <div>
              <div class="logo-title">SIMOPANG {{ $isAdmin ? 'Admin' : '' }}</div>
              <div class="logo-sub">Monitoring & Prediksi</div>
          </div>
      </div>
  </div>
    @if($isAdmin)
    {{-- ADMIN --}}
    <nav class="nav-section">
      <a href="/admin/dashboard" class="nav-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
        <i class="fas fa-gauge-high"></i> Overview
      </a>
      <a href="/admin/komoditas" class="nav-item {{ Request::is('admin/komoditas*') ? 'active' : '' }}">
        <i class="fas fa-boxes-stacked"></i> Kelola Komoditas
      </a>
      <a href="/admin/harga" class="nav-item {{ Request::is('admin/harga*') ? 'active' : '' }}">
        <i class="fas fa-tags"></i> Data Harga
      </a>
      <a href="/admin/prediksi" class="nav-item {{ Request::is('admin/prediksi*') ? 'active' : '' }}">
        <i class="fas fa-wand-magic-sparkles"></i> Generate Prediksi
      </a>
      <a href="/admin/api-status" class="nav-item {{ Request::is('admin/api-status*') ? 'active' : '' }}">
        <i class="fas fa-circle-nodes"></i> API Status
      </a>
    </nav>

    <nav class="nav-section bordered">
      <div class="nav-label">System</div>
      <a href="/admin/profile" class="nav-item {{ Request::is('admin/profile*') ? 'active' : '' }}">
        <i class="fas fa-circle-user"></i> Profile
      </a>
      <a href="#" class="nav-item nav-item--logout" onclick="confirmLogout(); return false;">
        <i class="fas fa-right-from-bracket"></i> Logout
      </a>
    </nav>

    @else
    {{-- USER --}}
    <nav class="nav-section">
      <div class="nav-label">Menu</div>
      <a href="{{ route('user.home') }}" class="nav-item {{ request()->routeIs('user.home') ? 'active' : '' }}">
        <i class="fas fa-gauge-high"></i> Dashboard
      </a>
      <a href="{{ route('user.prediksi') }}" class="nav-item {{ request()->routeIs('user.prediksi') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> Prediksi
      </a>
      <a href="{{ route('user.simulasi') }}" class="nav-item {{ request()->routeIs('user.simulasi') ? 'active' : '' }}">
        <i class="fas fa-calculator"></i> Simulasi
      </a>
      <a href="{{ route('user.chatai') }}" class="nav-item {{ request()->routeIs('user.chatai') ? 'active' : '' }}">
        <i class="fas fa-robot"></i> Tanya AI
      </a>
    </nav>

    <nav class="nav-section bordered">
      <div class="nav-label">Akun</div>
      <a href="{{ route('user.profil') }}" class="nav-item {{ request()->routeIs('user.profil') ? 'active' : '' }}">
        <i class="fas fa-circle-user"></i> Profil
      </a>
      <a href="#" class="nav-item nav-item--logout" onclick="confirmLogout(); return false;">
        <i class="fas fa-right-from-bracket"></i> Keluar
      </a>
    </nav>
    @endif

    {{-- FOOTER --}}
    <div class="sidebar-footer">
      <div class="avatar">
        @if($avatarUrl)
          <img src="{{ $avatarUrl }}" alt="{{ $namaUser }}"
              style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
        @else
          {{ $initials }}
        @endif
      </div>
      <div>
        <div class="footer-name">{{ $namaUser }}</div>
        <div class="footer-email">{{ $emailUser }}</div>
      </div>
    </div>

  </aside>

  {{-- MAIN --}}
  <div class="main" id="appMain">

    <header class="topbar">
      <div>
        <div class="topbar-title">@yield('page-title', config('app.name'))</div>
        <div class="topbar-sub">@yield('page-sub')</div>
      </div>

      <div class="topbar-right">
        <button id="dm-toggle" onclick="dmToggle()" title="Toggle Dark Mode">
          <span class="dm-icon" id="dm-icon">🌙</span>
          <span id="dm-label">Dark</span>
        </button>

        <div class="topbar-divider"></div>

        @if($isAdmin)
        <a href="/admin/profile">
        @else
        <a href="{{ route('user.profil') }}">
        @endif
          <div class="topbar-user">
            <div class="topbar-avatar">
              @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $namaUser }}"
                    style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
              @else
                {{ $initials }}
              @endif
            </div>
            <div class="topbar-user-info">
              <div class="topbar-user-name">{{ $namaUser }}</div>
              <div class="topbar-user-role">{{ $isAdmin ? 'Administrator' : 'Pengguna' }}</div>
            </div>
          </div>
        </a>
      </div>
    </header>

    <main class="content">
      @yield('content')
    </main>

  </div>

  {{-- LOGOUT FORM --}}
  <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none">
    @csrf
  </form>

  {{-- MODAL KONFIRMASI LOGOUT --}}
  <div class="modal-overlay" id="logoutModal" style="display:none">
    <div class="modal-backdrop" onclick="closeLogout()"></div>
    <div class="modal-box">
      <div class="modal-icon modal-icon--rose">
        <i class="fas fa-right-from-bracket"></i>
      </div>
      <div class="modal-title">
        {{ $isAdmin ? 'Keluar dari Admin Panel?' : 'Keluar dari SIMOPANG?' }}
      </div>
      <div class="modal-desc">
        Sesi kamu akan diakhiri dan kamu perlu login kembali untuk mengakses aplikasi.
      </div>
      <div class="modal-actions">
        <button class="modal-btn modal-btn--cancel" onclick="closeLogout()">
          <i class="fas fa-xmark"></i> Batal
        </button>
        <button class="modal-btn modal-btn--confirm" onclick="doLogout()">
          <i class="fas fa-right-from-bracket"></i> Ya, Keluar
        </button>
      </div>
    </div>
  </div>

  <script>
  // ── Logout Modal ──
  function confirmLogout() {
    const modal = document.getElementById('logoutModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeLogout() {
    const modal = document.getElementById('logoutModal');
    modal.classList.remove('show');
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }

  function doLogout() {
    document.getElementById('logout-form').submit();
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLogout();
  });

  // ── Dark Mode ──
  (function () {
    const KEY = 'simopang_dark';
    const root = document.documentElement;

    function applyDark(on) {
      root.classList.toggle('dark', on);
      const icon  = document.getElementById('dm-icon');
      const label = document.getElementById('dm-label');
      if (icon)  icon.textContent  = on ? '☀️' : '🌙';
      if (label) label.textContent = on ? 'Light' : 'Dark';
    }

    const saved = localStorage.getItem(KEY);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyDark(saved !== null ? saved === '1' : prefersDark);

    window.dmToggle = function () {
      const isDark = root.classList.toggle('dark');
      localStorage.setItem(KEY, isDark ? '1' : '0');
      const icon  = document.getElementById('dm-icon');
      const label = document.getElementById('dm-label');
      if (icon)  icon.textContent  = isDark ? '☀️' : '🌙';
      if (label) label.textContent = isDark ? 'Light' : 'Dark';
    };
  })();

   document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.u-alert--success, .u-alert--error');
    alerts.forEach(function (alert) {
      setTimeout(function () {
        alert.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(function () { alert.remove(); }, 600);
      }, 1000);
    });
  });

  </script>

  @stack('scripts')
  </body>
  </html>

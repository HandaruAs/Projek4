<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — SIMOPANG</title>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500;700&display=swap" rel="stylesheet">

  @php
    $role      = session('user')['role'] ?? session('role') ?? 'user';
    $isAdmin   = $role === 'admin';
    $userData  = session('user') ?? [];
    $namaUser  = $userData['nama'] ?? $userData['name'] ?? ($isAdmin ? 'Admin' : 'User');
    $emailUser = $userData['email'] ?? '';
    $initials  = strtoupper(substr($namaUser, 0, 1));
    $avatar    = $userData['avatar'] ?? null;
  @endphp

  <link rel="stylesheet" href="{{ asset('css/simopang.css') }}">
  @stack('styles')
</head>
<body>

  {{-- ═══════════ SIDEBAR ═══════════ --}}
  <aside class="sidebar" id="appSidebar">

    <div class="sidebar-logo">
      <div class="logo-badge">
        <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
        <div>
          <div class="logo-title">SIMOPANG {{ $isAdmin ? 'Admin' : '' }}</div>
          <div class="logo-sub">Monitoring & Prediksi</div>
        </div>
      </div>
    </div>

    @if($isAdmin)
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
    <nav class="nav-section">
      <div class="nav-label">Menu</div>
      <a href="{{ route('user.home') }}" class="nav-item {{ request()->routeIs('user.home') ? 'active' : '' }}">
        <i class="fas fa-gauge-high"></i> Dashboard
      </a>
      <a href="{{ route('user.harga') }}" class="nav-item {{ request()->routeIs('user.harga') ? 'active' : '' }}">
        <i class="fas fa-tags"></i> Data Harga
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

    {{-- Footer sidebar: foto atau inisial --}}
    <div class="sidebar-footer">
      <div class="avatar" style="overflow:hidden; padding:0;">
        @if($avatar)
          <img src="{{ $avatar }}" alt="{{ $initials }}"
               style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">
        @else
          <span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
            {{ $initials }}
          </span>
        @endif
      </div>
      <div>
        <div class="footer-name">{{ $namaUser }}</div>
        <div class="footer-email">{{ $emailUser }}</div>
      </div>
    </div>

  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  {{-- ═══════════ MAIN ═══════════ --}}
  <div class="main" id="appMain">

    <header class="topbar">
      <div>
        <div class="topbar-title">@yield('page-title', config('app.name'))</div>
        <div class="topbar-sub">@yield('page-sub')</div>
      </div>

      <div class="topbar-right">
        <span class="last-updated">
          <i class="fas fa-clock"></i>
          Last updated: Today, {{ now()->format('H:i') }} WIB
        </span>
        <button class="btn-refresh" onclick="window.location.reload()">
          <i class="fas fa-rotate-right"></i> Refresh
        </button>
        <div class="topbar-divider"></div>

        @if($isAdmin)
          <a href="/admin/profile" style="text-decoration:none">
            <div class="topbar-user">
              <div class="topbar-avatar" style="overflow:hidden;padding:0;">
                @if($avatar)
                  <img src="{{ $avatar }}" alt="{{ $initials }}"
                       style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">
                @else
                  <span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
                    {{ $initials }}
                  </span>
                @endif
              </div>
              <div class="topbar-user-info">
                <div class="topbar-user-name">{{ $namaUser }}</div>
                <div class="topbar-user-role">Administrator</div>
              </div>
              <i class="fas fa-chevron-down topbar-caret"></i>
            </div>
          </a>
        @else
          <a href="{{ route('user.profil') }}" style="text-decoration:none">
            <div class="topbar-user">
              <div class="topbar-avatar" style="overflow:hidden;padding:0;">
                @if($avatar)
                  <img src="{{ $avatar }}" alt="{{ $initials }}"
                       style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">
                @else
                  <span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
                    {{ $initials }}
                  </span>
                @endif
              </div>
              <div class="topbar-user-info">
                <div class="topbar-user-name">{{ $namaUser }}</div>
                <div class="topbar-user-role">Pengguna</div>
              </div>
              <i class="fas fa-chevron-down topbar-caret"></i>
            </div>
          </a>
        @endif
      </div>
    </header>

    <main class="content">
      @yield('content')
    </main>

  </div>

  {{-- Modal Logout --}}
  <form id="logout-form" method="POST"
        action="{{ $isAdmin ? '/logout' : route('logout') }}"
        style="display:none">@csrf</form>

  <div id="logoutModal" class="modal-overlay">
    <div class="modal-backdrop" onclick="closeLogout()"></div>
    <div class="modal-box">
      <div class="modal-icon modal-icon--rose">
        <i class="fas fa-right-from-bracket"></i>
      </div>
      <div class="modal-title">{{ $isAdmin ? 'Konfirmasi Logout' : 'Keluar dari SIMOPANG?' }}</div>
      <div class="modal-desc">
        {{ $isAdmin
          ? 'Apakah kamu yakin ingin keluar dari sesi ini?'
          : 'Sesi Anda akan diakhiri dan Anda perlu masuk kembali untuk mengakses dashboard.' }}
      </div>
      <div class="modal-actions">
        <button class="modal-btn modal-btn--cancel" onclick="closeLogout()">Batal</button>
        <button class="modal-btn modal-btn--confirm"
                onclick="document.getElementById('logout-form').submit()">
          <i class="fas fa-right-from-bracket"></i>
          {{ $isAdmin ? 'Ya, Logout' : 'Ya, Keluar' }}
        </button>
      </div>
    </div>
  </div>

  @stack('scripts')

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('appSidebar');
      const overlay = document.getElementById('sidebarOverlay');
      sidebar.classList.toggle('collapsed');
      overlay.classList.toggle('show');
    }
    function closeSidebar() {
      document.getElementById('appSidebar').classList.remove('collapsed');
      document.getElementById('sidebarOverlay').classList.remove('show');
    }
    function confirmLogout() {
      document.getElementById('logoutModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }
    function closeLogout() {
      document.getElementById('logoutModal').classList.remove('show');
      document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLogout(); });
  </script>

</body>
</html>
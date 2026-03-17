<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — SIMOPANG</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

{{-- ═══════════ SIDEBAR ═══════════ --}}
<aside class="sidebar">

    <div class="sidebar-logo">
        <div class="logo-badge">
            <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="logo-title">SIMOPANG Admin</div>
                <div class="logo-sub">Monitoring & Prediksi</div>
            </div>
        </div>
    </div>

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
    <a href="/admin/settings" class="nav-item {{ Request::is('admin/settings*') ? 'active' : '' }}">
        <i class="fas fa-gear"></i> Settings
    </a>
   <a href="#" class="nav-item" onclick="confirmLogout(); return false;">
    <i class="fas fa-right-from-bracket"></i> Logout
</a>
</nav>

    <div class="sidebar-footer">
        <div class="avatar">{{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}</div>
        <div>
            <div class="footer-name">{{ $user->name ?? 'Admin User' }}</div>
            <div class="footer-email">{{ $user->email ?? 'admin@simopang.id' }}</div>
        </div>
    </div>

</aside>

<form id="logout-form" method="POST" action="/logout" class="logout-form">@csrf</form>

{{-- ═══════════ MAIN ═══════════ --}}
<div class="main">

    <header class="topbar">
        <div>
            <div class="topbar-title">@yield('page-title')</div>
            <div class="topbar-sub">@yield('page-sub')</div>
        </div>
        <div class="topbar-right">
            <span class="last-updated">
                {{-- style="margin-right:4px" dipindah ke admin.css (.last-updated i) --}}
                <i class="fas fa-clock"></i>
                Last updated: Today, {{ now()->format('H:i') }} WIB
            </span>
            <button class="btn-refresh" onclick="window.location.reload()">
                <i class="fas fa-rotate-right"></i> Refresh
            </button>

            {{-- ── TAMBAHAN: info user yang login ── --}}
            <div class="topbar-divider"></div>
            <div class="topbar-user">
                <div class="topbar-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="topbar-user-info">
                    <div class="topbar-user-name">{{ auth()->user()->name ?? 'Admin User' }}</div>
                    <div class="topbar-user-role">Administrator</div>
                </div>
                <i class="fas fa-chevron-down topbar-caret"></i>
            </div>
            {{-- ── END TAMBAHAN ── --}}

        </div>
    </header>

    <main class="content">
        @yield('content')
    </main>

</div>


{{-- LOGOUT CONFIRMATION MODAL --}}
<div id="logoutModal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
    {{-- Backdrop --}}
    <div onclick="closeLogout()"
         style="position:absolute; inset:0; background:rgba(15,23,42,0.45); backdrop-filter:blur(4px)"></div>

    {{-- Dialog --}}
    <div style="position:relative; background:#fff; border-radius:16px; padding:32px 28px;
                width:100%; max-width:380px; box-shadow:0 20px 60px rgba(15,23,42,0.2);
                text-align:center; animation:fadeUp 0.25s ease both">

        {{-- Icon --}}
        <div style="width:56px; height:56px; border-radius:50%; background:#fef2f2;
                    display:flex; align-items:center; justify-content:center;
                    margin:0 auto 18px; font-size:22px; color:#ef4444">
            <i class="fas fa-right-from-bracket"></i>
        </div>

        <div style="font-size:17px; font-weight:700; color:#0f172a; margin-bottom:8px">
            Konfirmasi Logout
        </div>
        <div style="font-size:13px; color:#64748b; margin-bottom:28px; line-height:1.6">
            Apakah kamu yakin ingin keluar dari sesi ini?
        </div>

        <div style="display:flex; gap:10px;">
            <button onclick="closeLogout()"
                    style="flex:1; padding:10px; border-radius:9px; border:1.5px solid #e2e8f0;
                           background:#fff; font-size:13px; font-weight:600; color:#64748b;
                           cursor:pointer; font-family:inherit; transition:all 0.15s"
                    onmouseover="this.style.background='#f1f5f9'"
                    onmouseout="this.style.background='#fff'">
                Batal
            </button>
            <button onclick="document.getElementById('logout-form').submit()"
                    style="flex:1; padding:10px; border-radius:9px; border:none;
                           background:#ef4444; font-size:13px; font-weight:600; color:#fff;
                           cursor:pointer; font-family:inherit; transition:all 0.15s;
                           box-shadow:0 2px 8px rgba(239,68,68,0.3)"
                    onmouseover="this.style.background='#dc2626'"
                    onmouseout="this.style.background='#ef4444'">
                <i class="fas fa-right-from-bracket" style="margin-right:5px"></i> Ya, Logout
            </button>
        </div>
    </div>
</div>

<script>
    function confirmLogout() {
        const modal = document.getElementById('logoutModal');
        modal.style.display = 'flex';
    }
    function closeLogout() {
        const modal = document.getElementById('logoutModal');
        modal.style.display = 'none';
    }
    // Tutup dengan tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLogout();
    });
</script>

@stack('scripts')
</body>
</html>
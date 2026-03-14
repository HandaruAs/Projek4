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
    <a href="#" class="nav-item"
       onclick="document.getElementById('logout-form').submit(); return false;">
        <i class="fas fa-right-from-bracket"></i> Logout
    </a>
</nav>

    <nav class="nav-section bordered">
        <div class="nav-label">System</div>
        <a href="/admin/settings" class="nav-item {{ Request::is('admin/settings*') ? 'active' : '' }}">
            <i class="fas fa-gear"></i> Settings
        </a>
        <a href="#" class="nav-item"
           onclick="document.getElementById('logout-form').submit(); return false;">
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
                <i class="fas fa-clock" style="margin-right:4px"></i>
                Last updated: Today, {{ now()->format('H:i') }} WIB
            </span>
            <button class="btn-refresh" onclick="window.location.reload()">
                <i class="fas fa-rotate-right"></i> Refresh
            </button>
        </div>
    </header>

    <main class="content">
        @yield('content')
    </main>

</div>

@stack('scripts')
</body>
</html>
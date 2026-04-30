{{--
  =====================================================
  SIMOPANG — User Profil
  File : resources/views/user/profil.blade.php
  Desc : Halaman profil & pengaturan akun pengguna
  =====================================================
--}}
@extends('layouts.layout')

@section('title', 'Profil Saya')
@section('page-title', 'Profil')
@section('page-sub', 'Kelola informasi akun dan data pribadi Anda')

@section('content')

{{-- ALERT SUCCESS --}}
@if(session('success'))
<div class="u-alert u-alert--success" style="margin-bottom: 20px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- ALERT ERROR --}}
@if(session('error'))
<div class="u-alert u-alert--error" style="margin-bottom: 20px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    {{ session('error') }}
</div>
@endif

{{-- VALIDATION ERRORS --}}
@if($errors->any())
<div class="u-alert u-alert--error" style="margin-bottom: 20px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    {{ $errors->first() }}
</div>
@endif

{{-- PROFIL GRID --}}
<div class="u-profil-grid">

    {{-- LEFT: AVATAR & INFO --}}
    <div class="u-profil-left">

        {{-- Avatar Card --}}
        <div class="u-avatar-card">
            <div class="u-avatar-wrap">
                <div class="u-avatar-circle">
                    {{ strtoupper(substr($user['nama'] ?? session('user')['nama'] ?? 'U', 0, 1)) }}
                </div>
                <div class="u-avatar-ring"></div>
            </div>

            <div class="u-avatar-name">{{ $user['nama'] ?? session('user')['nama'] ?? 'Nama User' }}</div>
            <div class="u-avatar-email">{{ $user['email'] ?? session('user')['email'] ?? 'email@contoh.com' }}</div>

            <div class="u-avatar-badge">
                <div class="u-update-badge__dot" style="background:var(--emerald)"></div>
                Akun Aktif
            </div>

            <div class="u-avatar-meta">
                <div class="u-avatar-meta__item">
                    <span class="u-avatar-meta__label">Bergabung</span>
                    <span class="u-avatar-meta__val">
                        {{ isset($user['created_at'])
                            ? \Carbon\Carbon::parse($user['created_at'])->format('M Y')
                            : date('M Y') }}
                    </span>
                </div>
                <div class="u-avatar-meta__divider"></div>
                <div class="u-avatar-meta__item">
                    <span class="u-avatar-meta__label">Role</span>
                    <span class="u-avatar-meta__val">User</span>
                </div>
            </div>
        </div>

        {{-- Quick Info Card --}}
        <div class="u-quick-info-card">
            <div class="u-quick-info-card__title">Informasi Cepat</div>
            <ul class="u-quick-info-list">
                <li>
                    <div class="u-quick-info-list__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="u-quick-info-list__label">Nama Lengkap</div>
                        <div class="u-quick-info-list__val">{{ $user['nama'] ?? session('user')['nama'] ?? '—' }}</div>
                    </div>
                </li>
                <li>
                    <div class="u-quick-info-list__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <div>
                        <div class="u-quick-info-list__label">Email</div>
                        <div class="u-quick-info-list__val">{{ $user['email'] ?? session('user')['email'] ?? '—' }}</div>
                    </div>
                </li>
                <li>
                    <div class="u-quick-info-list__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.19 1.22 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-.54a2 2 0 012.11.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="u-quick-info-list__label">No. Telepon</div>
                        <div class="u-quick-info-list__val">{{ $user['telepon'] ?? '—' }}</div>
                    </div>
                </li>
                <li>
                    <div class="u-quick-info-list__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0116 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <div class="u-quick-info-list__label">Alamat</div>
                        <div class="u-quick-info-list__val">{{ $user['alamat'] ?? '—' }}</div>
                    </div>
                </li>
            </ul>
        </div>

    </div>
    {{-- / LEFT --}}

    {{-- RIGHT: FORM --}}
    <div class="u-profil-right">

        <form method="POST" action="{{ route('user.profil.update') }}">
            @csrf
            @method('PUT')

            <div class="u-profil-form-card">
                <div class="u-profil-form-card__header">
                    <div class="u-profil-form-card__title">Data Pribadi</div>
                    <div class="u-profil-form-card__sub">Informasi ini akan ditampilkan di profil Anda</div>
                </div>
                <div class="u-profil-form-card__body">

                    {{-- Nama --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="nama">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Nama Lengkap
                        </label>
                        <input type="text" id="nama" name="nama"
                               class="u-profil-input @error('nama') is-error @enderror"
                               value="{{ old('nama', $user['nama'] ?? session('user')['nama'] ?? '') }}"
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('nama')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="email">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Alamat Email
                        </label>
                        <input type="email" id="email" name="email"
                               class="u-profil-input @error('email') is-error @enderror"
                               value="{{ old('email', $user['email'] ?? session('user')['email'] ?? '') }}"
                               placeholder="nama@email.com"
                               required>
                        @error('email')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Telepon --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="telepon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.19 1.22 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-.54a2 2 0 012.11.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            No. Telepon
                            <span class="u-profil-field__optional">Opsional</span>
                        </label>
                        <input type="tel" id="telepon" name="telepon"
                               class="u-profil-input @error('telepon') is-error @enderror"
                               value="{{ old('telepon', $user['telepon'] ?? '') }}"
                               placeholder="08xx-xxxx-xxxx">
                        @error('telepon')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="alamat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0116 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Alamat Lengkap
                            <span class="u-profil-field__optional">Opsional</span>
                        </label>
                        <textarea id="alamat" name="alamat" rows="3"
                                  class="u-profil-input u-profil-textarea @error('alamat') is-error @enderror"
                                  placeholder="Jl. Contoh No. 1, Kota, Provinsi">{{ old('alamat', $user['alamat'] ?? '') }}</textarea>
                        @error('alamat')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="u-profil-form-card__footer">
                    <button type="submit" class="u-btn-simpan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                    <span class="u-profil-form-card__hint">
                        Terakhir diperbarui:
                        {{ isset($user['updated_at'])
                            ? \Carbon\Carbon::parse($user['updated_at'])->diffForHumans()
                            : 'belum pernah' }}
                    </span>
                </div>
            </div>

        </form>

    </div>
    {{-- / RIGHT --}}

</div>

@endsection
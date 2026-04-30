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
                <div class="u-avatar-circle" id="avatarCircle" onclick="openAvatarModal()" style="cursor:pointer">
                    @if($user->avatar)
                        <img id="avatarPreview" src="{{ asset('storage/' . $user->avatar) }}"
                             style="width:100%; height:100%; object-fit:cover; border-radius:50%">
                    @else
                        {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                    @endif
                </div>
                <div class="u-avatar-ring"></div>
            </div>

            <div class="u-avatar-name">{{ $user->name ?? 'Admin User' }}</div>
            <div class="u-avatar-email">{{ $user->email ?? '' }}</div>

            <div class="u-avatar-badge">
                <div class="u-update-badge__dot" style="background:var(--emerald)"></div>
                Akun Aktif
            </div>

            <div class="u-avatar-meta">
                <div class="u-avatar-meta__item">
                    <span class="u-avatar-meta__label">Role</span>
                    <span class="u-avatar-meta__val">{{ ucfirst($user->role ?? 'admin') }}</span>
                </div>
                <div class="u-avatar-meta__divider"></div>
                <div class="u-avatar-meta__item">
                    <span class="u-avatar-meta__label">Bergabung</span>
                    <span class="u-avatar-meta__val">
                        {{ isset($user->created_at) ? \Carbon\Carbon::parse($user->created_at)->format('M Y') : date('M Y') }}
                    </span>
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
                        <div class="u-quick-info-list__val">{{ $user->name ?? '—' }}</div>
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
                        <div class="u-quick-info-list__val">{{ $user->email ?? '—' }}</div>
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
                        <div class="u-quick-info-list__val">{{ $user->phone ?? '—' }}</div>
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
                        <div class="u-quick-info-list__val">{{ $user->address ?? '—' }}</div>
                    </div>
                </li>
            </ul>
        </div>

    </div>
    {{-- / LEFT --}}

    {{-- RIGHT: FORM --}}
    <div class="u-profil-right">

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="u-profil-form-card">
                <div class="u-profil-form-card__header">
                    <div class="u-profil-form-card__title">Data Pribadi</div>
                    <div class="u-profil-form-card__sub">Informasi ini akan ditampilkan di profil Anda</div>
                </div>
                <div class="u-profil-form-card__body">

                    {{-- Avatar Upload --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="avatar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                                <path d="M12 3v4m0 12v4"/>
                            </svg>
                            Foto Profil
                            <span class="u-profil-field__optional">Opsional</span>
                        </label>
                        <input type="file" id="avatarInput" name="avatar"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="u-profil-input">
                        <span class="u-profil-field__hint">Max 2MB. Format: JPG, PNG, WEBP</span>
                    </div>

                    {{-- Nama --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="name">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Nama Lengkap
                        </label>
                        <input type="text" id="name" name="name"
                               class="u-profil-input @error('name') is-error @enderror"
                               value="{{ old('name', $user->name ?? '') }}"
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('name')
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
                               value="{{ old('email', $user->email ?? '') }}"
                               placeholder="admin@email.com"
                               required>
                        @error('email')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Telepon --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="phone">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.19 1.22 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-.54a2 2 0 012.11.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            No. Telepon
                            <span class="u-profil-field__optional">Opsional</span>
                        </label>
                        <input type="tel" id="phone" name="phone"
                               class="u-profil-input @error('phone') is-error @enderror"
                               value="{{ old('phone', $user->phone ?? '') }}"
                               placeholder="08xx-xxxx-xxxx">
                        @error('phone')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="address">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0116 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Alamat Lengkap
                            <span class="u-profil-field__optional">Opsional</span>
                        </label>
                        <textarea id="address" name="address" rows="3"
                                  class="u-profil-input u-profil-textarea @error('address') is-error @enderror"
                                  placeholder="Jl. Contoh No. 1, Kota, Provinsi">{{ old('address', $user->address ?? '') }}</textarea>
                        @error('address')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Role (disabled) --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="role">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Role
                        </label>
                        <input type="text" id="role" name="role"
                               class="u-profil-input"
                               value="{{ ucfirst($user->role ?? 'admin') }}"
                               disabled
                               style="cursor:not-allowed; opacity:0.7">
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
                        {{ isset($user->updated_at) ? \Carbon\Carbon::parse($user->updated_at)->diffForHumans() : 'belum pernah' }}
                    </span>
                </div>
            </div>

        </form>

        {{-- Form Change Password --}}
        <form method="POST" action="{{ route('profile.password') ?? '#' }}" style="margin-top:16px">
            @csrf
            @method('PUT')

            <div class="u-profil-form-card">
                <div class="u-profil-form-card__header">
                    <div class="u-profil-form-card__title">Ganti Password</div>
                    <div class="u-profil-form-card__sub">Pastikan password Anda kuat dan aman</div>
                </div>
                <div class="u-profil-form-card__body">

                    {{-- Password Lama --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="current_password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            Password Saat Ini
                        </label>
                        <div class="u-profil-input-wrap">
                            <input type="password" id="current_password" name="current_password"
                                   class="u-profil-input @error('current_password') is-error @enderror"
                                   placeholder="••••••••">
                            <button type="button" class="u-profil-eye" onclick="togglePassword('current_password')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password Baru --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            Password Baru
                        </label>
                        <div class="u-profil-input-wrap">
                            <input type="password" id="password" name="password"
                                   class="u-profil-input @error('password') is-error @enderror"
                                   placeholder="••••••••">
                            <button type="button" class="u-profil-eye" onclick="togglePassword('password')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div id="passwordStrength" class="u-strength-wrap" style="display:none">
                            <div class="u-strength-bar"><div class="u-strength-bar__fill" id="strengthFill"></div></div>
                            <span id="strengthText" class="u-strength-label">Lemah</span>
                        </div>
                        @error('password')
                            <div class="u-profil-field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="u-profil-field">
                        <label class="u-profil-field__label" for="password_confirmation">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                                <line x1="12" y1="15" x2="12" y2="17"/>
                            </svg>
                            Konfirmasi Password Baru
                        </label>
                        <div class="u-profil-input-wrap">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="u-profil-input"
                                   placeholder="••••••••">
                            <button type="button" class="u-profil-eye" onclick="togglePassword('password_confirmation')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div id="matchMsg" class="u-profil-field__match" style="display:none"></div>
                    </div>

                    <div class="u-pass-tips">
                        <div class="u-pass-tips__title">Tips Password Kuat:</div>
                        <ul>
                            <li>Minimal 8 karakter</li>
                            <li>Mengandung huruf besar dan kecil</li>
                            <li>Mengandung angka</li>
                            <li>Mengandung simbol (@$!%*?&)</li>
                        </ul>
                    </div>

                </div>
                <div class="u-profil-form-card__footer">
                    <button type="submit" class="u-btn-simpan u-btn-simpan--rose">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        Update Password
                    </button>
                </div>
            </div>
        </form>

    </div>
    {{-- / RIGHT --}}

</div>

{{-- MODAL PREVIEW AVATAR --}}
<div id="avatarModal" onclick="closeAvatarModal()"
     style="display:none; position:fixed; inset:0; z-index:9999;
            background:rgba(15,23,42,0.7); backdrop-filter:blur(6px);
            align-items:center; justify-content:center; cursor:zoom-out">
    <div onclick="event.stopPropagation()"
         style="position:relative; max-width:420px; width:90%">
        <img id="avatarModalImg" src=""
             style="width:100%; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.4); display:block">
        <button onclick="closeAvatarModal()"
                style="position:absolute; top:-14px; right:-14px; width:32px; height:32px;
                       border-radius:50%; border:none; background:#fff; cursor:pointer;
                       font-size:14px; color:#64748b; box-shadow:0 2px 8px rgba(0,0,0,0.15);
                       display:flex; align-items:center; justify-content:center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
</div>

<script>
// Preview avatar sebelum upload
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(ev) {
        const circle = document.getElementById('avatarCircle');
        circle.innerHTML = `<img src="${ev.target.result}"
            style="width:100%; height:100%; object-fit:cover; border-radius:50%">`;
        document.getElementById('avatarModalImg').src = ev.target.result;
    };
    reader.readAsDataURL(file);
});

// Buka modal lihat foto
function openAvatarModal() {
    const img = document.querySelector('#avatarCircle img');
    if (!img) return;

    document.getElementById('avatarModalImg').src = img.src;
    const modal = document.getElementById('avatarModal');
    modal.style.display = 'flex';
}

// Tutup modal
function closeAvatarModal() {
    document.getElementById('avatarModal').style.display = 'none';
}

// Tutup dengan Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAvatarModal();
});

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}

// Password strength checker
const passwordInput = document.getElementById('password');
const strengthDiv = document.getElementById('passwordStrength');
const strengthFill = document.getElementById('strengthFill');
const strengthText = document.getElementById('strengthText');

if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        const val = this.value;
        if (val.length === 0) {
            strengthDiv.style.display = 'none';
            return;
        }
        strengthDiv.style.display = 'flex';

        let strength = 0;
        if (val.length >= 8) strength++;
        if (val.match(/[a-z]/) && val.match(/[A-Z]/)) strength++;
        if (val.match(/[0-9]/)) strength++;
        if (val.match(/[^a-zA-Z0-9]/)) strength++;

        let width = '25%';
        let color = '#ef4444';
        let text = 'Lemah';

        if (strength >= 4) {
            width = '100%';
            color = '#10b981';
            text = 'Kuat';
        } else if (strength === 3) {
            width = '75%';
            color = '#f59e0b';
            text = 'Sedang';
        } else if (strength === 2) {
            width = '50%';
            color = '#f59e0b';
            text = 'Sedang';
        } else if (strength === 1) {
            width = '25%';
            color = '#ef4444';
            text = 'Lemah';
        }

        strengthFill.style.width = width;
        strengthFill.style.background = color;
        strengthText.textContent = text;
        strengthText.style.color = color;
    });
}

// Password match checker
const confirmInput = document.getElementById('password_confirmation');
const matchMsg = document.getElementById('matchMsg');

if (passwordInput && confirmInput) {
    function checkMatch() {
        if (confirmInput.value.length === 0) {
            matchMsg.style.display = 'none';
            return;
        }
        matchMsg.style.display = 'block';
        if (passwordInput.value === confirmInput.value) {
            matchMsg.innerHTML = '✓ Password cocok';
            matchMsg.className = 'u-profil-field__match u-profil-field__match--ok';
        } else {
            matchMsg.innerHTML = '✗ Password tidak cocok';
            matchMsg.className = 'u-profil-field__match u-profil-field__match--err';
        }
    }
    passwordInput.addEventListener('input', checkMatch);
    confirmInput.addEventListener('input', checkMatch);
}
</script>

@endsection
{{--
  =====================================================
  SIMOPANG — User Profil
  File : resources/views/user/profil.blade.php
  Desc : Halaman profil & pengaturan akun pengguna
  =====================================================
--}}
@extends('user.layouts')

@section('title', 'Profil Saya')

@section('content')

  {{-- ── BREADCRUMB ─────────────────────────────────── --}}
  <nav class="u-breadcrumb">
    <a href="{{ route('user.home') }}">Beranda</a>
    <span class="u-breadcrumb__sep">/</span>
    <span class="u-breadcrumb__current">Profil Saya</span>
  </nav>

  {{-- ── PAGE HEADER ───────────────────────────────── --}}
  <div class="u-page-header">
    <div>
      <h1>Profil Saya</h1>
      <p>Kelola informasi akun dan data pribadi Anda.</p>
    </div>
  </div>

  {{-- ── ALERT SUCCESS ──────────────────────────────── --}}
  @if(session('success'))
  <div class="u-alert u-alert--success">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
      <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    {{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div class="u-alert u-alert--error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/>
      <line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    {{ session('error') }}
  </div>
  @endif

  {{-- ── PROFIL GRID ─────────────────────────────────── --}}
  <div class="u-profil-grid">

    {{-- ── LEFT: AVATAR & INFO ───────────────────────── --}}
    <div class="u-profil-left">

      {{-- Avatar Card --}}
      <div class="u-avatar-card">
        <div class="u-avatar-wrap">
          <div class="u-avatar">
            {{ strtoupper(substr(session('user')['nama'] ?? 'U', 0, 1)) }}
          </div>
          <div class="u-avatar-ring"></div>
        </div>

        <div class="u-avatar-name">{{ session('user')['nama'] ?? 'Nama User' }}</div>
        <div class="u-avatar-email">{{ session('user')['email'] ?? 'email@contoh.com' }}</div>

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
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

    {{-- ── RIGHT: FORM TABS ──────────────────────────── --}}
    <div class="u-profil-right">

      {{-- Tab Header --}}
      <div class="u-profil-tabs">
        <button class="u-profil-tab active" onclick="switchTab(this, 'tab-info')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          Informasi Akun
        </button>
        <button class="u-profil-tab" onclick="switchTab(this, 'tab-password')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
          Ubah Password
        </button>
      </div>

      {{-- ── TAB: INFORMASI AKUN ──────────────────────── --}}
      <div class="u-profil-tab-panel active" id="tab-info">
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
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

              {{-- Telepon & Alamat (2 kolom) --}}
              <div class="u-profil-row">

                <div class="u-profil-field">
                  <label class="u-profil-field__label" for="telepon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

              </div>

              {{-- Alamat --}}
              <div class="u-profil-field">
                <label class="u-profil-field__label" for="alamat">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
      {{-- / TAB INFO --}}

      {{-- ── TAB: UBAH PASSWORD ────────────────────────── --}}
      <div class="u-profil-tab-panel" id="tab-password">
        <form method="POST" action="{{ route('user.profil.password') }}">
          @csrf
          @method('PUT')

          <div class="u-profil-form-card">
            <div class="u-profil-form-card__header">
              <div class="u-profil-form-card__title">Ubah Password</div>
              <div class="u-profil-form-card__sub">Gunakan password yang kuat dan unik</div>
            </div>
            <div class="u-profil-form-card__body">

              {{-- Password Lama --}}
              <div class="u-profil-field">
                <label class="u-profil-field__label" for="password_lama">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                  </svg>
                  Password Saat Ini
                </label>
                <div class="u-profil-input-wrap">
                  <input type="password" id="password_lama" name="password_lama"
                         class="u-profil-input @error('password_lama') is-error @enderror"
                         placeholder="Masukkan password lama"
                         required>
                  <button type="button" class="u-profil-eye" onclick="togglePass('password_lama', this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </div>
                @error('password_lama')
                  <div class="u-profil-field__error">{{ $message }}</div>
                @enderror
              </div>

              {{-- Password Baru --}}
              <div class="u-profil-field">
                <label class="u-profil-field__label" for="password_baru">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                  </svg>
                  Password Baru
                </label>
                <div class="u-profil-input-wrap">
                  <input type="password" id="password_baru" name="password_baru"
                         class="u-profil-input @error('password_baru') is-error @enderror"
                         placeholder="Minimal 8 karakter"
                         minlength="8"
                         oninput="checkStrength(this)"
                         required>
                  <button type="button" class="u-profil-eye" onclick="togglePass('password_baru', this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </div>
                {{-- Password strength --}}
                <div class="u-strength-wrap" id="strength-wrap" style="display:none">
                  <div class="u-strength-bar">
                    <div class="u-strength-bar__fill" id="strength-fill"></div>
                  </div>
                  <span class="u-strength-label" id="strength-label"></span>
                </div>
                @error('password_baru')
                  <div class="u-profil-field__error">{{ $message }}</div>
                @enderror
              </div>

              {{-- Konfirmasi Password --}}
              <div class="u-profil-field">
                <label class="u-profil-field__label" for="password_konfirmasi">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                  </svg>
                  Konfirmasi Password Baru
                </label>
                <div class="u-profil-input-wrap">
                  <input type="password" id="password_konfirmasi" name="password_baru_confirmation"
                         class="u-profil-input @error('password_baru_confirmation') is-error @enderror"
                         placeholder="Ulangi password baru"
                         oninput="checkMatch()"
                         required>
                  <button type="button" class="u-profil-eye" onclick="togglePass('password_konfirmasi', this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </div>
                <div class="u-profil-field__match" id="match-msg" style="display:none"></div>
                @error('password_baru_confirmation')
                  <div class="u-profil-field__error">{{ $message }}</div>
                @enderror
              </div>

              {{-- Tips --}}
              <div class="u-pass-tips">
                <div class="u-pass-tips__title">Tips Password Aman:</div>
                <ul>
                  <li>Minimal 8 karakter</li>
                  <li>Kombinasi huruf besar, kecil, dan angka</li>
                  <li>Tambahkan simbol (!@#$%) untuk lebih kuat</li>
                </ul>
              </div>

            </div>
            <div class="u-profil-form-card__footer">
              <button type="submit" class="u-btn-simpan u-btn-simpan--rose">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                Ubah Password
              </button>
            </div>
          </div>

        </form>
      </div>
      {{-- / TAB PASSWORD --}}

    </div>
    {{-- / RIGHT --}}

  </div>
  {{-- / profil grid --}}

@endsection

@push('scripts')
<script>
function switchTab(btn, targetId) {
  document.querySelectorAll('.u-profil-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.u-profil-tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(targetId).classList.add('active');
}

function togglePass(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.classList.toggle('active', !isText);
}

function checkStrength(input) {
  const val  = input.value;
  const wrap  = document.getElementById('strength-wrap');
  const fill  = document.getElementById('strength-fill');
  const label = document.getElementById('strength-label');

  if (val.length === 0) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'flex';

  let score = 0;
  if (val.length >= 8)               score++;
  if (/[A-Z]/.test(val))             score++;
  if (/[0-9]/.test(val))             score++;
  if (/[^A-Za-z0-9]/.test(val))      score++;

  const levels = [
    { w: '25%',  bg: '#f43f5e', text: 'Lemah' },
    { w: '50%',  bg: '#f59e0b', text: 'Cukup' },
    { w: '75%',  bg: '#0ea5e9', text: 'Baik' },
    { w: '100%', bg: '#10b981', text: 'Kuat' },
  ];

  const lvl = levels[score - 1] || levels[0];
  fill.style.width      = lvl.w;
  fill.style.background = lvl.bg;
  label.textContent     = lvl.text;
  label.style.color     = lvl.bg;
}

function checkMatch() {
  const baru    = document.getElementById('password_baru').value;
  const konfirm = document.getElementById('password_konfirmasi').value;
  const msg     = document.getElementById('match-msg');

  if (konfirm.length === 0) { msg.style.display = 'none'; return; }
  msg.style.display = 'flex';

  if (baru === konfirm) {
    msg.textContent = '✓ Password cocok';
    msg.className   = 'u-profil-field__match u-profil-field__match--ok';
  } else {
    msg.textContent = '✗ Password tidak cocok';
    msg.className   = 'u-profil-field__match u-profil-field__match--err';
  }
}
</script>
@endpush
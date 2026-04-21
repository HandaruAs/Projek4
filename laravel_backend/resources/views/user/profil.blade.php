@extends('layouts.layout')

@section('title', 'Profil Saya')

@section('content')

  <nav class="u-breadcrumb">
    <a href="{{ route('user.home') }}">Beranda</a>
    <span class="u-breadcrumb__sep">/</span>
    <span class="u-breadcrumb__current">Profil Saya</span>
  </nav>

  <div class="u-page-header">
    <div>
      <h1>Profil Saya</h1>
      <p>Kelola informasi akun dan data pribadi Anda.</p>
    </div>
  </div>

  @if(session('success'))
    <div class="u-alert u-alert--success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="u-alert u-alert--error">{{ session('error') }}</div>
  @endif

  @php
    $avatar = $user['avatar'] ?? session('user')['avatar'] ?? null;
    $nama   = $user['name']  ?? session('user')['name']  ?? ($user['nama'] ?? session('user')['nama'] ?? 'U');
    $email  = $user['email'] ?? session('user')['email'] ?? '';
    $inisial = strtoupper(substr($nama, 0, 1));
  @endphp

  <div class="u-profil-grid">

    {{-- ── LEFT ── --}}
    <div class="u-profil-left">

      {{-- Avatar Card --}}
      <div class="u-avatar-card">

        {{-- Lingkaran foto --}}
        <div class="u-avatar-wrap" style="cursor:pointer;" onclick="document.getElementById('avatarInput').click()">
          <div class="u-avatar" id="avatarCircle">
            @if($avatar)
              <img id="avatarImg" src="{{ $avatar }}" alt="Avatar"
                   style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">
              <span id="avatarInitial" style="display:none;">{{ $inisial }}</span>
            @else
              <img id="avatarImg" src="" alt="Avatar"
                   style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:none;">
              <span id="avatarInitial" class="u-avatar-initial--active">{{ $inisial }}</span>
            @endif
          </div>
          <div class="u-avatar-ring"></div>
          {{-- Badge kamera --}}
          <div class="u-avatar-cam-btn" title="Ganti foto profil">
            <i class="fas fa-camera"></i>
          </div>
        </div>

        <div class="u-avatar-name">{{ $nama }}</div>
        <div class="u-avatar-email">{{ $email }}</div>

        <div class="u-avatar-badge">
          <div class="u-update-badge__dot" style="background:var(--emerald)"></div>
          Akun Aktif
        </div>

        {{-- Tombol ganti & hapus foto --}}
        <div class="u-avatar-photo-btns">
          <button type="button" class="u-avatar-change-btn"
                  onclick="document.getElementById('avatarInput').click()">
            <i class="fas fa-camera"></i> Ganti Foto
          </button>
          <button type="button" class="u-avatar-remove-btn" id="removeBtn"
                  style="{{ $avatar ? '' : 'display:none;' }}"
                  onclick="removeAvatar()">
            <i class="fas fa-trash-can"></i> Hapus Foto
          </button>
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
            <span class="u-avatar-meta__val">{{ ucfirst($user['role'] ?? 'user') }}</span>
          </div>
        </div>
      </div>

      {{-- Informasi Akun --}}
      <div class="u-info-card">
        <div class="u-info-card__header">
          <i class="fas fa-circle-info u-info-card__icon"></i>
          <div class="u-info-card__title">Informasi Akun</div>
        </div>
        <div class="u-info-list">
          <div class="u-info-item">
            <span class="u-info-label">
              <i class="fas fa-user u-info-label__icon"></i>
              Nama Lengkap
            </span>
            <span class="u-info-value">{{ $nama ?: '—' }}</span>
          </div>
          <div class="u-info-item">
            <span class="u-info-label">
              <i class="fas fa-envelope u-info-label__icon"></i>
              Email
            </span>
            <span class="u-info-value">{{ $email ?: '—' }}</span>
          </div>
          <div class="u-info-item">
            <span class="u-info-label">
              <i class="fas fa-phone u-info-label__icon"></i>
              No. Telepon
            </span>
            <span class="u-info-value">{{ $user['phone'] ?? session('user')['phone'] ?? ($user['telepon'] ?? '—') }}</span>
          </div>
          <div class="u-info-item">
            <span class="u-info-label">
              <i class="fas fa-location-dot u-info-label__icon"></i>
              Alamat
            </span>
            <span class="u-info-value">{{ $user['address'] ?? session('user')['address'] ?? ($user['alamat'] ?? '—') }}</span>
          </div>
          <div class="u-info-item">
            <span class="u-info-label">
              <i class="fas fa-shield u-info-label__icon"></i>
              Role
            </span>
            <span class="u-info-value u-info-value--badge">{{ ucfirst($user['role'] ?? 'user') }}</span>
          </div>
        </div>
      </div>

    </div>
    {{-- / LEFT --}}

    {{-- ── RIGHT ── --}}
    <div class="u-profil-right">

      <form method="POST" action="{{ route('user.profil.update') }}">
        @csrf
        @method('PUT')

        {{-- Input file hanya untuk trigger picker, TIDAK disubmit --}}
        <input type="file" id="avatarInput"
               accept="image/jpg,image/jpeg,image/png,image/webp"
               style="display:none;"
               onchange="previewAvatar(event)">

        {{-- Base64 foto yang sesungguhnya dikirim ke controller --}}
        <input type="hidden" id="avatarBase64" name="avatar_base64" value="">

        {{-- Flag hapus avatar --}}
        <input type="hidden" id="removeAvatarFlag" name="remove_avatar" value="0">

        <div class="u-profil-form-card">
          <div class="u-profil-form-card__header">
            <div class="u-profil-form-card__title">Edit Data Pribadi</div>
            <div class="u-profil-form-card__sub">Perubahan akan disimpan ke akun Anda</div>
          </div>

          <div class="u-profil-form-card__body">

            <div class="u-profil-field">
              <label class="u-profil-field__label" for="nama">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Nama Lengkap
              </label>
              <input type="text" id="nama" name="nama"
                     class="u-profil-input @error('nama') is-error @enderror"
                     value="{{ old('nama', $nama) }}"
                     placeholder="Masukkan nama lengkap" required>
              @error('nama')<div class="u-profil-field__error">{{ $message }}</div>@enderror
            </div>

            <div class="u-profil-field">
              <label class="u-profil-field__label" for="email">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Alamat Email
              </label>
              <input type="email" id="email" name="email"
                     class="u-profil-input @error('email') is-error @enderror"
                     value="{{ old('email', $email) }}"
                     placeholder="nama@email.com" required>
              @error('email')<div class="u-profil-field__error">{{ $message }}</div>@enderror
            </div>

            <div class="u-profil-field">
              <label class="u-profil-field__label" for="telepon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.19 1.22 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-.54a2 2 0 012.11.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                No. Telepon
                <span class="u-profil-field__optional">Opsional</span>
              </label>
              <input type="tel" id="telepon" name="telepon"
                     class="u-profil-input @error('telepon') is-error @enderror"
                     value="{{ old('telepon', $user['phone'] ?? session('user')['phone'] ?? ($user['telepon'] ?? '')) }}"
                     placeholder="08xx-xxxx-xxxx">
              @error('telepon')<div class="u-profil-field__error">{{ $message }}</div>@enderror
            </div>

            <div class="u-profil-field">
              <label class="u-profil-field__label" for="alamat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Alamat Lengkap
                <span class="u-profil-field__optional">Opsional</span>
              </label>
              <textarea id="alamat" name="alamat" rows="3"
                        class="u-profil-input u-profil-textarea @error('alamat') is-error @enderror"
                        placeholder="Jl. Contoh No. 1, Kota, Provinsi">{{ old('alamat', $user['address'] ?? session('user')['address'] ?? ($user['alamat'] ?? '')) }}</textarea>
              @error('alamat')<div class="u-profil-field__error">{{ $message }}</div>@enderror
            </div>

          </div>

          <div class="u-profil-form-card__footer">
            <button type="submit" class="u-btn-simpan">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;">
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

@push('scripts')
<script>
  function previewAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
      alert('Ukuran foto maksimal 2MB');
      event.target.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      const base64 = e.target.result; // format: data:image/jpeg;base64,/9j/...

      // Tampilkan preview
      const img     = document.getElementById('avatarImg');
      const initial = document.getElementById('avatarInitial');
      img.src = base64;
      img.style.display = 'block';
      if (initial) {
        initial.style.display = 'none';
        initial.classList.remove('u-avatar-initial--active');
      }

      // Simpan base64 ke hidden input → yang ini dikirim ke controller
      document.getElementById('avatarBase64').value = base64;

      document.getElementById('removeBtn').style.display = 'inline-flex';
      document.getElementById('removeAvatarFlag').value = '0';
    };
    reader.readAsDataURL(file);
  }

  function removeAvatar() {
    const img     = document.getElementById('avatarImg');
    const initial = document.getElementById('avatarInitial');
    img.src = '';
    img.style.display = 'none';
    if (initial) {
      initial.style.display = 'flex';
      initial.classList.add('u-avatar-initial--active');
    }
    // Kosongkan base64 dan file input
    document.getElementById('avatarBase64').value = '';
    document.getElementById('avatarInput').value = '';
    document.getElementById('removeAvatarFlag').value = '1';
    document.getElementById('removeBtn').style.display = 'none';
  }
</script>
@endpush

@push('styles')
<style>
  /* ══════════════════════════════════════
     AVATAR
  ══════════════════════════════════════ */
  .u-avatar {
    width: 88px !important;
    height: 88px !important;
    min-width: 88px;
    min-height: 88px;
    border-radius: 50% !important;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    /* default: border saat belum ada foto */
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
  }

  .u-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 50% !important;
    display: block;
  }

  /* Inisial saat belum upload foto — ungu seperti navbar */
  #avatarInitial {
    display: none;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 32px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1;
    border-radius: 50%;
    letter-spacing: 0;
  }
  #avatarInitial.u-avatar-initial--active {
    display: flex;
    background: #5a5aff; /* ungu sama persis dengan navbar */
  }

  /* Saat avatar ada foto, pastikan background tidak ungu */
  .u-avatar:has(img[src]:not([src=""])) {
    background: transparent;
    border-color: transparent;
  }

  .u-avatar-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 88px;
    height: 88px;
  }

  .u-avatar-cam-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #5a5aff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 11px;
    border: 2px solid #fff;
    pointer-events: none;
    z-index: 2;
  }

  /* ══════════════════════════════════════
     TOMBOL FOTO
  ══════════════════════════════════════ */
  .u-avatar-change-btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
    padding: 9px 16px;
    border-radius: 8px;
    border: 1.5px solid #d1d5db;
    background: #ffffff;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, border-color .15s;
  }
  .u-avatar-change-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
  }
  .u-avatar-change-btn i { font-size: 13px; color: #6b7280; }

  .u-avatar-remove-btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
    padding: 9px 16px;
    border-radius: 8px;
    border: 1.5px solid #fca5a5;
    background: #fff5f5;
    color: #dc2626;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, border-color .15s;
  }
  .u-avatar-remove-btn:hover {
    background: #fee2e2;
    border-color: #f87171;
  }
  .u-avatar-remove-btn i { font-size: 13px; }

  .u-avatar-photo-btns {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    margin-top: 4px;
  }

  /* ══════════════════════════════════════
     INFO CARD — dipercantik dengan card
  ══════════════════════════════════════ */
  .u-info-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
  }

  .u-info-card__header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 18px;
    border-bottom: 1px solid #f0f0f5;
    background: #fafafa;
  }

  .u-info-card__icon {
    color: #5a5aff;
    font-size: 14px;
  }

  .u-info-card__title {
    font-size: 13.5px;
    font-weight: 700;
    color: #111827;
    letter-spacing: .01em;
  }

  .u-info-list {
    display: flex;
    flex-direction: column;
  }

  .u-info-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 11px 18px;
    border-bottom: 1px solid #f3f4f6;
    transition: background .12s;
  }
  .u-info-item:last-child {
    border-bottom: none;
  }
  .u-info-item:hover {
    background: #f9f9ff;
  }

  .u-info-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .05em;
  }

  .u-info-label__icon {
    font-size: 10px;
    color: #5a5aff;
    opacity: .8;
  }

  .u-info-value {
    font-size: 13px;
    font-weight: 500;
    color: #1f2937;
    word-break: break-word;
  }

  /* Badge khusus untuk role */
  .u-info-value--badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 10px;
    border-radius: 20px;
    background: #eeecff;
    color: #5a5aff;
    font-size: 12px;
    font-weight: 600;
    width: fit-content;
  }
</style>
@endpush
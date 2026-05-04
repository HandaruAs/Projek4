@extends('layouts.layout')

@section('title', 'Profil Admin')
@section('page-title', 'Profil Saya')
@section('page-sub', 'Kelola informasi akun dan data pribadi Anda.')

@section('content')

  {{-- ── BREADCRUMB ─────────────────────────────────── --}}
  <nav class="u-breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <span class="u-breadcrumb__sep">/</span>
    <span class="u-breadcrumb__current">Profil Saya</span>
  </nav>

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

  @if($errors->any())
  <div class="u-alert u-alert--error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/>
      <line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    {{ $errors->first() }}
  </div>
  @endif

  {{-- ── PROFIL GRID ─────────────────────────────────── --}}
  <div class="u-profil-grid">

    {{-- ── LEFT: AVATAR & INFO ───────────────────────── --}}
    <div class="u-profil-left">

      {{-- Avatar Card --}}
      <div class="u-avatar-card">
        <div class="u-avatar-wrap">
          <div class="u-avatar"
               id="avatarWrapper"
               onclick="openAvatarModal()"
               title="Klik untuk melihat foto"
               style="cursor:pointer; transition:opacity .2s;"
               onmouseover="this.style.opacity='.8'"
               onmouseout="this.style.opacity='1'">
            @if($user->avatar)
              <img id="avatarPreview"
                   src="{{ asset('storage/' . $user->avatar) }}"
                   style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
            @else
              <span id="avatarInitial">
                {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
              </span>
            @endif
          </div>
          <div class="u-avatar-ring"></div>
        </div>

        <div class="u-avatar-name">{{ $user->name ?? 'Nama Admin' }}</div>
        <div class="u-avatar-email">{{ $user->email ?? 'email@contoh.com' }}</div>

        <div class="u-avatar-badge">
          <div class="u-update-badge__dot" style="background:var(--emerald)"></div>
          Akun Aktif
        </div>

        <div class="u-avatar-meta">
          <div class="u-avatar-meta__item">
            <span class="u-avatar-meta__label">Bergabung</span>
            <span class="u-avatar-meta__val">
              {{ isset($user->created_at)
                  ? \Carbon\Carbon::parse($user->created_at)->format('M Y')
                  : date('M Y') }}
            </span>
          </div>
          <div class="u-avatar-meta__divider"></div>
          <div class="u-avatar-meta__item">
            <span class="u-avatar-meta__label">Role</span>
            <span class="u-avatar-meta__val">{{ ucfirst($user->role ?? 'Admin') }}</span>
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
              <div class="u-quick-info-list__val">{{ $user->name ?? '—' }}</div>
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
              <div class="u-quick-info-list__val">{{ $user->email ?? '—' }}</div>
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
              <div class="u-quick-info-list__val">{{ $user->phone ?? '—' }}</div>
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
              <div class="u-quick-info-list__val">{{ $user->address ?? '—' }}</div>
            </div>
          </li>
        </ul>
      </div>

    </div>
    {{-- / LEFT --}}

    {{-- ── RIGHT: FORM ───────────────────────────────── --}}
    <div class="u-profil-right">

      <form method="POST" action="/admin/settings/profile" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="u-profil-form-card">
          <div class="u-profil-form-card__header">
            <div class="u-profil-form-card__title">Data Pribadi</div>
            <div class="u-profil-form-card__sub">Informasi ini akan ditampilkan di profil Anda</div>
          </div>
          <div class="u-profil-form-card__body">

            {{-- Foto Profil --}}
            <div class="u-profil-field">
              <label class="u-profil-field__label" for="avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <polyline points="21 15 16 10 5 21"/>
                </svg>
                Foto Profil
                <span class="u-profil-field__optional">Opsional</span>
              </label>
              <input type="file" id="avatar" name="avatar"
                     accept="image/jpg,image/jpeg,image/png,image/webp"
                     class="u-profil-input @error('avatar') is-error @enderror"
                     style="padding: 6px 10px; cursor: pointer;">
              <div style="font-size: 12px; color: var(--text-muted, #6b7280); margin-top: 4px;">
                Maks. 2MB. Format: JPG, PNG, WEBP
              </div>
              @error('avatar')
                <div class="u-profil-field__error">{{ $message }}</div>
              @enderror
            </div>

            {{-- Nama --}}
            <div class="u-profil-field">
              <label class="u-profil-field__label" for="name">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                  <polyline points="22,6 12,13 2,6"/>
                </svg>
                Alamat Email
              </label>
              <input type="email" id="email" name="email"
                     class="u-profil-input @error('email') is-error @enderror"
                     value="{{ old('email', $user->email ?? '') }}"
                     placeholder="nama@email.com"
                     required>
              @error('email')
                <div class="u-profil-field__error">{{ $message }}</div>
              @enderror
            </div>

            {{-- Telepon --}}
            <div class="u-profil-row">
              <div class="u-profil-field">
                <label class="u-profil-field__label" for="phone">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            </div>

            {{-- Alamat --}}
            <div class="u-profil-field">
              <label class="u-profil-field__label" for="address">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

            {{-- Role (read-only) --}}
            <div class="u-profil-field">
              <label class="u-profil-field__label" for="role">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Role
              </label>
              <input type="text" id="role"
                     class="u-profil-input"
                     value="{{ ucfirst($user->role ?? 'Admin') }}"
                     disabled
                     style="cursor: not-allowed; opacity: .6;">
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
              {{ isset($user->updated_at)
                  ? \Carbon\Carbon::parse($user->updated_at)->diffForHumans()
                  : 'belum pernah' }}
            </span>
          </div>
        </div>

      </form>

    </div>
    {{-- / RIGHT --}}

  </div>
  {{-- / profil grid --}}

  {{-- ── MODAL PREVIEW AVATAR ──────────────────────── --}}
  {{--
    CATATAN: Karena u-profil user tidak menggunakan position:fixed,
    jika CSS layout Anda mendukung modal, gunakan class yang sudah ada.
    Berikut ini implementasi modal dengan inline style minimal:
  --}}
  <div id="avatarModal"
       onclick="closeAvatarModal()"
       style="display:none; position:fixed; inset:0; z-index:9999;
              background:rgba(15,23,42,.72); backdrop-filter:blur(6px);
              align-items:center; justify-content:center; cursor:zoom-out;">
    <div onclick="event.stopPropagation()"
         style="position:relative; max-width:420px; width:90%;">
      <img id="avatarModalImg" src=""
           style="width:100%; border-radius:16px;
                  box-shadow:0 20px 60px rgba(0,0,0,.4); display:block;">
      <button onclick="closeAvatarModal()"
              style="position:absolute; top:-14px; right:-14px; width:32px; height:32px;
                     border-radius:50%; border:none; background:#fff; cursor:pointer;
                     font-size:14px; color:#64748b;
                     box-shadow:0 2px 8px rgba(0,0,0,.15);
                     display:flex; align-items:center; justify-content:center;">
        &#x2715;
      </button>
    </div>
  </div>

@endsection

@push('scripts')
<script>
// Preview avatar sebelum upload
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(ev) {
        const wrapper = document.getElementById('avatarWrapper');
        wrapper.innerHTML = `<img src="${ev.target.result}"
            style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        document.getElementById('avatarModalImg').src = ev.target.result;
    };
    reader.readAsDataURL(file);
});

// Buka modal lihat foto
function openAvatarModal() {
    const img = document.querySelector('#avatarWrapper img');
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
</script>
@endpush
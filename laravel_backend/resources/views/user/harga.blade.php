@extends('layouts.layout')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-sub', 'Kelola informasi akun dan data pribadi Anda.')

@section('content')

<nav class="u-breadcrumb">
  <a href="{{ route('user.home') }}">Beranda</a>
  <span class="u-breadcrumb__sep">/</span>
  <span class="u-breadcrumb__current">Profil Saya</span>
</nav>

@if(session('success'))

<div class="u-alert u-alert--success">
  {{ session('success') }}
</div>
@endif

@if(session('error'))

<div class="u-alert u-alert--error">
  {{ session('error') }}
</div>
@endif

<div class="u-profil-grid">

{{-- LEFT --}}

  <div class="u-profil-left">

```
<div class="u-avatar-card">
  <div class="u-avatar-wrap">
    <div class="u-avatar">
      {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div class="u-avatar-ring"></div>
  </div>

  <div class="u-avatar-name">{{ $user->name }}</div>
  <div class="u-avatar-email">{{ $user->email }}</div>

  {{-- 🔥 UPLOAD FOTO --}}
  <div class="u-avatar-badge" style="flex-direction:column; gap:8px; align-items:center;">

    @if($user->avatar)
      <img src="{{ asset('storage/' . $user->avatar) }}"
           style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
    @endif

    <input type="file" name="avatar" form="formProfile"
           accept="image/*"
           style="font-size:12px; max-width:150px;">
  </div>

  <div class="u-avatar-meta">
    <div class="u-avatar-meta__item">
      <span class="u-avatar-meta__label">Bergabung</span>
      <span class="u-avatar-meta__val">
        {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('M Y') : '-' }}
      </span>
    </div>
    <div class="u-avatar-meta__divider"></div>
    <div class="u-avatar-meta__item">
      <span class="u-avatar-meta__label">Role</span>
      <span class="u-avatar-meta__val">User</span>
    </div>
  </div>
</div>

{{-- QUICK INFO --}}
<div class="u-quick-info-card">
  <div class="u-quick-info-card__title">Informasi Cepat</div>
  <ul class="u-quick-info-list">
    <li>
      <div>
        <div class="u-quick-info-list__label">Nama Lengkap</div>
        <div class="u-quick-info-list__val">{{ $user->name ?? '—' }}</div>
      </div>
    </li>
    <li>
      <div>
        <div class="u-quick-info-list__label">Email</div>
        <div class="u-quick-info-list__val">{{ $user->email ?? '—' }}</div>
      </div>
    </li>
    <li>
      <div>
        <div class="u-quick-info-list__label">No. Telepon</div>
        <div class="u-quick-info-list__val">{{ $user->phone ?? '—' }}</div>
      </div>
    </li>
    <li>
      <div>
        <div class="u-quick-info-list__label">Alamat</div>
        <div class="u-quick-info-list__val">{{ $user->address ?? '—' }}</div>
      </div>
    </li>
  </ul>
</div>
```

  </div>

{{-- RIGHT --}}

  <div class="u-profil-right">

```
<form id="formProfile" method="POST"
      action="{{ route('user.profil.update') }}"
      enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="u-profil-form-card">
    <div class="u-profil-form-card__header">
      <div class="u-profil-form-card__title">Data Pribadi</div>
      <div class="u-profil-form-card__sub">Informasi ini akan ditampilkan di profil Anda</div>
    </div>

    <div class="u-profil-form-card__body">

      <div class="u-profil-field">
        <label>Nama Lengkap</label>
        <input type="text" name="nama"
               value="{{ old('nama', $user->name) }}"
               class="u-profil-input" required>
      </div>

      <div class="u-profil-field">
        <label>Email</label>
        <input type="email" name="email"
               value="{{ old('email', $user->email) }}"
               class="u-profil-input" required>
      </div>

      <div class="u-profil-field">
        <label>No. Telepon</label>
        <input type="text" name="telepon"
               value="{{ old('telepon', $user->phone) }}"
               class="u-profil-input">
      </div>

      <div class="u-profil-field">
        <label>Alamat</label>
        <textarea name="alamat"
                  class="u-profil-input">{{ old('alamat', $user->address) }}</textarea>
      </div>

    </div>

    <div class="u-profil-form-card__footer">
      <button type="submit" class="u-btn-simpan">
        Simpan Perubahan
      </button>
    </div>

  </div>

</form>
```

  </div>

</div>

@endsection

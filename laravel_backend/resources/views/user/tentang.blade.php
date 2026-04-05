{{--
  =====================================================
  SIMOPANG — Tentang Kami
  File : resources/views/user/tentang.blade.php
  Desc : Halaman informasi sistem SIMOPANG
  =====================================================
--}}
@extends('user.layouts')

@section('title', 'Tentang SIMOPANG')

@section('content')

  {{-- ── HERO ─────────────────────────────────────── --}}
  <div class="u-tentang-hero">
    <div class="u-tentang-hero__badge">MENGENAL KAMI</div>
    <h1 class="u-tentang-hero__title">Sistem Monitoring Pangan<br>Nasional</h1>
    <p class="u-tentang-hero__desc">
      SIMOPANG adalah platform inovatif yang dirancang untuk memberikan transparansi
      harga komoditas pangan secara real-time. Kami menggabungkan pengumpulan data
      otomatis dengan kecerdasan buatan untuk membantu pengambilan keputusan yang
      lebih baik.
    </p>
  </div>

  {{-- ── VISI & MISI ─────────────────────────────── --}}
  <div class="u-visi-misi-grid">

    <div class="u-vm-card">
      <div class="u-vm-card__header">
        <div class="u-vm-icon u-vm-icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </div>
        <span class="u-vm-card__title">Visi Kami</span>
      </div>
      <p class="u-vm-card__text">
        Menjadi rujukan utama data pangan nasional yang akurat, transparan, dan mampu
        memprediksi dinamika pasar demi ketahanan pangan nasional.
      </p>
    </div>

    <div class="u-vm-card">
      <div class="u-vm-card__header">
        <div class="u-vm-icon u-vm-icon--cyan">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
          </svg>
        </div>
        <span class="u-vm-card__title">Misi Kami</span>
      </div>
      <ul class="u-vm-list">
        <li>
          <span class="u-vm-list__num">1</span>
          Menyediakan data harga pangan yang diperbaharui secara otomatis dan berkala.
        </li>
        <li>
          <span class="u-vm-list__num">2</span>
          Mengimplementasikan teknologi Machine Learning untuk prediksi dan tren harga masa depan.
        </li>
        <li>
          <span class="u-vm-list__num">3</span>
          Memberikan akses informasi yang merata bagi produsen, konsumen, dan pembuat kebijakan.
        </li>
      </ul>
    </div>

  </div>

  {{-- ── TEKNOLOGI ────────────────────────────────── --}}
  <div class="u-tech-section">
    <div class="u-tech-section__head">
      <h2 class="u-tech-section__title">Teknologi Kami</h2>
      <p class="u-tech-section__sub">
        Dibangun dengan arsitektur modern untuk menjamin performa, keamanan, dan akurasi data yang tinggi.
      </p>
    </div>

    <div class="u-tech-grid">

      <div class="u-tech-card">
        <div class="u-tech-card__icon u-tech-card__icon--red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
            <line x1="7" y1="7" x2="7.01" y2="7"/>
          </svg>
        </div>
        <div class="u-tech-card__name">Laravel Ecosystem</div>
        <p class="u-tech-card__desc">
          Menggunakan framework PHP paling populer untuk membangun backend yang robust, elastis, dan dengan keamanan tingkat tinggi.
        </p>
      </div>

      <div class="u-tech-card">
        <div class="u-tech-card__icon u-tech-card__icon--green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <ellipse cx="12" cy="5" rx="9" ry="3"/>
            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
          </svg>
        </div>
        <div class="u-tech-card__name">MongoDB</div>
        <p class="u-tech-card__desc">
          Penyimpanan data berbasis dokumen NoSQL yang mengoptimalkan fleksibilitas data tinggi dan pemrosesan big data harga komoditas secara efisien.
        </p>
      </div>

      <div class="u-tech-card">
        <div class="u-tech-card__icon u-tech-card__icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
        </div>
        <div class="u-tech-card__name">Prophet AI</div>
        <p class="u-tech-card__desc">
          Algoritma forecasting time-series yang dikembangkan Meta, mampu mengenali seasonal effect untuk prediksi harga yang akurat.
        </p>
      </div>

    </div>
  </div>

  {{-- ── KONTAK ───────────────────────────────────── --}}
  <div class="u-kontak-section">

    <div class="u-kontak-info">
      <h2 class="u-kontak-info__title">Informasi Kontak</h2>
      <p class="u-kontak-info__sub">
        Punya pertanyaan atau butuh akses API khusus? Tim kami siap membantu Anda mendapatkan data yang diperlukan.
      </p>
      <ul class="u-kontak-list">
        <li>
          <div class="u-kontak-list__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </div>
          kontak@simopang.go.id
        </li>
        <li>
          <div class="u-kontak-list__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
            </svg>
          </div>
          +62 21 1234 5678
        </li>
        <li>
          <div class="u-kontak-list__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          Jakarta, Indonesia
        </li>
      </ul>
    </div>

    <div class="u-kontak-form-card">
      <div class="u-kontak-form-group">
        <label class="u-kontak-form-label">Nama Lengkap</label>
        <input type="text" class="u-kontak-form-input" placeholder="Masukkan nama Anda">
      </div>
      <div class="u-kontak-form-group">
        <label class="u-kontak-form-label">Email</label>
        <input type="email" class="u-kontak-form-input" placeholder="email@contoh.com">
      </div>
      <div class="u-kontak-form-group">
        <label class="u-kontak-form-label">Pesan</label>
        <textarea class="u-kontak-form-input u-kontak-form-textarea"
                  placeholder="Apa yang bisa kami bantu?"></textarea>
      </div>
      <button class="u-btn-kirim">Kirim Pesan</button>
    </div>

  </div>

@endsection
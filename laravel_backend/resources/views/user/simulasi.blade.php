{{--
  =====================================================
  SIMOPANG — User Simulasi Pengeluaran AI
  File : resources/views/user/simulasi.blade.php
  Desc : Halaman simulasi pengeluaran berbasis prediksi AI
  =====================================================
--}}
@extends('user.layouts')

@section('title', 'Simulasi Pengeluaran AI')

@section('content')

  {{-- ── BREADCRUMB ─────────────────────────────────── --}}
  <nav class="u-breadcrumb">
    <a href="{{ route('user.home') }}">Beranda</a>
    <span class="u-breadcrumb__sep">/</span>
    <span class="u-breadcrumb__current">Simulasi Pengeluaran AI</span>
  </nav>

  {{-- ── PAGE HEADER ───────────────────────────────── --}}
  <div class="u-page-header">
    <div>
      <h1>Simulasi Pengeluaran AI</h1>
      <p>Estimasi pengeluaran Anda berdasarkan tren harga komoditas terkini dan prediksi<br>
         cerdas untuk perencanaan finansial yang lebih baik.</p>
    </div>
  </div>

  {{-- ── SIMULASI GRID ─────────────────────────────── --}}
  <div class="u-sim-grid">

    {{-- ── LEFT: INPUT PANEL ─────────────────────── --}}
    <div class="u-sim-left">

      {{-- Input Card --}}
      <div class="u-input-card">
        <div class="u-input-card__header">
          <div class="u-input-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <line x1="8" y1="21" x2="16" y2="21"/>
              <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
          </div>
          <span class="u-input-card__title">Input Data Konsumsi</span>
        </div>

        <div class="u-input-card__body">

          <div class="u-form-group">
            <label class="u-form-label">Pilih Komoditas</label>
            <select class="u-form-select" id="sim-komoditas" name="komoditas">
              @isset($komoditas)
                @foreach($komoditas as $item)
                  <option value="{{ $item->id }}"
                    {{ ($selectedKomoditas ?? '') == $item->id ? 'selected' : '' }}>
                    {{ $item->nama }}
                  </option>
                @endforeach
              @else
                <option value="1">Beras (Premium)</option>
                <option value="2">Cabai Rawit</option>
                <option value="3">Bawang Merah</option>
                <option value="4">Telur Ayam</option>
              @endisset
            </select>
          </div>

          <div class="u-form-group">
            <label class="u-form-label">Konsumsi per Minggu (Kg/Liter)</label>
            <div class="u-input-unit-wrap">
              <input type="number" class="u-form-input" id="sim-konsumsi"
                     name="konsumsi" value="{{ $konsumsi ?? 0.5 }}"
                     min="0.1" max="100" step="0.1" placeholder="0.5">
              <span class="u-input-unit">kg</span>
            </div>
            <p class="u-form-hint">*Data ini akan digunakan untuk menghitung total bulanan</p>
          </div>

          <button class="u-btn-hitung" id="btn-hitung" onclick="hitungSimulasi(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="2" width="16" height="20" rx="2"/>
              <line x1="8" y1="10" x2="16" y2="10"/>
              <line x1="8" y1="14" x2="16" y2="14"/>
              <line x1="8" y1="18" x2="12" y2="18"/>
            </svg>
            Hitung Estimasi
          </button>

        </div>
      </div>

      {{-- AI Insight Box --}}
      <div class="u-ai-insight">
        <div class="u-ai-insight__header">
          <div class="u-ai-insight__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <span class="u-ai-insight__title">Wawasan AI</span>
        </div>
        <p class="u-ai-insight__text">
          {{ $wawasanAI ?? 'AI kami memprediksi kenaikan harga beras sekitar 2.5% bulan depan dikarenakan faktor musim panen yang bergeser.' }}
        </p>
      </div>

    </div>
    {{-- / LEFT --}}

    {{-- ── RIGHT: HASIL SIMULASI ─────────────────── --}}
    <div class="u-sim-right">

      {{-- Harga Cards Row --}}
      <div class="u-price-grid">

        {{-- Harga Saat Ini --}}
        <div class="u-price-card u-price-card--current">
          <div class="u-price-card__label">Harga Saat Ini</div>
          <div class="u-price-card__main">
            <span class="u-price-card__value">
              Rp {{ number_format($hargaTerbaru ?? 14500, 0, ',', '.') }}
            </span>
            <span class="u-price-card__unit">/kg</span>
          </div>
          <div class="u-price-card__sub-label">Total Pengeluaran Sekarang</div>
          <div class="u-price-card__sub-value">
            Rp {{ number_format($totalSekarang ?? 29000, 0, ',', '.') }}
            <span class="u-price-card__sub-unit">/bulan</span>
          </div>
        </div>

        {{-- Prediksi Bulan Depan --}}
        <div class="u-price-card u-price-card--predict">
          <div class="u-price-card__badge-wrap">
            <span class="u-ai-badge">AI Prediction</span>
          </div>
          <div class="u-price-card__label">Prediksi Harga Bulan Depan</div>
          <div class="u-price-card__main">
            <span class="u-price-card__value">
              Rp {{ number_format($hargaPrediksi ?? 14862, 0, ',', '.') }}
            </span>
            <span class="u-price-card__unit">/kg</span>
          </div>
          <div class="u-price-card__sub-label">Estimasi Pengeluaran Bulan Depan</div>
          <div class="u-price-card__sub-value u-price-card__sub-value--predict">
            Rp {{ number_format($totalPrediksi ?? 29724, 0, ',', '.') }}
            <span class="u-price-card__sub-unit">/bulan</span>
          </div>
        </div>

      </div>
      {{-- / price grid --}}

      {{-- Ringkasan Anggaran --}}
      <div class="u-budget-card">

        <div class="u-budget-card__header">
          <div>
            <div class="u-budget-card__title">Ringkasan Anggaran</div>
            <div class="u-budget-card__sub">
              Berdasarkan konsumsi {{ $konsumsi ?? '0.5' }} kg per minggu
            </div>
          </div>
          <button class="u-dl-btn" title="Unduh Ringkasan">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="5" y="2" width="14" height="20" rx="2"/>
              <line x1="9" y1="7" x2="15" y2="7"/>
              <line x1="9" y1="11" x2="15" y2="11"/>
              <line x1="9" y1="15" x2="12" y2="15"/>
            </svg>
          </button>
        </div>

        <div class="u-budget-body">

          {{-- Selisih --}}
          <div class="u-budget-col">
            <div class="u-budget-col__label">Selisih Pengeluaran</div>
            <div class="u-selisih u-selisih--up">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                <polyline points="16 7 22 7 22 13"/>
              </svg>
              + Rp {{ number_format($selisih ?? 724, 0, ',', '.') }}
            </div>
            <p class="u-selisih__desc">
              Peningkatan biaya estimasi sekitar
              <strong class="u-selisih__pct">{{ $changePercent ?? '2.5' }}%</strong>
            </p>
          </div>

          {{-- Rekomendasi --}}
          <div class="u-budget-col">
            <div class="u-budget-col__label">Rekomendasi Tindakan</div>
            <div class="u-action-btns">
              <button class="u-btn-action u-btn-action--primary">Stok Lebih Awal</button>
              <button class="u-btn-action u-btn-action--secondary">Cari Promo</button>
            </div>
          </div>

        </div>

        {{-- Mini Chart --}}
        <div class="u-sim-chart-wrap">
          <svg class="u-sim-chart-svg" viewBox="0 0 500 90"
               xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">

            {{-- Grid lines --}}
            <line x1="0" y1="20" x2="500" y2="20" stroke="#e5eaf3" stroke-width="1"/>
            <line x1="0" y1="45" x2="500" y2="45" stroke="#e5eaf3" stroke-width="1"/>
            <line x1="0" y1="70" x2="500" y2="70" stroke="#e5eaf3" stroke-width="1"/>

            {{-- Area fill (actual) --}}
            <defs>
              <linearGradient id="sim-grad-actual" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#2563eb" stop-opacity=".18"/>
                <stop offset="100%" stop-color="#2563eb" stop-opacity="0"/>
              </linearGradient>
              <linearGradient id="sim-grad-predict" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#0ea5e9" stop-opacity=".22"/>
                <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0"/>
              </linearGradient>
            </defs>

            {{-- Actual line area --}}
            <path d="M0,62 C80,58 160,50 250,45 L250,90 L0,90 Z"
                  fill="url(#sim-grad-actual)"/>
            <path d="M0,62 C80,58 160,50 250,45"
                  stroke="#2563eb" stroke-width="2" fill="none"
                  stroke-linecap="round"/>

            {{-- Predicted line area (dashed) --}}
            <path d="M250,45 C330,40 400,30 500,22 L500,90 L250,90 Z"
                  fill="url(#sim-grad-predict)"/>
            <path d="M250,45 C330,40 400,30 500,22"
                  stroke="#0ea5e9" stroke-width="2" fill="none"
                  stroke-dasharray="5,4" stroke-linecap="round"/>

            {{-- Divider --}}
            <line x1="250" y1="10" x2="250" y2="80"
                  stroke="#e5eaf3" stroke-width="1" stroke-dasharray="3,3"/>

            {{-- Dots --}}
            <circle cx="0"   cy="62" r="3" fill="#2563eb"/>
            <circle cx="83"  cy="58" r="3" fill="#2563eb"/>
            <circle cx="166" cy="50" r="3" fill="#2563eb"/>
            <circle cx="250" cy="45" r="4" fill="#2563eb"/>
            <circle cx="375" cy="30" r="3" fill="#0ea5e9" stroke="white" stroke-width="1.5"/>
            <circle cx="500" cy="22" r="3" fill="#0ea5e9" stroke="white" stroke-width="1.5"/>

          </svg>

          <div class="u-sim-chart-labels">
            <span>MAR</span>
            <span>APR</span>
            <span>MEI</span>
            <span>JUN →</span>
          </div>
          <div class="u-sim-chart-caption">
            Grafik Tren Harga 4 Bulan Terakhir &amp; Prediksi
          </div>
        </div>

      </div>
      {{-- / budget card --}}

    </div>
    {{-- / RIGHT --}}

  </div>
  {{-- / sim grid --}}

@endsection

@push('scripts')
<script>
function hitungSimulasi(btn) {
  const orig = btn.innerHTML;
  btn.innerHTML = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round"
         style="animation:spin .7s linear infinite;width:15px;height:15px">
      <path d="M21 12a9 9 0 11-6.22-8.56"/>
    </svg> Menghitung...`;
  btn.disabled = true;
  setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 1800);
}
</script>
@endpush
{{--
  =====================================================
  SIMOPANG — User Prediksi Harga Komoditas
  File : resources/views/user/prediksi.blade.php
  Desc : Halaman prediksi harga berbasis AI Prophet
  =====================================================
--}}
@extends('layouts.layout')

@section('title', 'Prediksi Harga Komoditas')
@section('page-title', 'Prediksi Harga Komoditas')
@section('page-sub', 'Pantau pergerakan harga historis dan perkiraan harga masa depan menggunakan model AI Prophet untuk perencanaan stok dan belanja yang lebih akurat')

@section('content')

  {{-- ── FILTER BAR ────────────────────────────────── --}}
  <div class="u-filter-bar">

    {{-- Komoditas --}}
    <div class="u-filter-group">
      <label class="u-filter-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
        </svg>
        Pilih Komoditas
      </label>
      <select class="u-filter-select" name="komoditas">
        @isset($komoditas)
          @foreach($komoditas as $item)
            <option value="{{ $item->id }}"
              {{ ($selectedKomoditas ?? '') == $item->id ? 'selected' : '' }}>
              {{ $item->nama }}
            </option>
          @endforeach
        @else
          <option value="1">Beras Premium</option>
          <option value="2">Cabai Rawit</option>
          <option value="3">Bawang Merah</option>
          <option value="4">Telur Ayam</option>
        @endisset
      </select>
    </div>

    {{-- Wilayah --}}
    <div class="u-filter-group">
      <label class="u-filter-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/>
          <circle cx="12" cy="10" r="3"/>
        </svg>
        Pilih Wilayah
      </label>
      <select class="u-filter-select" name="wilayah">
        <option value="">Semua Wilayah</option>
        @isset($wilayah)
          @foreach($wilayah as $w)
            <option value="{{ $w->id }}"
              {{ ($selectedWilayah ?? '') == $w->id ? 'selected' : '' }}>
              {{ $w->nama }}
            </option>
          @endforeach
        @else
          <option value="1" selected>Jawa Timur</option>
          <option value="2">Jawa Tengah</option>
          <option value="3">Jawa Barat</option>
          <option value="4">DKI Jakarta</option>
        @endisset
      </select>
    </div>

    <button class="u-btn-filter" onclick="perbaruiGrafik(this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="1 4 1 10 7 10"/>
        <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
      </svg>
      Perbarui Grafik
    </button>

  </div>

  {{-- ── CHART CARD ─────────────────────────────────── --}}
  <div class="u-chart-card">

    <div class="u-chart-card__header">
      <div>
        <div class="u-chart-card__title">Analisis Tren &amp; Prediksi Harga</div>
        <div class="u-chart-card__sub">Estimasi 90 hari kedepan menggunakan AI Prophet Modeling</div>
      </div>
      <div class="u-pred-legend">
        <div class="u-pred-legend__item">
          <span class="u-pred-legend__line u-pred-legend__line--solid"></span>
          Historis
        </div>
        <div class="u-pred-legend__item">
          <span class="u-pred-legend__line u-pred-legend__line--dashed"></span>
          Prediksi AI
        </div>
        <div class="u-pred-legend__item">
          <span class="u-pred-legend__area"></span>
          Confidence Interval
        </div>
      </div>
    </div>

    {{-- SVG Chart --}}
    <div class="u-chart-wrap">
      <svg class="u-pred-chart-svg" viewBox="0 0 700 200"
           xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <defs>
          <linearGradient id="pred-conf-grad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#2563eb" stop-opacity=".12"/>
            <stop offset="100%" stop-color="#2563eb" stop-opacity=".02"/>
          </linearGradient>
        </defs>

        {{-- Y-axis grid lines --}}
        <line x1="50" y1="20"  x2="680" y2="20"  stroke="#e5eaf3" stroke-width="1"/>
        <line x1="50" y1="60"  x2="680" y2="60"  stroke="#e5eaf3" stroke-width="1"/>
        <line x1="50" y1="100" x2="680" y2="100" stroke="#e5eaf3" stroke-width="1"/>
        <line x1="50" y1="140" x2="680" y2="140" stroke="#e5eaf3" stroke-width="1"/>
        <line x1="50" y1="175" x2="680" y2="175" stroke="#e5eaf3" stroke-width="1"/>

        {{-- Y-axis labels --}}
        <text x="42" y="24"  class="u-chart-y-lbl" text-anchor="end">16.000</text>
        <text x="42" y="64"  class="u-chart-y-lbl" text-anchor="end">15.500</text>
        <text x="42" y="104" class="u-chart-y-lbl" text-anchor="end">15.000</text>
        <text x="42" y="144" class="u-chart-y-lbl" text-anchor="end">14.500</text>
        <text x="42" y="179" class="u-chart-y-lbl" text-anchor="end">14.000</text>

        {{-- X-axis labels --}}
        <text x="90"  y="195" class="u-chart-x-lbl">Jan</text>
        <text x="160" y="195" class="u-chart-x-lbl">Feb</text>
        <text x="230" y="195" class="u-chart-x-lbl">Mar</text>
        <text x="300" y="195" class="u-chart-x-lbl">Apr</text>
        <text x="370" y="195" class="u-chart-x-lbl">Mei</text>
        <text x="440" y="195" class="u-chart-x-lbl">Jun</text>
        <text x="510" y="195" class="u-chart-x-lbl">Jul →</text>
        <text x="580" y="195" class="u-chart-x-lbl">Agu →</text>
        <text x="645" y="195" class="u-chart-x-lbl">Sep →</text>

        {{-- Divider: historis vs prediksi --}}
        <line x1="440" y1="12" x2="440" y2="178"
              stroke="#bfdbfe" stroke-width="1.5" stroke-dasharray="4,3"/>

        {{-- Confidence interval area (prediksi) --}}
        <path d="M440,105 C470,98 500,85 530,72 C560,59 600,44 650,28
                 L650,60 C600,74 560,88 530,100 C500,112 470,122 440,128 Z"
              fill="url(#pred-conf-grad)"/>

        {{-- Historical line --}}
        <path d="M60,150 C90,145 120,155 160,160 C200,165 230,158 270,148
                 C310,138 340,128 370,122 C400,116 420,110 440,105"
              stroke="#2563eb" stroke-width="2.5" fill="none"
              stroke-linecap="round" stroke-linejoin="round"/>

        {{-- Prediction line (dashed) --}}
        <path d="M440,105 C470,98 500,85 530,72 C560,59 600,44 650,28"
              stroke="#2563eb" stroke-width="2" fill="none"
              stroke-dasharray="6,4" stroke-linecap="round"/>

        {{-- Transition dot --}}
        <circle cx="440" cy="105" r="5" fill="white" stroke="#2563eb" stroke-width="2.5"/>

        {{-- Historical dots --}}
        <circle cx="60"  cy="150" r="3" fill="#2563eb"/>
        <circle cx="160" cy="160" r="3" fill="#2563eb"/>
        <circle cx="270" cy="148" r="3" fill="#2563eb"/>
        <circle cx="370" cy="122" r="3" fill="#2563eb"/>

        {{-- Predicted dots --}}
        <circle cx="510" cy="80"  r="3" fill="white" stroke="#2563eb" stroke-width="2"/>
        <circle cx="580" cy="52"  r="3" fill="white" stroke="#2563eb" stroke-width="2"/>
        <circle cx="650" cy="28"  r="3" fill="white" stroke="#2563eb" stroke-width="2"/>
      </svg>
    </div>

  </div>

  {{-- ── PREDIKSI STAT CARDS ────────────────────────── --}}
  <div class="u-pred-stat-row">

    {{-- Estimasi Harga --}}
    <div class="u-pred-stat-card u-pred-stat-card--blue">
      <div class="u-pred-stat-card__label">Estimasi Harga (Jul 2024)</div>
      <div class="u-pred-stat-card__value">
        Rp {{ number_format($estimasiHarga ?? 15240, 0, ',', '.') }}
        <span class="u-pred-stat-card__unit">/kg</span>
      </div>
      <div class="u-pred-stat-card__note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Berdasarkan tren rata-rata telapan
      </div>
    </div>

    {{-- Tren Prediksi --}}
    <div class="u-pred-stat-card u-pred-stat-card--rose">
      <div class="u-pred-stat-card__label">Tren Prediksi (30 Hari)</div>
      <div class="u-pred-stat-card__value u-pred-stat-card__value--up">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
          <polyline points="16 7 22 7 22 13"/>
        </svg>
        +{{ $trenPersen ?? '4.2' }}%
      </div>
      <div class="u-pred-stat-card__sub">
        Kenaikan diperkirakan berlanjut hingga akhir kuartal.
      </div>
    </div>

    {{-- Tingkat Kepercayaan AI --}}
    <div class="u-pred-stat-card u-pred-stat-card--blue">
      <div class="u-pred-stat-card__label">Tingkat Kepercayaan AI</div>
      <div class="u-pred-stat-card__value u-pred-stat-card__value--conf">
        {{ $kepercayaan ?? '95.4' }}%
      </div>
      <div class="u-conf-bar-wrap">
        <div class="u-conf-bar">
          <div class="u-conf-bar__fill"
               style="width: {{ $kepercayaan ?? '95.4' }}%"></div>
        </div>
      </div>
      <div class="u-pred-stat-card__sub">
        Model validasi ulang dengan akurasi tinggi.
      </div>
    </div>

  </div>

  {{-- ── WEEKLY TABLE ───────────────────────────────── --}}
  <div class="u-table-card">

    <div class="u-table-card__header">
      <div class="u-table-card__title">Detail Angka Prediksi (Mingguan)</div>
      <div class="u-table-actions">
        <button class="u-btn-csv" onclick="downloadCSV()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Unduh CSV
        </button>
      </div>
    </div>

    <div class="u-table-wrap">
      <table class="u-table">
        <thead>
          <tr>
            <th>Minggu Ke-</th>
            <th>Periode</th>
            <th>Estimasi Harga</th>
            <th>Variasi Harga</th>
          </tr>
        </thead>
        <tbody>
          @isset($prediksiMingguan)
            @forelse($prediksiMingguan as $row)
            <tr>
              <td class="u-pred-week">{{ $row->minggu }}</td>
              <td class="u-table__date">{{ $row->periode }}</td>
              <td class="u-table__harga">Rp {{ number_format($row->estimasi, 0, ',', '.') }}</td>
              <td>
                <span class="u-variasi">± Rp {{ number_format($row->variasi, 0, ',', '.') }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" style="text-align:center;padding:40px;color:var(--text-3);font-size:13px">
                Tidak ada data prediksi untuk filter yang dipilih.
              </td>
            </tr>
            @endforelse
          @else
            <tr>
              <td class="u-pred-week">W1 - Jun</td>
              <td class="u-table__date">01 Jun – 07 Jun</td>
              <td class="u-table__harga">Rp 14.650</td>
              <td><span class="u-variasi">± Rp 120</span></td>
            </tr>
            <tr>
              <td class="u-pred-week">W2 - Jun</td>
              <td class="u-table__date">08 Jun – 14 Jun</td>
              <td class="u-table__harga">Rp 14.780</td>
              <td><span class="u-variasi">± Rp 140</span></td>
            </tr>
            <tr>
              <td class="u-pred-week">W3 - Jun</td>
              <td class="u-table__date">15 Jun – 21 Jun</td>
              <td class="u-table__harga">Rp 14.920</td>
              <td><span class="u-variasi">± Rp 165</span></td>
            </tr>
            <tr>
              <td class="u-pred-week">W4 - Jun</td>
              <td class="u-table__date">22 Jun – 30 Jun</td>
              <td class="u-table__harga">Rp 15.100</td>
              <td><span class="u-variasi">± Rp 180</span></td>
            </tr>
            <tr>
              <td class="u-pred-week">W1 - Jul</td>
              <td class="u-table__date">01 Jul – 07 Jul</td>
              <td class="u-table__harga">Rp 15.240</td>
              <td><span class="u-variasi">± Rp 200</span></td>
            </tr>
          @endisset
        </tbody>
      </table>
    </div>

  </div>

@endsection

@push('scripts')
<script>
function perbaruiGrafik(btn) {
  const orig = btn.innerHTML;
  btn.innerHTML = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round"
         style="animation:spin .7s linear infinite;width:14px;height:14px">
      <path d="M21 12a9 9 0 11-6.22-8.56"/>
    </svg> Memuat...`;
  btn.disabled = true;
  setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 1800);
}

function downloadCSV() {
  alert('Mengunduh data CSV prediksi...');
}
</script>
@endpush
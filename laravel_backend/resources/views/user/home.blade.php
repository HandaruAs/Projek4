@extends('layouts.layout')

@section('title', 'Monitoring Harga Komoditas')

@section('content')

  {{-- ── PAGE HEADER ───────────────────────────────── --}}
  <div class="u-page-header">
    <h1>Monitoring Harga Komoditas</h1>
    <p>Pantau harga pasar real-time dan prediksi tren masa depan menggunakan AI.</p>
  </div>

  {{-- ── FILTER BAR ────────────────────────────────── --}}
  <div class="u-filter-bar">

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
          <option value="2">Jagung</option>
          <option value="3">Kedelai</option>
        @endisset
      </select>
    </div>

    <div class="u-filter-group">
      <label class="u-filter-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/>
          <circle cx="12" cy="10" r="3"/>
        </svg>
        Pilih Daerah
      </label>
      <select class="u-filter-select" name="daerah">
        @isset($daerah)
          @foreach($daerah as $d)
            <option value="{{ $d->id }}"
              {{ ($selectedDaerah ?? '') == $d->id ? 'selected' : '' }}>
              {{ $d->nama }}
            </option>
          @endforeach
        @else
          <option value="1">Jember</option>
          <option value="2">Surabaya</option>
          <option value="3">Malang</option>
        @endisset
      </select>
    </div>

    <button class="u-btn-filter" onclick="applyFilter(this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      Terapkan Filter
    </button>

  </div>

  {{-- ── STAT CARDS ────────────────────────────────── --}}
  <div class="u-stat-row">

    {{-- Harga Terbaru --}}
    <div class="u-stat-card u-stat-card--blue">
      <div class="u-stat-card__top">
        <div class="u-stat-icon u-stat-icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
          </svg>
        </div>
        <span class="u-stat-chip u-stat-chip--up">↑ +{{ $hargaPercent ?? '2.6' }}%</span>
      </div>
      <div class="u-stat-card__label">Harga Terbaru ({{ $namaKomoditas ?? 'Beras' }})</div>
      <div class="u-stat-card__value">
        Rp {{ number_format($hargaTerbaru ?? 14500, 0, ',', '.') }}
        <span class="u-stat-card__unit">/kg</span>
      </div>
      <div class="u-stat-card__sub">Diperbarui hari ini pukul {{ $jamUpdate ?? '08:00' }} WIB</div>
    </div>

    {{-- Perubahan Harian --}}
    <div class="u-stat-card u-stat-card--green">
      <div class="u-stat-card__top">
        <div class="u-stat-icon u-stat-icon--green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
            <polyline points="16 7 22 7 22 13"/>
          </svg>
        </div>
        <span class="u-stat-chip u-stat-chip--up">↑ +{{ $changePercent ?? '5.6' }}%</span>
      </div>
      <div class="u-stat-card__label">Perubahan Harian</div>
      <div class="u-stat-card__value">
        + Rp {{ number_format($hargaChange ?? 200, 0, ',', '.') }}
      </div>
      <div class="u-stat-card__sub">
        vs kemarin Rp {{ number_format($hargaKemarin ?? 14300, 0, ',', '.') }}
      </div>
    </div>

    {{-- Status Volatilitas --}}
    <div class="u-stat-card u-stat-card--amber">
      <div class="u-stat-card__top">
        <div class="u-stat-icon u-stat-icon--amber">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
        </div>
        <span class="u-stat-chip u-stat-chip--low">Stabil</span>
      </div>
      <div class="u-stat-card__label">Status Volatilitas</div>
      <div class="u-stat-card__value" style="font-size:20px;letter-spacing:0;font-family:'Plus Jakarta Sans',sans-serif">
        {{ $statusVolatilitas ?? 'Rendah' }}
      </div>
      <div class="u-stat-card__sub">
        Indeks: {{ $indexVolatilitas ?? '0.38' }} (normal)
      </div>
    </div>

  </div>

  {{-- ── MAIN GRID: CHART + PREDIKSI ──────────────── --}}
  <div class="u-main-grid">

    {{-- LINE CHART --}}
    <div class="u-chart-card">
      <div class="u-chart-card__header">
        <div>
          <div class="u-chart-card__title">Grafik Harga Historis</div>
          <div class="u-chart-card__sub">
            Pergerakan harga {{ strtolower($namaKomoditas ?? 'beras') }} dalam 30 hari terakhir
          </div>
        </div>
        <div class="u-chart-controls">
          <div class="u-period-group">
            <button class="u-period-btn active" onclick="setPeriod(this, 30)">30 Hari</button>
            <button class="u-period-btn" onclick="setPeriod(this, 90)">90 Hari</button>
          </div>
          <div class="u-dl-btn" onclick="downloadChart()" title="Unduh grafik">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
          </div>
        </div>
      </div>

      {{-- SVG Chart --}}
      <div class="u-chart-wrap" id="chartWrap">
        <svg class="u-chart-svg" id="mainChart"
             viewBox="0 0 660 220" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%"   stop-color="#2563eb" stop-opacity=".18"/>
              <stop offset="100%" stop-color="#2563eb" stop-opacity="0"/>
            </linearGradient>
            <linearGradient id="lineGrad" x1="0" y1="0" x2="1" y2="0">
              <stop offset="0%"   stop-color="#2563eb"/>
              <stop offset="100%" stop-color="#0ea5e9"/>
            </linearGradient>
          </defs>

          {{-- Grid --}}
          <line x1="44" y1="10"  x2="44"  y2="186" stroke="#e5eaf3" stroke-width="1"/>
          <line x1="44" y1="10"  x2="650" y2="10"  stroke="#e5eaf3" stroke-width="1" stroke-dasharray="4 3"/>
          <line x1="44" y1="54"  x2="650" y2="54"  stroke="#e5eaf3" stroke-width="1" stroke-dasharray="4 3"/>
          <line x1="44" y1="98"  x2="650" y2="98"  stroke="#e5eaf3" stroke-width="1" stroke-dasharray="4 3"/>
          <line x1="44" y1="142" x2="650" y2="142" stroke="#e5eaf3" stroke-width="1" stroke-dasharray="4 3"/>
          <line x1="44" y1="186" x2="650" y2="186" stroke="#e5eaf3" stroke-width="1"/>

          {{-- Y Labels --}}
          <text class="u-chart-y-lbl" x="38" y="14"  text-anchor="end">15.000</text>
          <text class="u-chart-y-lbl" x="38" y="58"  text-anchor="end">14.900</text>
          <text class="u-chart-y-lbl" x="38" y="102" text-anchor="end">14.700</text>
          <text class="u-chart-y-lbl" x="38" y="146" text-anchor="end">14.500</text>
          <text class="u-chart-y-lbl" x="38" y="190" text-anchor="end">14.300</text>

          {{-- Area Fill --}}
          <path id="areaPath"
            d="M44,186 L65,179 L107,170 L149,163 L191,156 L233,149 L275,139 L317,128 L359,118 L401,105 L443,94 L485,84 L527,68 L569,52 L611,34 L650,16 L650,186 Z"
            fill="url(#areaGrad)"/>

          {{-- Line --}}
          <path id="linePath"
            d="M44,186 L65,179 L107,170 L149,163 L191,156 L233,149 L275,139 L317,128 L359,118 L401,105 L443,94 L485,84 L527,68 L569,52 L611,34 L650,16"
            fill="none" stroke="url(#lineGrad)" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round"/>

          {{-- Data Points --}}
          <circle cx="191" cy="156" r="5" fill="white" stroke="#2563eb" stroke-width="2"
                  class="u-chart-point" data-val="Rp 14.450"/>
          <circle cx="359" cy="118" r="5" fill="white" stroke="#2563eb" stroke-width="2"
                  class="u-chart-point" data-val="Rp 14.520"/>
          <circle cx="527" cy="68"  r="5" fill="white" stroke="#0ea5e9" stroke-width="2"
                  class="u-chart-point" data-val="Rp 14.490"/>
          <circle cx="650" cy="16"  r="6" fill="#2563eb" stroke="white" stroke-width="2.5"
                  class="u-chart-point" data-val="Rp 14.500"/>

          {{-- X Labels --}}
          <text class="u-chart-x-lbl" x="44"  y="202">01 Okt</text>
          <text class="u-chart-x-lbl" x="149" y="202">06 Okt</text>
          <text class="u-chart-x-lbl" x="275" y="202">11 Okt</text>
          <text class="u-chart-x-lbl" x="401" y="202">16 Okt</text>
          <text class="u-chart-x-lbl" x="527" y="202">21 Okt</text>
          <text class="u-chart-x-lbl" x="650" y="202">31 Okt</text>
        </svg>

        {{-- Tooltip --}}
        <div class="u-chart-tooltip" id="chartTooltip"></div>
      </div>
    </div>

    {{-- PREDIKSI CARD --}}
    <div class="u-pred-card">
      <div class="u-pred-card__header">
        <h2>Prediksi</h2>
        <span class="u-ai-badge">AI Prophet</span>
      </div>
      <p class="u-pred-card__sub">Estimasi tren harga ke depan</p>

      <div class="u-pred-body">

        @foreach($prediksi ?? [
          ['label' => 'BESOK', 'harga' => 14550, 'pct' => '+0.3%'],
          ['label' => '3 HARI', 'harga' => 14600, 'pct' => '+0.8%'],
          ['label' => '7 HARI', 'harga' => 14850, 'pct' => '+2.4%'],
        ] as $pred)
        <div class="u-pred-item">
          <div class="u-pred-period">{{ $pred['label'] }}</div>
          <div class="u-pred-row">
            <span class="u-pred-price">
              Rp {{ number_format($pred['harga'], 0, ',', '.') }}
            </span>
            <span class="u-pred-pct">{{ $pred['pct'] }}</span>
          </div>
        </div>
        @endforeach

        {{-- Mini Trend Chart --}}
        <div class="u-mini-chart">
          <div class="u-mini-chart__label">Tren Naik Terdeteksi</div>
          <svg viewBox="0 0 240 68" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="miniGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%"   stop-color="#2563eb" stop-opacity=".14"/>
                <stop offset="100%" stop-color="#2563eb" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <path d="M0,60 L30,52 L60,46 L90,38 L120,30 L150,22 L180,14 L210,8 L240,3 L240,68 L0,68 Z"
              fill="url(#miniGrad)"/>
            <path d="M0,60 L30,52 L60,46 L90,38 L120,30 L150,22 L180,14 L210,8 L240,3"
              fill="none" stroke="#2563eb" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="240" cy="3" r="4" fill="#2563eb"/>
          </svg>
        </div>

      </div>
    </div>

  </div>

  {{-- ── INFO BAR ───────────────────────────────────── --}}
  <div class="u-info-bar">
    <div class="u-info-bar__icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
    </div>
    <div class="u-info-bar__text">
      <strong>Sumber Data Terpercaya</strong>
      <p>
        Data dikumpulkan secara otomatis melalui integrasi API MongoDB
        dan dianalisis menggunakan algoritma Prophet.
      </p>
    </div>
    <div class="u-info-bar__actions">
      <button class="u-btn-pdf" id="pdfBtn" onclick="downloadPDF()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Unduh Laporan PDF
      </button>
      <button class="u-btn-api">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
          <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
        </svg>
        API Dokumentasi
      </button>
    </div>
  </div>

@endsection

{{-- ── SCRIPTS ─────────────────────────────────────── --}}
@push('scripts')
<script>
/* Period toggle */
function setPeriod(btn, days) {
  document.querySelectorAll('.u-period-btn')
    .forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  /* TODO: fetch data chart baru berdasarkan period */
}

/* Chart point tooltip */
document.querySelectorAll('.u-chart-point').forEach(pt => {
  pt.addEventListener('mouseenter', () => {
    const tip  = document.getElementById('chartTooltip');
    const svg  = document.getElementById('mainChart');
    const r1   = svg.getBoundingClientRect();
    const r2   = pt.getBoundingClientRect();
    tip.style.left    = (r2.left - r1.left + r2.width / 2) + 'px';
    tip.style.top     = (r2.top  - r1.top) + 'px';
    tip.textContent   = pt.dataset.val;
    tip.style.opacity = '1';
  });
  pt.addEventListener('mouseleave', () => {
    document.getElementById('chartTooltip').style.opacity = '0';
  });
});

/* Apply filter with loading state */
function applyFilter(btn) {
  const orig = btn.innerHTML;
  btn.innerHTML = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round"
         style="animation:spin .7s linear infinite;width:14px;height:14px">
      <path d="M21 12a9 9 0 11-6.22-8.56"/>
    </svg> Memuat...`;
  btn.disabled = true;
  /* Simulate — ganti dengan form submit / AJAX ke backend */
  setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 1500);
}

/* Download PDF */
function downloadPDF() {
  const btn  = document.getElementById('pdfBtn');
  const orig = btn.innerHTML;
  btn.innerHTML = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round"
         style="animation:spin .7s linear infinite;width:13px;height:13px">
      <path d="M21 12a9 9 0 11-6.22-8.56"/>
    </svg> Membuat PDF...`;
  btn.disabled = true;
  setTimeout(() => {
    btn.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
           stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px">
        <polyline points="20 6 9 17 4 12"/>
      </svg> Selesai!`;
    btn.style.background = '#10b981';
    setTimeout(() => {
      btn.innerHTML        = orig;
      btn.disabled         = false;
      btn.style.background = '';
    }, 2000);
  }, 1800);
}

/* Download chart */
function downloadChart() {
  /* TODO: export chart as image */
  alert('Grafik sedang diunduh...');
}
</script>
@endpush

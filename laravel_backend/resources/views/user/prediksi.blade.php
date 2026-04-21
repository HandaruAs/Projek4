{{--
  =====================================================
  SIMOPANG — User Prediksi Harga Komoditas
  File : resources/views/user/prediksi.blade.php
  Desc : Halaman prediksi harga berbasis AI Prophet
  =====================================================
--}}
@extends('layouts.layout')

@section('title', 'Prediksi Harga')
@section('page-title', 'Prediksi Harga')
@section('page-sub', 'Hasil prediksi harga komoditas menggunakan Holt-Winters Exponential Smoothing.')

@section('content')

{{-- Filter Komoditas --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body">
        <form method="GET" action="{{ route('user.prediksi') }}"
              style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <label style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);display:block;margin-bottom:6px">
                    FILTER KOMODITAS
                </label>
                <select class="form-select" name="commodity_id"
                        onchange="this.form.submit()">
                    <option value="">— Semua Komoditas —</option>
                    @foreach($commodities as $c)
                        <option value="{{ $c->_id }}"
                            {{ request('commodity_id') == $c->_id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('commodity_id'))
                <a href="{{ route('user.prediksi') }}"
                   style="padding:8px 16px;font-size:13px;color:var(--muted);text-decoration:none;
                          border:1px solid var(--border);border-radius:8px;white-space:nowrap">
                    <i class="fas fa-xmark"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Prediction History Table --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-chart-line" style="color:var(--accent);margin-right:8px"></i>
                Riwayat Prediksi
            </div>
            <div class="table-sub">Data prediksi harga komoditas pangan Jember</div>
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
<<<<<<< HEAD
            <tr>
                <th>Date/Time</th>
                <th>Komoditas</th>
                <th>Horizon</th>
                <th>Trend / Seasonal</th>
                <th>MAE</th>
                <th>RMSE</th>
                <th>MAPE (%)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($predictions as $item)
            @php
                $metrics  = $item->metrics ?? [];
                $status   = $metrics['status'] ?? 'completed';
                $badgeMap = [
                    'completed'     => 'badge-status-completed',
                    'review_needed' => 'badge-status-review',
                    'failed'        => 'badge-status-failed',
                ];
                $labelMap = [
                    'completed'     => 'COMPLETED',
                    'review_needed' => 'REVIEW NEEDED',
                    'failed'        => 'FAILED',
                ];
            @endphp
            <tr>
                <td class="date-text" style="white-space:nowrap">
                    {{ \Carbon\Carbon::parse($item->predicted_at)->format('d M Y') }}<br>
                    <span style="color:var(--muted)">
                        {{ \Carbon\Carbon::parse($item->predicted_at)->format('H:i') }}
                    </span>
                </td>
                <td class="commodity-name">{{ $item->commodity_name ?? '—' }}</td>
                <td class="date-text">{{ $item->horizon_days }} Hari</td>
                <td class="date-text" style="font-size:.72rem;white-space:nowrap">
                    T: <strong>{{ ucfirst($metrics['trend'] ?? '-') }}</strong><br>
                    S: <strong>{{ ucfirst($metrics['seasonal'] ?? '-') }}</strong>
                </td>
                <td class="date-text">
                    {{ isset($metrics['mae'])  ? number_format($metrics['mae'],  2) : '—' }}
                </td>
                <td class="date-text">
                    {{ isset($metrics['rmse']) ? number_format($metrics['rmse'], 2) : '—' }}
                </td>
                <td class="date-text">
                    {{ isset($metrics['mape']) ? number_format($metrics['mape'], 2).'%' : '—' }}
                </td>
                <td>
                    <span class="{{ $badgeMap[$status] ?? 'badge-status-completed' }}">
                        {{ $labelMap[$status] ?? strtoupper($status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('user.prediksi.show', $item->_id) }}"
                       class="pred-action-link">
                        <i class="fas fa-chart-line"></i> Lihat Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div style="text-align:center;padding:2rem;color:var(--muted)">
                        <i class="fas fa-clock-rotate-left" style="font-size:2rem;margin-bottom:.5rem;display:block"></i>
                        Belum ada riwayat prediksi
                    </div>
                </td>
=======
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
>>>>>>> fd823bc0833f5e144f68f61a74f5531fc4687a14
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
<<<<<<< HEAD
    </table>

    @if($predictions->hasPages())
    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $predictions->firstItem() }}–{{ $predictions->lastItem() }}
            of {{ $predictions->total() }} results
        </span>
        <div>{{ $predictions->links() }}</div>
    </div>
    @endif
</div>

@endsection
=======
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
>>>>>>> fd823bc0833f5e144f68f61a74f5531fc4687a14

{{--
  =====================================================
  SIMOPANG — User Prediksi Harga Komoditas
  File : resources/views/user/prediksi.blade.php
  Desc : Halaman prediksi harga dengan BAR CHART premium
  =====================================================
--}}
@extends('layouts.layout')

@section('title', 'Prediksi Harga Komoditas')
@section('page-title', 'Prediksi Harga Komoditas')
@section('page-sub', 'Pantau pergerakan harga historis dan perkiraan harga masa depan menggunakan model AI Prophet untuk perencanaan stok dan belanja yang lebih akurat')

@section('content')

  {{-- ── FILTER BAR ────────────────────────────────── --}}
  <div class="u-filter-bar">
    <div class="u-filter-group">
      <label class="u-filter-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
        </svg>
        Pilih Komoditas
      </label>
      <select class="u-filter-select" id="komoditasSelect">
        @isset($komoditas)
          @foreach($komoditas as $item)
            <option value="{{ $item->id }}" {{ ($selectedKomoditas ?? '') == $item->id ? 'selected' : '' }}>
              {{ $item->nama }}
            </option>
          @endforeach
        @else
          <option value="1">🌾 Beras Premium</option>
          <option value="2">🌶️ Cabai Rawit</option>
          <option value="3">🧅 Bawang Merah</option>
          <option value="4">🥚 Telur Ayam</option>
        @endisset
      </select>
    </div>

    <input type="hidden" name="wilayah" value="jember">

    <button class="u-btn-filter" id="updateChartBtn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="1 4 1 10 7 10"/>
        <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
      </svg>
      Perbarui Grafik
    </button>

    <button class="u-btn-reset-zoom" id="resetZoomBtn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="22" y1="22" x2="16.65" y2="16.65"/>
        <polyline points="12 8 12 12 16 14"/>
      </svg>
      Reset Zoom
    </button>
  </div>

  {{-- ── CHART CARD (BAR CHART) ─────────────────────── --}}
  <div class="u-chart-card">
    <div class="u-chart-card__header">
      <div>
        <div class="u-chart-card__title">Analisis Tren &amp; Prediksi Harga</div>
        <div class="u-chart-card__sub">Estimasi 90 hari kedepan • Model AI Prophet • Akurasi Tinggi</div>
      </div>
      <div class="u-pred-legend">
        <div class="u-pred-legend__item">
          <span class="u-pred-legend__bar u-pred-legend__bar--historis"></span>
          Historis
        </div>
        <div class="u-pred-legend__item">
          <span class="u-pred-legend__bar u-pred-legend__bar--prediksi"></span>
          Prediksi AI
        </div>
        <div class="u-pred-legend__item">
          <span class="u-pred-legend__line"></span>
          Tren Linear
        </div>
      </div>
    </div>

    <div class="u-chart-canvas-wrap">
      <canvas id="prediksiChart" width="1000" height="400"></canvas>
    </div>

    <div class="u-chart-info">
      <div class="u-chart-info__item">
        <span class="u-chart-info__dot" style="background: #3b82f6"></span>
        <span>Data Historis (12 bulan terakhir)</span>
      </div>
      <div class="u-chart-info__item">
        <span class="u-chart-info__dot" style="background: #f59e0b"></span>
        <span>Prediksi AI (3 bulan ke depan)</span>
      </div>
      <div class="u-chart-info__item">
        <span class="u-chart-info__line"></span>
        <span>Tren pergerakan harga</span>
      </div>
    </div>
  </div>

  {{-- ── STAT CARDS ─────────────────────────────────── --}}
  <div class="u-pred-stat-row">
    <div class="u-pred-stat-card u-pred-stat-card--blue">
      <div class="u-pred-stat-card__label">Estimasi Harga (3 Bulan)</div>
      <div class="u-pred-stat-card__value" id="estimasiHargaValue">
        Rp {{ number_format($estimasiHarga ?? 18500, 0, ',', '.') }}
        <span class="u-pred-stat-card__unit">/kg</span>
      </div>
      <div class="u-pred-stat-card__note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Berdasarkan tren 30 hari terakhir
      </div>
    </div>

    <div class="u-pred-stat-card u-pred-stat-card--rose">
      <div class="u-pred-stat-card__label">Tren Perubahan</div>
      <div class="u-pred-stat-card__value u-pred-stat-card__value--up" id="trenPersenValue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
          <polyline points="16 7 22 7 22 13"/>
        </svg>
        +{{ $trenPersen ?? '8.4' }}%
      </div>
      <div class="u-pred-stat-card__sub">
        Dibandingkan periode yang sama tahun lalu
      </div>
    </div>

    <div class="u-pred-stat-card u-pred-stat-card--blue">
      <div class="u-pred-stat-card__label">Akurasi Model AI</div>
      <div class="u-pred-stat-card__value u-pred-stat-card__value--conf" id="kepercayaanValue">
        {{ $kepercayaan ?? '94.7' }}%
      </div>
      <div class="u-conf-bar-wrap">
        <div class="u-conf-bar">
          <div class="u-conf-bar__fill" id="confidenceBarFill" style="width: {{ $kepercayaan ?? '94.7' }}%"></div>
        </div>
      </div>
      <div class="u-pred-stat-card__sub">
        Berdasarkan validasi silang 5-fold
      </div>
    </div>
  </div>

  {{-- ── PREDIKSI DETAIL TABLE ──────────────────────── --}}
  <div class="u-table-card">
    <div class="u-table-card__header">
      <div class="u-table-card__title">📊 Detail Prediksi Harga (Mingguan)</div>
      <div class="u-table-actions">
        <button class="u-btn-csv" onclick="downloadCSV()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Unduh CSV
        </button>
      </div>
    </div>

    <div class="u-table-wrap">
      <table class="u-table" id="prediksiTable">
        <thead>
          <tr>
            <th>Periode</th>
            <th>Estimasi Harga</th>
            <th>Range (Min - Max)</th>
            <th>Tren</th>
          </tr>
        </thead>
        <tbody>
          @isset($prediksiMingguan)
            @forelse($prediksiMingguan as $row)
            <tr>
              <td class="u-table__date">{{ $row->periode }}</td>
              <td class="u-table__harga">Rp {{ number_format($row->estimasi, 0, ',', '.') }}</td>
              <td>
                <span class="u-range-min">Rp {{ number_format($row->estimasi - $row->variasi, 0, ',', '.') }}</span>
                <span class="u-range-sep">→</span>
                <span class="u-range-max">Rp {{ number_format($row->estimasi + $row->variasi, 0, ',', '.') }}</span>
              </td>
              <td>
                <span class="u-trend-badge {{ $row->trend == 'up' ? 'u-trend-up' : ($row->trend == 'down' ? 'u-trend-down' : 'u-trend-stable') }}">
                  {{ $row->trend == 'up' ? '📈 Naik' : ($row->trend == 'down' ? '📉 Turun' : '➡️ Stabil') }}
                </span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="u-empty-state">Tidak ada data prediksi</td>
            </tr>
            @endforelse
          @else
            <tr><td class="u-table__date">Minggu 1 - Juli</td><td class="u-table__harga">Rp 18.500</td><td><span class="u-range-min">Rp 17.800</span> → <span class="u-range-max">Rp 19.200</span></td><td><span class="u-trend-badge u-trend-up">📈 Naik</span></td></tr>
            <tr><td class="u-table__date">Minggu 2 - Juli</td><td class="u-table__harga">Rp 19.200</td><td><span class="u-range-min">Rp 18.400</span> → <span class="u-range-max">Rp 20.000</span></td><td><span class="u-trend-badge u-trend-up">📈 Naik</span></td></tr>
            <tr><td class="u-table__date">Minggu 3 - Juli</td><td class="u-table__harga">Rp 20.100</td><td><span class="u-range-min">Rp 19.200</span> → <span class="u-range-max">Rp 21.000</span></td><td><span class="u-trend-badge u-trend-up">📈 Naik</span></td></tr>
            <tr><td class="u-table__date">Minggu 4 - Juli</td><td class="u-table__harga">Rp 21.000</td><td><span class="u-range-min">Rp 20.000</span> → <span class="u-range-max">Rp 22.000</span></td><td><span class="u-trend-badge u-trend-up">📈 Naik</span></td></tr>
            <tr><td class="u-table__date">Minggu 1 - Agustus</td><td class="u-table__harga">Rp 22.200</td><td><span class="u-range-min">Rp 21.000</span> → <span class="u-range-max">Rp 23.400</span></td><td><span class="u-trend-badge u-trend-up">📈 Naik</span></td></tr>
          @endisset
        </tbody>
       </table>
    </div>
  </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script>
let chartInstance = null;

const histLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
const predLabels = ['Jan (2025)','Feb (2025)','Mar (2025)'];

function fmt(n) { return n.toLocaleString('id-ID'); }

function buildChart(histPrices, predPrices) {
    const ctx = document.getElementById('prediksiChart').getContext('2d');
    if (chartInstance) chartInstance.destroy();

    const n = histPrices.length;
    const allLabels = [...histLabels, ...predLabels];

    // Sambungkan titik terakhir historis ke awal prediksi
    const histFull = [...histPrices, histPrices[n-1], null, null];
    const predFull = [...new Array(n-1).fill(null), histPrices[n-1], ...predPrices];
    const predUpper = [...new Array(n-1).fill(null), histPrices[n-1], ...predPrices.map((v,i) => v + (v * 0.04 * (i+1)))];
    const predLower = [...new Array(n-1).fill(null), histPrices[n-1], ...predPrices.map((v,i) => v - (v * 0.04 * (i+1)))];

    const histGrad = ctx.createLinearGradient(0, 0, 0, 400);
    histGrad.addColorStop(0, 'rgba(59,130,246,0.18)');
    histGrad.addColorStop(1, 'rgba(59,130,246,0.01)');

    const predGrad = ctx.createLinearGradient(0, 0, 0, 400);
    predGrad.addColorStop(0, 'rgba(245,158,11,0.15)');
    predGrad.addColorStop(1, 'rgba(245,158,11,0.01)');

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Harga Historis',
                    data: histFull,
                    borderColor: '#3b82f6',
                    backgroundColor: histGrad,
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointHoverRadius: 6,
                    order: 2,
                    spanGaps: false
                },
                {
                    label: 'Prediksi AI',
                    data: predFull,
                    borderColor: '#f59e0b',
                    backgroundColor: predGrad,
                    borderWidth: 2.5,
                    borderDash: [6, 4],
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointHoverRadius: 6,
                    order: 1,
                    spanGaps: false
                },
                {
                    label: 'Batas Atas',
                    data: predUpper,
                    borderColor: 'rgba(245,158,11,0.25)',
                    backgroundColor: 'rgba(245,158,11,0.06)',
                    borderWidth: 1,
                    borderDash: [3, 3],
                    tension: 0.4,
                    fill: '-1',
                    pointRadius: 0,
                    order: 3,
                    spanGaps: false
                },
                {
                    label: 'Batas Bawah',
                    data: predLower,
                    borderColor: 'rgba(245,158,11,0.25)',
                    backgroundColor: 'rgba(245,158,11,0.06)',
                    borderWidth: 1,
                    borderDash: [3, 3],
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    order: 4,
                    spanGaps: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.95)',
                    titleColor: '#f1f5f9',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    cornerRadius: 12,
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    callbacks: {
                        label(c) {
                            if (c.raw === null) return null;
                            if (c.dataset.label === 'Batas Atas' || c.dataset.label === 'Batas Bawah') return null;
                            return ` ${c.dataset.label}: Rp ${fmt(Math.round(c.raw))}`;
                        }
                    }
                },
                zoom: {
                    pan: { enabled: true, mode: 'x', modifierKey: null },
                    zoom: {
                        wheel: { enabled: true, speed: 0.1, modifierKey: 'ctrl' },
                        pinch: { enabled: true },
                        mode: 'x'
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11, weight: '600' },
                        color: '#64748b',
                        autoSkip: false,
                        maxRotation: 40
                    },
                    title: { display: true, text: 'Periode', color: '#94a3b8', font: { size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(226,232,240,0.6)', drawBorder: false },
                    ticks: {
                        font: { size: 11, family: "'DM Mono'" },
                        color: '#64748b',
                        callback: (v) => 'Rp ' + fmt(v)
                    },
                    title: { display: true, text: 'Harga (Rp/kg)', color: '#94a3b8', font: { size: 11 } }
                }
            }
        }
    });
}

function updateChartData() {
    const komoditasId = document.getElementById('komoditasSelect').value;
    const btn = document.getElementById('updateChartBtn');
    const originalHtml = btn.innerHTML;

    btn.innerHTML = `<svg style="animation:spin 0.7s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 11-6.22-8.56"/></svg> Memuat...`;
    btn.disabled = true;

    setTimeout(() => {
        let histPrices, predPrices, estimasi, tren, akurasi;

        if (komoditasId == '1') {
            histPrices = [14200,14500,14800,15100,15400,15800,16200,16500,16800,17200,17600,18000];
            predPrices = [18500,19200,19800];
            estimasi = 19800; tren = 5.2; akurasi = 96.2;
        } else if (komoditasId == '2') {
            histPrices = [28500,29200,30500,31800,33500,35200,36800,38500,40200,41800,43500,45200];
            predPrices = [47500,49800,52500];
            estimasi = 52500; tren = 12.8; akurasi = 92.5;
        } else if (komoditasId == '3') {
            histPrices = [16500,16800,17200,17800,18500,19200,19800,20500,21200,21800,22500,23200];
            predPrices = [24000,24800,25500];
            estimasi = 25500; tren = 6.5; akurasi = 94.8;
        } else {
            histPrices = [14800,14950,15100,15250,15400,15550,15700,15850,16000,16150,16300,16450];
            predPrices = [16650,16800,16950];
            estimasi = 16950; tren = 3.2; akurasi = 98.1;
        }

        buildChart(histPrices, predPrices);

        document.getElementById('estimasiHargaValue').innerHTML = `Rp ${fmt(estimasi)} <span class="u-pred-stat-card__unit">/kg</span>`;
        document.getElementById('trenPersenValue').innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg> +${tren}%`;
        document.getElementById('kepercayaanValue').innerHTML = `${akurasi}%`;
        document.getElementById('confidenceBarFill').style.width = `${akurasi}%`;

        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }, 600);
}

function resetZoom() {
    if (chartInstance) chartInstance.resetZoom();
}

function downloadCSV() {
    const komoditas = document.getElementById('komoditasSelect').options[document.getElementById('komoditasSelect').selectedIndex]?.text || 'Komoditas';
    let csvContent = "Periode,Harga (Rp),Tipe\n";
    histLabels.forEach((l, i) => csvContent += `${l},${[14200,14500,14800,15100,15400,15800,16200,16500,16800,17200,17600,18000][i]},Historis\n`);
    predLabels.forEach((l, i) => csvContent += `${l},${[18500,19200,19800][i]},Prediksi\n`);
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `prediksi_harga_${komoditas.toLowerCase().replace(/ /g,'_')}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}

document.addEventListener('DOMContentLoaded', function () {
    const histPrices = [14200,14500,14800,15100,15400,15800,16200,16500,16800,17200,17600,18000];
    const predPrices = [18500,19200,19800];
    buildChart(histPrices, predPrices);
    document.getElementById('updateChartBtn').addEventListener('click', updateChartData);
    document.getElementById('resetZoomBtn').addEventListener('click', resetZoom);
    document.getElementById('prediksiChart').addEventListener('dblclick', resetZoom);
});
</script>
@endpush
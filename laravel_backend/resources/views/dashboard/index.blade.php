@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-text-primary-light dark:text-text-primary-dark text-3xl font-black tracking-tight mb-2">
                Monitoring Harga Komoditas
            </h1>
            <p class="text-text-secondary-light dark:text-text-secondary-dark text-base">
                Pantau harga pasar real-time dan prediksi tren masa depan menggunakan AI.
            </p>
        </div>
        <div class="flex items-center gap-2 text-sm text-text-secondary-light dark:text-text-secondary-dark bg-white dark:bg-surface-dark px-4 py-2 rounded-full border border-border-light dark:border-border-dark shadow-sm">
            <span class="material-symbols-outlined text-green-600 text-[18px]">update</span>
            <span id="last-updated">Terakhir diperbarui: baru saja</span>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-8">
        <form id="filter-form" onsubmit="applyFilter(event)">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

{{-- Komoditas --}}
<div class="flex flex-col gap-2">
    <label class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[18px]">category</span>
        Pilih Komoditas
    </label>
    <div class="relative">
        <select id="select-komoditas" name="komoditas"
                class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all appearance-none cursor-pointer">
            @foreach($komoditas as $item)
                <option value="{{ $item->_id }}" {{ $selectedKomoditas == $item->_id ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">expand_more</span>
    </div>
</div>

{{-- Daerah --}}
<div class="flex flex-col gap-2">
    <label class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
        Pilih Daerah
    </label>
    <div class="relative">
        <select id="select-daerah" name="daerah"
                class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all appearance-none cursor-pointer">
            @foreach($daerah as $item)
                <option value="{{ $item }}" {{ $selectedDaerah == $item ? 'selected' : '' }}>
                    {{ $item }}
                </option>
            @endforeach
        </select>
        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">expand_more</span>
    </div>
</div>

                {{-- Button --}}
                <div class="flex flex-col justify-end">
                    <button type="submit" id="filter-btn"
                            class="h-11 w-full bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined" id="filter-icon">search</span>
                        <span id="filter-text">Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        {{-- Harga Terbaru --}}
        <div class="observe-fade delay-100 card-hover bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark hover:border-blue-200 dark:hover:border-blue-800 transition-colors group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">payments</span>
                </div>
                <span id="badge-perubahan" class="flex items-center text-xs font-bold text-green-600 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-full">
                    <span class="material-symbols-outlined text-[14px] mr-0.5">trending_up</span>
                    <span id="val-persentase">{{ $stats['persentase'] ?? '+0%' }}</span>
                </span>
            </div>
            <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium mb-1">
                Harga Terbaru (<span id="label-komoditas">{{ $stats['komoditas'] ?? 'Beras' }}</span>)
            </p>
            <h3 class="text-text-primary-light dark:text-text-primary-dark text-2xl font-bold">
                <span id="val-harga">{{ $stats['harga_terbaru'] ?? 'Rp 0' }}</span>
                <span class="text-sm font-normal text-text-secondary-light dark:text-text-secondary-dark">/kg</span>
            </h3>
        </div>

        {{-- Perubahan Harian --}}
       <div class="observe-fade delay-200 card-hover bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark hover:border-blue-200 dark:hover:border-blue-800 transition-colors group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">show_chart</span>
                </div>
                <span id="badge-perubahan-harian" class="flex items-center text-xs font-bold text-green-600 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-full">
                    <span class="material-symbols-outlined text-[14px] mr-0.5">trending_up</span>
                    <span id="val-persentase-harian">{{ $stats['persentase_harian'] ?? '+0%' }}</span>
                </span>
            </div>
            <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium mb-1">Perubahan Harian</p>
            <h3 class="text-text-primary-light dark:text-text-primary-dark text-2xl font-bold" id="val-perubahan-harian">
                {{ $stats['perubahan_harian'] ?? '+ Rp 0' }}
            </h3>
        </div>

        {{-- Volatilitas --}}
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark hover:border-blue-200 dark:hover:border-blue-800 transition-colors group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">water_ec</span>
                </div>
                <span id="badge-volatilitas" class="text-xs font-bold text-amber-600 bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-full">
                    {{ $stats['status_volatilitas_label'] ?? 'Stabil' }}
                </span>
            </div>
            <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium mb-1">Status Volatilitas</p>
            <h3 class="text-text-primary-light dark:text-text-primary-dark text-2xl font-bold" id="val-volatilitas">
                {{ $stats['volatilitas'] ?? 'Rendah' }}
            </h3>
        </div>
    </div>

    {{-- GRAFIK + PREDIKSI --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- Grafik --}}
        <div class="lg:col-span-3 bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-8 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-text-primary-light dark:text-text-primary-dark text-xl font-bold">Grafik Harga Historis</h3>
                    <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm" id="chart-subtitle">
                        Pergerakan harga dalam 30 hari terakhir
                    </p>
                </div>
                <div class="flex gap-2">
                    <button onclick="setChartPeriod(30)" id="btn-30"
                            class="bg-primary text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                        30 Hari
                    </button>
                    <button onclick="setChartPeriod(90)" id="btn-90"
                            class="bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-white dark:hover:bg-surface-dark transition-colors">
                        90 Hari
                    </button>
                    <button onclick="downloadChart()" title="Download grafik"
                            class="text-primary hover:bg-primary-light dark:hover:bg-primary-dark/20 p-2 rounded-lg transition-colors">
                        <span class="material-symbols-outlined">download</span>
                    </button>
                </div>
            </div>

            {{-- Chart Loading State --}}
            <div id="chart-loading" class="flex-1 flex items-center justify-center min-h-[320px]">
                <div class="text-center text-text-secondary-light dark:text-text-secondary-dark">
                    <span class="material-symbols-outlined text-4xl animate-pulse">bar_chart</span>
                    <p class="text-sm mt-2">Memuat data grafik...</p>
                </div>
            </div>

            <div id="chart-container" class="hidden min-h-[320px]">
                <canvas id="price-chart"></canvas>
            </div>
        </div>

        {{-- Prediksi --}}
        <div class="lg:col-span-1 bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-8 flex flex-col">
            <div class="flex flex-col gap-1 mb-6">
                <div class="flex items-center gap-2">
                    <h3 class="text-text-primary-light dark:text-text-primary-dark text-xl font-bold">Prediksi</h3>
                    <span class="bg-primary/10 text-primary text-[10px] font-bold px-2 py-0.5 rounded-full border border-primary/20">AI PROPHET</span>
                </div>
                <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm">Estimasi tren harga ke depan</p>
            </div>

            <div class="flex-1 flex flex-col gap-4">
                @foreach(['besok' => 'Besok', '3hari' => '3 Hari', '7hari' => '7 Hari'] as $key => $label)
                <div class="flex items-center justify-between p-4 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark hover:border-primary/30 transition-colors">
                    <div class="flex flex-col">
                        <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark uppercase font-bold tracking-wider">{{ $label }}</span>
                        <span class="font-bold text-lg text-text-primary-light dark:text-text-primary-dark" id="pred-{{ $key }}">
                            {{ $prediksi[$key]['harga'] ?? 'Rp —' }}
                        </span>
                    </div>
                    <span id="pred-{{ $key }}-pct"
                          class="text-green-600 text-xs font-bold bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded">
                        {{ $prediksi[$key]['persentase'] ?? '—' }}
                    </span>
                </div>
                @endforeach

                {{-- Mini Tren Chart --}}
                <div class="h-32 mt-auto w-full relative rounded-xl overflow-hidden bg-primary/5 border border-primary/10">
                    <svg class="absolute inset-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 50">
                        <defs>
                            <linearGradient id="miniGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#137fec" stop-opacity="0.2"/>
                                <stop offset="100%" stop-color="#137fec" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,45 C20,40 50,20 100,5" fill="none" stroke="#137fec" stroke-dasharray="2,1" stroke-linecap="round" stroke-width="2"/>
                        <polygon fill="url(#miniGradient)" points="0,50 100,50 100,5 0,45"/>
                    </svg>
                    <div id="tren-label" class="absolute bottom-3 right-3 text-[11px] font-bold text-primary bg-white dark:bg-background-dark px-2.5 py-1 rounded-full shadow-sm border border-primary/10">
                        {{ $prediksi['tren'] ?? 'Tren Naik Terdeteksi' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER INFO --}}
    <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 p-6 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined">database</span>
            </div>
            <div>
                <h4 class="font-bold text-text-primary-light dark:text-text-primary-dark">Sumber Data Terpercaya</h4>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark leading-relaxed">
                    Data dikumpulkan secara otomatis melalui integrasi API MongoDB dan dianalisis menggunakan algoritma Prophet.
                </p>
            </div>
        </div>
        <div class="flex gap-3 shrink-0">
            <button onclick="unduhLaporan()"
                    class="bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-lg font-bold text-sm transition-colors shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                Unduh Laporan PDF
            </button>
            <a href="{{ route('api.docs') }}"
               class="bg-white dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark px-6 py-2.5 rounded-lg font-bold text-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">code</span>
                API Dokumentasi
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const DASHBOARD_DATA = {
    chart_data_30: @json($chartData30 ?? []),
    chart_data_90: @json($chartData90 ?? []),
};

let currentPeriod = 30;
let priceChart    = null;

document.addEventListener('DOMContentLoaded', () => {
    renderChart(DASHBOARD_DATA.chart_data_30, 30);
    updateLastUpdated();
});

// ── APPLY FILTER (AJAX) ──────────────────────────────────────────
async function applyFilter(e) {
    e.preventDefault();
    const komoditas = document.getElementById('select-komoditas').value;
    const daerah    = document.getElementById('select-daerah').value;

    setFilterLoading(true);

    try {
        const res = await fetch('{{ route("dashboard.filter") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body:    JSON.stringify({ komoditas, daerah }),
        });

        if (!res.ok) throw new Error();
        const data = await res.json();

        updateStatCards(data.stats);
        updatePrediksi(data.prediksi);
        renderChart(data.chart_data, currentPeriod);
        showToast('Data berhasil diperbarui!', 'success');
        updateLastUpdated();

    } catch {
        showToast('Gagal memuat data. Coba lagi.', 'error');
    } finally {
        setFilterLoading(false);
    }
}

// ── TOGGLE 30 / 90 HARI ─────────────────────────────────────────
async function setChartPeriod(days) {
    currentPeriod = days;

    const active   = 'bg-primary text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors';
    const inactive = 'bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-white transition-colors';

    document.getElementById('btn-30').className = days === 30 ? active : inactive;
    document.getElementById('btn-90').className = days === 90 ? active : inactive;
    document.getElementById('chart-subtitle').textContent = `Pergerakan harga dalam ${days} hari terakhir`;

    const komoditas = document.getElementById('select-komoditas').value;
    const daerah    = document.getElementById('select-daerah').value;

    try {
        showChartLoading(true);
        const res  = await fetch(`{{ route("dashboard.chart-data") }}?komoditas=${komoditas}&daerah=${daerah}&periode=${days}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();
        renderChart(data.chart_data, days);
    } catch {
        showToast('Gagal memuat grafik.', 'error');
        showChartLoading(false);
    }
}

// ── RENDER CHART.JS ──────────────────────────────────────────────
function renderChart(chartData, days) {
    showChartLoading(false);

    if (!chartData?.labels?.length) { showChartEmpty(); return; }

    const ctx    = document.getElementById('price-chart').getContext('2d');
    const isDark = document.documentElement.classList.contains('dark');

    if (priceChart) priceChart.destroy();

    priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels:   chartData.labels,
            datasets: [{
                label:                'Harga (Rp/kg)',
                data:                 chartData.values,
                borderColor:          '#137fec',
                backgroundColor:      'rgba(19,127,236,0.08)',
                borderWidth:          2.5,
                pointRadius:          4,
                pointHoverRadius:     7,
                pointBackgroundColor: '#ffffff',
                pointBorderColor:     '#137fec',
                pointBorderWidth:     2,
                fill:                 true,
                tension:              0.4,
            }],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1a2634' : '#ffffff',
                    titleColor:      isDark ? '#e2e8f0' : '#0d141b',
                    bodyColor:       isDark ? '#94a3b8' : '#4c739a',
                    borderColor:     isDark ? '#2d3748' : '#e7edf3',
                    borderWidth:     1,
                    padding:         12,
                    callbacks: { label: ctx => `Rp ${ctx.parsed.y.toLocaleString('id-ID')}/kg` },
                },
            },
            scales: {
                x: {
                    grid:  { display: false },
                    ticks: { color: isDark ? '#94a3b8' : '#4c739a', font: { size: 11 }, maxTicksLimit: days === 30 ? 7 : 10 },
                    border: { display: false },
                },
                y: {
                    grid:  { color: isDark ? 'rgba(45,55,72,0.5)' : 'rgba(231,237,243,0.8)' },
                    ticks: { color: isDark ? '#94a3b8' : '#4c739a', font: { size: 11 }, callback: v => 'Rp ' + (v/1000).toFixed(0) + 'k' },
                    border: { display: false },
                },
            },
        },
    });
}

// ── UPDATE STAT CARDS ────────────────────────────────────────────
function updateStatCards(stats) {
    if (!stats) return;
    document.getElementById('val-harga').textContent             = stats.harga_terbaru          ?? '—';
    document.getElementById('val-persentase').textContent        = stats.persentase             ?? '—';
    document.getElementById('label-komoditas').textContent       = stats.komoditas              ?? '—';
    document.getElementById('val-perubahan-harian').textContent  = stats.perubahan_harian       ?? '—';
    document.getElementById('val-persentase-harian').textContent = stats.persentase_harian      ?? '—';
    document.getElementById('val-volatilitas').textContent       = stats.volatilitas            ?? '—';
    document.getElementById('badge-volatilitas').textContent     = stats.status_volatilitas_label ?? '—';
}

// ── UPDATE PREDIKSI ──────────────────────────────────────────────
function updatePrediksi(prediksi) {
    if (!prediksi) return;
    ['besok','3hari','7hari'].forEach(k => {
        const hEl = document.getElementById(`pred-${k}`);
        const pEl = document.getElementById(`pred-${k}-pct`);
        if (hEl) hEl.textContent = prediksi[k]?.harga      ?? '—';
        if (pEl) pEl.textContent = prediksi[k]?.persentase ?? '—';
    });
    const tren = document.getElementById('tren-label');
    if (tren && prediksi.tren) tren.textContent = prediksi.tren;
}

// ── UNDUH PDF ────────────────────────────────────────────────────
function unduhLaporan() {
    const komoditas = document.getElementById('select-komoditas').value;
    const daerah    = document.getElementById('select-daerah').value;
    showToast('Menyiapkan laporan PDF...', 'info');
    window.location.href = `{{ route("dashboard.export-pdf") }}?komoditas=${komoditas}&daerah=${daerah}`;
}

// ── DOWNLOAD CHART PNG ───────────────────────────────────────────
function downloadChart() {
    if (!priceChart) return;
    const a = document.createElement('a');
    a.download = 'grafik-harga-simopang.png';
    a.href     = priceChart.toBase64Image();
    a.click();
    showToast('Grafik berhasil diunduh!', 'success');
}

// ── HELPERS ──────────────────────────────────────────────────────
function setFilterLoading(loading) {
    const btn = document.getElementById('filter-btn');
    document.getElementById('filter-icon').textContent = loading ? 'hourglass_top' : 'search';
    document.getElementById('filter-text').textContent = loading ? 'Memuat...'     : 'Terapkan Filter';
    btn.disabled = loading;
    btn.classList.toggle('opacity-75', loading);
    btn.classList.toggle('cursor-not-allowed', loading);
}

function showChartLoading(show) {
    document.getElementById('chart-loading').classList.toggle('hidden', !show);
    document.getElementById('chart-container').classList.toggle('hidden', show);
}

function showChartEmpty() {
    document.getElementById('chart-loading').innerHTML = `
        <div class="text-center text-text-secondary-light dark:text-text-secondary-dark">
            <span class="material-symbols-outlined text-4xl">sentiment_dissatisfied</span>
            <p class="text-sm mt-2">Tidak ada data untuk periode ini</p>
        </div>`;
    document.getElementById('chart-loading').classList.remove('hidden');
    document.getElementById('chart-container').classList.add('hidden');
}

function updateLastUpdated() {
    const now = new Date();
    document.getElementById('last-updated').textContent =
        `Terakhir diperbarui: ${now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
}
</script>
@endpush
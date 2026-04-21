@extends('layouts.app')

@section('title', 'Prediksi Harga')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- BREADCRUMB + HEADER --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs font-medium text-primary mb-2">
            <a href="{{ route('dashboard') }}" class="hover:underline">Beranda</a>
            <span>/</span>
            <span class="text-text-secondary-light dark:text-text-secondary-dark">Prediksi Harga AI</span>
        </div>
        <h1 class="text-text-primary-light dark:text-text-primary-dark text-3xl font-black tracking-tight mb-2">
            Prediksi Harga Komoditas
        </h1>
        <p class="text-text-secondary-light dark:text-text-secondary-dark text-base max-w-2xl">
            Pantau pergerakan harga historis dan perkiraan harga masa depan menggunakan model AI Prophet untuk perencanaan stok dan belanja yang lebih akurat.
        </p>
    </div>

    {{-- FILTER --}}
    <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl p-4 mb-8 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider px-1">
                    Pilih Komoditas
                </label>
                <div class="relative">
                    <select id="pred-komoditas"
                            class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold focus:ring-2 focus:ring-primary appearance-none cursor-pointer">
                        <option value="beras_premium">Beras Premium</option>
                        <option value="beras_medium">Beras Medium</option>
                        <option value="cabai_merah">Cabai Merah Besar</option>
                        <option value="cabai_rawit">Cabai Rawit</option>
                        <option value="bawang_merah">Bawang Merah</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">expand_more</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider px-1">
                    Pilih Wilayah
                </label>
                <div class="relative">
                    <select id="pred-wilayah"
                            class="w-full h-11 pl-4 pr-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold focus:ring-2 focus:ring-primary appearance-none cursor-pointer">
                        <option value="jatim">Jawa Timur</option>
                        <option value="jateng">Jawa Tengah</option>
                        <option value="jabar">Jawa Barat</option>
                        <option value="jakarta">DKI Jakarta</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark pointer-events-none">expand_more</span>
                </div>
            </div>
            <div class="flex items-end">
                <button onclick="perbaruiGrafik()"
                        id="btn-perbarui"
                        class="w-full h-11 bg-primary hover:bg-primary-dark text-white font-bold rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-xl" id="btn-perbarui-icon">refresh</span>
                    <span id="btn-perbarui-text">Perbarui Grafik</span>
                </button>
            </div>
        </div>
    </div>

    {{-- GRAFIK --}}
    <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl p-6 mb-8 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">
                    Analisis Tren & Prediksi Harga
                </h3>
                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">
                    Estimasi 90 hari kedepan menggunakan AI Prophet Modeling
                </p>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-0.5 bg-primary inline-block"></span>
                    <span class="text-text-secondary-light dark:text-text-secondary-dark">Historis</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 border-t-2 border-dashed border-primary inline-block"></span>
                    <span class="text-text-secondary-light dark:text-text-secondary-dark">Prediksi AI</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-primary/10 border border-primary/20 rounded-sm inline-block"></span>
                    <span class="text-text-secondary-light dark:text-text-secondary-dark">Confidence Interval</span>
                </div>
            </div>
        </div>

        {{-- Chart Canvas --}}
        <div class="relative h-[350px] w-full">
            <canvas id="prediksi-chart"></canvas>
        </div>

        {{-- X Axis Labels --}}
        <div class="flex justify-between mt-4 px-2" id="chart-labels">
            @foreach(['JAN','FEB','MAR','APR','MEI','JUN*','JUL*','AGU*'] as $label)
            <span class="text-[10px] font-bold {{ str_contains($label, '*') ? 'text-primary' : 'text-text-secondary-light dark:text-text-secondary-dark' }}">
                {{ $label }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        {{-- Estimasi Harga --}}
        <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark p-6 rounded-xl shadow-sm">
            <p class="text-[10px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-2">
                Estimasi Harga [Juli 2024]
            </p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-text-primary-light dark:text-text-primary-dark" id="stat-estimasi-harga">
                    Rp 15.240
                </h3>
                <span class="text-xs font-medium text-text-secondary-light dark:text-text-secondary-dark">/kg</span>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-primary">
                <span class="material-symbols-outlined text-sm">info</span>
                <span>Berdasarkan tren rata-rata wilayah</span>
            </div>
        </div>

        {{-- Tren Prediksi --}}
        <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark p-6 rounded-xl shadow-sm">
            <p class="text-[10px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-2">
                Tren Prediksi (30 Hari)
            </p>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1 text-red-500">
                    <span class="material-symbols-outlined font-black">trending_up</span>
                    <span class="text-2xl font-black" id="stat-tren">+4.2%</span>
                </div>
            </div>
            <div class="mt-4 text-[11px] text-text-secondary-light dark:text-text-secondary-dark" id="stat-tren-desc">
                Kenaikan diperkirakan berlanjut hingga akhir kuartal.
            </div>
        </div>

        {{-- Tingkat Kepercayaan --}}
        <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark p-6 rounded-xl shadow-sm">
            <p class="text-[10px] font-bold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-2">
                Tingkat Kepercayaan AI
            </p>
            <div class="flex items-center gap-4">
                <h3 class="text-2xl font-black text-primary" id="stat-akurasi">95.4%</h3>
                <div class="flex-grow bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div class="bg-primary h-full rounded-full transition-all duration-700" id="stat-akurasi-bar" style="width: 95.4%"></div>
                </div>
            </div>
            <div class="mt-4 text-[11px] text-text-secondary-light dark:text-text-secondary-dark">
                Model validasi silang dengan akurasi tinggi.
            </div>
        </div>
    </div>

    {{-- TABEL PREDIKSI MINGGUAN --}}
    <div class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-border-light dark:border-border-dark flex justify-between items-center">
            <h3 class="text-sm font-bold text-text-primary-light dark:text-text-primary-dark uppercase tracking-wide">
                Detail Angka Prediksi (Mingguan)
            </h3>
            <button onclick="unduhCSVPrediksi()"
                    class="text-primary text-xs font-bold flex items-center gap-1 hover:text-primary-dark transition-colors">
                <span class="material-symbols-outlined text-sm">download</span> Unduh CSV
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tabel-prediksi">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-border-light dark:border-border-dark">
                        <th class="px-6 py-3 text-[10px] font-black text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-widest">Minggu Ke-</th>
                        <th class="px-6 py-3 text-[10px] font-black text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-widest">Periode</th>
                        <th class="px-6 py-3 text-[10px] font-black text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-widest text-right">Estimasi Harga</th>
                        <th class="px-6 py-3 text-[10px] font-black text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-widest text-right">Variasi Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark" id="tbody-prediksi">
                    @php
                        $prediksiMingguan = [
                            ['minggu' => 'W1 - Jun', 'periode' => '01 Jun - 07 Jun', 'harga' => 'Rp 14.650', 'variasi' => '± Rp 120'],
                            ['minggu' => 'W2 - Jun', 'periode' => '08 Jun - 14 Jun', 'harga' => 'Rp 14.780', 'variasi' => '± Rp 140'],
                            ['minggu' => 'W3 - Jun', 'periode' => '15 Jun - 21 Jun', 'harga' => 'Rp 14.920', 'variasi' => '± Rp 165'],
                            ['minggu' => 'W4 - Jun', 'periode' => '22 Jun - 30 Jun', 'harga' => 'Rp 15.100', 'variasi' => '± Rp 190'],
                        ];
                    @endphp
                    @foreach($prediksiMingguan as $row)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 text-xs font-bold text-text-primary-light dark:text-text-primary-dark">{{ $row['minggu'] }}</td>
                        <td class="px-6 py-4 text-xs text-text-secondary-light dark:text-text-secondary-dark">{{ $row['periode'] }}</td>
                        <td class="px-6 py-4 text-xs font-black text-text-primary-light dark:text-text-primary-dark text-right">{{ $row['harga'] }}</td>
                        <td class="px-6 py-4 text-xs text-right text-text-secondary-light dark:text-text-secondary-dark">{{ $row['variasi'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let prediksiChart = null;

// Data per komoditas (statis)
const dataPrediksi = {
    beras_premium: { historis: [250,240,260,230,245,220,210], prediksi: [210,190,185,175,160], estimasi: 'Rp 15.240', tren: '+4.2%', akurasi: 95.4 },
    beras_medium:  { historis: [220,215,230,210,220,200,195], prediksi: [195,180,175,168,155], estimasi: 'Rp 14.100', tren: '+3.8%', akurasi: 93.1 },
    cabai_merah:   { historis: [180,200,190,210,195,220,230], prediksi: [230,250,260,270,290], estimasi: 'Rp 72.000', tren: '+6.1%', akurasi: 88.7 },
    cabai_rawit:   { historis: [160,180,175,200,190,210,220], prediksi: [220,240,255,265,280], estimasi: 'Rp 68.500', tren: '+5.8%', akurasi: 87.2 },
    bawang_merah:  { historis: [200,195,210,205,215,210,205], prediksi: [205,210,215,220,225], estimasi: 'Rp 33.200', tren: '+1.8%', akurasi: 91.5 },
};

document.addEventListener('DOMContentLoaded', () => {
    renderPrediksiChart('beras_premium');
});

// ── PERBARUI GRAFIK ──────────────────────────────────────────────
function perbaruiGrafik() {
    const komoditas = document.getElementById('pred-komoditas').value;

    document.getElementById('btn-perbarui-icon').textContent = 'hourglass_top';
    document.getElementById('btn-perbarui-text').textContent = 'Memuat...';
    document.getElementById('btn-perbarui').disabled = true;

    setTimeout(() => {
        renderPrediksiChart(komoditas);
        updateStatCards(komoditas);

        document.getElementById('btn-perbarui-icon').textContent = 'check_circle';
        document.getElementById('btn-perbarui-text').textContent = 'Diperbarui!';
        document.getElementById('btn-perbarui').disabled = false;

        showToast('Grafik berhasil diperbarui!', 'success');

        setTimeout(() => {
            document.getElementById('btn-perbarui-icon').textContent = 'refresh';
            document.getElementById('btn-perbarui-text').textContent = 'Perbarui Grafik';
        }, 2000);
    }, 800);
}

// ── RENDER CHART.JS ──────────────────────────────────────────────
function renderPrediksiChart(komoditas) {
    const data   = dataPrediksi[komoditas];
    const isDark = document.documentElement.classList.contains('dark');
    const ctx    = document.getElementById('prediksi-chart').getContext('2d');

    if (prediksiChart) prediksiChart.destroy();

    const labelsHistoris = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'];
    const labelsPrediksi = ['Jul', 'Agu*', 'Sep*', 'Okt*', 'Nov*'];
    const allLabels      = [...labelsHistoris, ...labelsPrediksi.slice(1)];

    // Gabungkan data: historis + null untuk prediksi, null untuk historis + prediksi
    const dataHistoris = [...data.historis, ...Array(labelsPrediksi.length - 1).fill(null)];
    const dataPred     = [...Array(labelsHistoris.length - 1).fill(null), ...data.prediksi];

    prediksiChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label:                'Historis',
                    data:                 dataHistoris,
                    borderColor:          '#137fec',
                    backgroundColor:      'rgba(19,127,236,0.06)',
                    borderWidth:          2.5,
                    pointRadius:          4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor:     '#137fec',
                    pointBorderWidth:     2,
                    fill:                 true,
                    tension:              0.4,
                    spanGaps:             false,
                },
                {
                    label:                'Prediksi AI',
                    data:                 dataPred,
                    borderColor:          '#137fec',
                    backgroundColor:      'rgba(19,127,236,0.08)',
                    borderWidth:          2.5,
                    borderDash:           [8, 4],
                    pointRadius:          4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor:     '#137fec',
                    pointBorderWidth:     2,
                    fill:                 true,
                    tension:              0.4,
                    spanGaps:             false,
                },
            ],
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
                    callbacks: {
                        label: ctx => ctx.parsed.y !== null
                            ? `${ctx.dataset.label}: Rp ${ctx.parsed.y.toLocaleString('id-ID')}`
                            : null,
                    },
                },
            },
            scales: {
                x: {
                    grid:  { display: false },
                    ticks: { color: isDark ? '#94a3b8' : '#4c739a', font: { size: 11 } },
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
function updateStatCards(komoditas) {
    const data = dataPrediksi[komoditas];
    if (!data) return;

    document.getElementById('stat-estimasi-harga').textContent = data.estimasi;
    document.getElementById('stat-tren').textContent           = data.tren;
    document.getElementById('stat-akurasi').textContent        = data.akurasi + '%';
    document.getElementById('stat-akurasi-bar').style.width    = data.akurasi + '%';
}

// ── UNDUH CSV ────────────────────────────────────────────────────
function unduhCSVPrediksi() {
    const rows = document.querySelectorAll('#tabel-prediksi tbody tr');
    let csv    = 'Minggu,Periode,Estimasi Harga,Variasi\n';

    rows.forEach(row => {
        const cols = row.querySelectorAll('td');
        csv += [
            cols[0]?.innerText.trim(),
            cols[1]?.innerText.trim(),
            cols[2]?.innerText.trim(),
            cols[3]?.innerText.trim(),
        ].join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(blob);
    a.download = 'prediksi-harga-simopang.csv';
    a.click();
    showToast('CSV prediksi berhasil diunduh!', 'success');
}
</script>
@endpush
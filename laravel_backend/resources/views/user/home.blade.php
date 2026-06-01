@extends('layouts.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard SIMOPANG')
@section('page-sub', 'Selamat datang. Pantau harga komoditas terkini dan prediksi tren harga.')

@section('content')

{{-- ── BANNER RANGE PREDIKSI ── --}}
@if(isset($globalTanggalMulai) && $globalTanggalMulai)
<div style="
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    border-radius: 12px;
    padding: 12px 20px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
">
    <div style="display:flex; align-items:center; gap:10px;">
        <div style="
            width: 8px; height: 8px;
            background: #4ade80;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        "></div>
        <span style="color:white; font-size:13px; font-weight:500;">
            <i class="fas fa-robot" style="margin-right:6px; opacity:0.8;"></i>
            Harga diprediksi AI (Holt-Winters) ·
            Data diperbarui otomatis setiap hari
        </span>
    </div>
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <span style="
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 4px 14px;
            color: white;
            font-size: 12px;
        ">
            <i class="fas fa-calendar-day" style="margin-right:5px;"></i>
            Harga Hari Ini:
            <strong>{{ $today->locale('id')->isoFormat('DD MMM YYYY') }}</strong>
        </span>
        <span style="
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 4px 14px;
            color: white;
            font-size: 12px;
        ">
            <i class="fas fa-calendar-range" style="margin-right:5px;"></i>
            Periode prediksi:
            <strong>{{ $globalTanggalMulai->locale('id')->isoFormat('DD MMM YYYY') }}</strong>
            →
            <strong>{{ $globalTanggalAkhir->locale('id')->isoFormat('DD MMM YYYY') }}</strong>
        </span>
    </div>
</div>
@endif

{{-- ── STAT CARDS ── --}}
<div class="stats-grid">

    <div class="stat-card">
        <div>
            <div class="stat-label">Rata-rata Harga Terkini</div>
            <div class="stat-value">
                Rp {{ number_format($rataRataHarga ?? 0, 0, ',', '.') }}
                <span style="font-size:14px; font-weight:400">/kg</span>
            </div>
            <div class="stat-change up">
                <i class="fas fa-chart-bar"></i>
                <span class="stat-change-sub">Rata-rata harga terkini seluruh komoditas</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-chart-bar"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Harga Tertinggi Terkini</div>
            <div class="stat-value">
                Rp {{ number_format($hargaTertinggi ?? 0, 0, ',', '.') }}
                <span style="font-size:14px; font-weight:400">/kg</span>
            </div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i>
                <span class="stat-change-sub">{{ $namaKomoditasTertinggi ?? '-' }}</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-arrow-trend-up"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Komoditas</div>
            <div class="stat-value">{{ $totalKomoditas ?? 0 }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-boxes-stacked"></i>
                <span class="stat-change-sub">Keseluruhan komoditas</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-boxes-stacked"></i></div>
    </div>

</div>

{{-- ── FILTER BAR ── --}}
<form method="GET" action="{{ url()->current() }}">
    <x-filter-bar
        placeholder="Cari komoditas..."
        :categories="$categoryList ?? []"
        :withDate="true"
        searchId="komoditasSearch"
        categoryId="komoditasCategory"
        dateId="komoditasDate"
    >
        <input type="hidden" name="daerah" value="1">
        <button type="submit" class="u-btn-filter">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Terapkan Filter
        </button>
    </x-filter-bar>
</form>

{{-- ── TABEL HARGA ── --}}
<div class="table-card">

    <div class="table-header">
        <div>
            <div class="table-title">
                Harga Komoditas
                <span style="
                    background: #dcfce7;
                    color: #166534;
                    font-size: 11px;
                    font-weight: 600;
                    padding: 2px 10px;
                    border-radius: 20px;
                    margin-left: 8px;
                    vertical-align: middle;
                ">
                    <span id="liveDot" style="
                        display:inline-block;
                        width:6px; height:6px;
                        background:#16a34a;
                        border-radius:50%;
                        margin-right:4px;
                        animation: pulse-dot 2s infinite;
                    "></span>
                    LIVE
                </span>
            </div>
            <div class="table-subtitle">
                Harga berubah otomatis setiap hari sesuai prediksi AI.
                @if(isset($globalTanggalAkhir) && $globalTanggalAkhir)
                    Prediksi tersedia hingga
                    <strong>{{ $globalTanggalAkhir->locale('id')->isoFormat('DD MMMM YYYY') }}</strong>.
                @endif
            </div>
        </div>
        <div class="table-actions">
            <div class="search-box">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" placeholder="Cari komoditas..." id="tableSearch">
            </div>
            <a href="/harga" class="view-all">Lihat Semua</a>
        </div>
    </div>

    @if(isset($recentPrices) && $recentPrices->count() > 0)
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Komoditas</th>
                    <th>Kategori</th>
                    <th>Harga (IDR)</th>
                    <th>
                        Tanggal Harga
                        <span style="
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 14px; height: 14px;
                            background: #e5e7eb;
                            color: #6b7280;
                            border-radius: 50%;
                            font-size: 9px;
                            font-weight: 700;
                            cursor: help;
                            margin-left: 4px;
                            vertical-align: middle;
                        " title="Tanggal data aktual = tanggal data nyata terakhir yang diketahui. Tanggal prediksi = tanggal sesuai forecast AI hari ini.">
                            ?
                        </span>
                    </th>
                    <th>Perubahan</th>
                    <th>Status Prediksi</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentPrices as $item)
                <tr>
                    {{-- Komoditas --}}
                    <td class="commodity-name">{{ $item->commodity_name ?? '-' }}</td>

                    {{-- Kategori --}}
                    <td class="region-text">{{ $item->kategori ?? '-' }}</td>

                    {{-- Harga --}}
                    <td class="price-text">
                        Rp {{ number_format($item->harga_sekarang ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- Tanggal Harga --}}
                    <td>
                        @if($item->tanggal_harga)
                            @if($item->belum_mulai)
                                <div>
                                    <span style="font-size: 12px; color: #374151; font-weight: 500;">
                                        <i class="fas fa-calendar-day" style="margin-right:4px; font-size:10px; color:#9ca3af;"></i>
                                        {{ \Carbon\Carbon::parse($item->tanggal_harga)->locale('id')->isoFormat('DD MMM YYYY') }}
                                    </span>
                                    <br>
                                    <span style="font-size: 10px; color: #9ca3af; font-style: italic;">
                                        data aktual terakhir
                                    </span>
                                </div>
                            @elseif($item->dalam_range)
                                <span style="font-size: 12px; color: #1d4ed8; font-weight: 600;">
                                    <i class="fas fa-calendar-day" style="margin-right:4px; font-size:10px;"></i>
                                    {{ \Carbon\Carbon::parse($item->tanggal_harga)->locale('id')->isoFormat('DD MMM YYYY') }}
                                </span>
                            @else
                                <span style="font-size: 12px; color: #92400e; font-weight: 400;">
                                    <i class="fas fa-calendar-day" style="margin-right:4px; font-size:10px;"></i>
                                    {{ \Carbon\Carbon::parse($item->tanggal_harga)->locale('id')->isoFormat('DD MMM YYYY') }}
                                </span>
                            @endif
                        @else
                            <span style="color:#9ca3af; font-size:12px;">-</span>
                        @endif
                    </td>

                    {{-- Perubahan --}}
                    <td>
                        @php
                            $selisih = $item->selisih ?? 0;
                            $persen  = $item->persen  ?? 0;
                        @endphp
                        @if($selisih > 0)
                            <span class="stat-change up" style="font-size:12px">
                                <i class="fas fa-arrow-up"></i> +{{ number_format(abs($persen), 2) }}%
                            </span>
                        @elseif($selisih < 0)
                            <span class="stat-change down" style="font-size:12px">
                                <i class="fas fa-arrow-down"></i> -{{ number_format(abs($persen), 2) }}%
                            </span>
                        @else
                            <span class="stat-change neutral" style="font-size:12px">
                                <i class="fas fa-minus"></i> 0%
                            </span>
                        @endif
                    </td>

                    {{-- Status Prediksi --}}
                    <td>
                        @if($item->dalam_range)
                            <span style="
                                background: #dcfce7;
                                color: #166534;
                                font-size: 11px;
                                font-weight: 600;
                                padding: 3px 10px;
                                border-radius: 20px;
                                white-space: nowrap;
                            ">
                                <i class="fas fa-check-circle" style="margin-right:3px;"></i>
                                Aktif
                            </span>
                        @elseif($item->sudah_kadaluarsa)
                            <span style="
                                background: #fef3c7;
                                color: #92400e;
                                font-size: 11px;
                                font-weight: 600;
                                padding: 3px 10px;
                                border-radius: 20px;
                                white-space: nowrap;
                            " title="Prediksi berakhir {{ $item->tanggal_akhir?->locale('id')->isoFormat('DD MMM YYYY') }}">
                                <i class="fas fa-clock" style="margin-right:3px;"></i>
                                Kadaluarsa
                            </span>
                        @elseif($item->belum_mulai)
                            <div>
                                <span style="
                                    background: #eff6ff;
                                    color: #1d4ed8;
                                    font-size: 11px;
                                    font-weight: 600;
                                    padding: 3px 10px;
                                    border-radius: 20px;
                                    white-space: nowrap;
                                    display: inline-block;
                                " title="Prediksi AI mulai {{ $item->tanggal_mulai?->locale('id')->isoFormat('DD MMM YYYY') }}. Harga sekarang adalah data aktual terakhir.">
                                    <i class="fas fa-calendar-check" style="margin-right:3px;"></i>
                                    Mulai {{ $item->tanggal_mulai?->locale('id')->isoFormat('DD MMM') }}
                                </span>
                                <br>
                                <span style="font-size: 10px; color: #9ca3af; font-style: italic; margin-top: 2px; display: inline-block;">
                                    harga: data aktual
                                </span>
                            </div>
                        @else
                            <span style="
                                background: #f3f4f6;
                                color: #6b7280;
                                font-size: 11px;
                                padding: 3px 10px;
                                border-radius: 20px;
                                white-space: nowrap;
                            ">
                                <i class="fas fa-minus" style="margin-right:3px;"></i>
                                Tidak ada data
                            </span>
                        @endif
                    </td>

                    {{-- Detail Button --}}
                    <td class="detail-cell">
                        <a href="{{ route('user.prediksi', ['komoditas' => $item->commodity_name]) }}"
                           class="btn-detail"
                           title="Lihat prediksi harga untuk {{ $item->commodity_name }}">
                            <i class="fas fa-chart-line"></i> Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div style="text-align:center; padding: 2rem; color: var(--text-muted);">
            <i class="fas fa-database" style="font-size: 48px; margin-bottom: 1rem;"></i>
            <p>Belum ada data harga.</p>
            <small>Silakan tambah data harga terlebih dahulu.</small>
        </div>
    @endif

    <div class="table-footer">
        <span class="table-footer-text">
            @if(isset($recentPrices) && $recentPrices->total() > 0)
                Menampilkan {{ $recentPrices->firstItem() }} - {{ $recentPrices->lastItem() }}
                dari {{ $recentPrices->total() }} data
            @else
                Belum ada data
            @endif
        </span>
        <div class="table-actions" style="gap:8px;">
            <form action="{{ route('user.downloadPdf') }}" method="GET" style="display: inline;">
                @if(request()->has('search'))
                    <input type="hidden" name="search" value="{{ request()->get('search') }}">
                @endif
                @if(request()->has('category'))
                    <input type="hidden" name="category" value="{{ request()->get('category') }}">
                @endif
                @if(request()->has('date'))
                    <input type="hidden" name="date" value="{{ request()->get('date') }}">
                @endif
                <button type="submit" class="u-btn-pdf" id="pdfBtn">
                    <i class="fas fa-download"></i> Unduh Laporan PDF
                </button>
            </form>

            @if(isset($recentPrices) && $recentPrices->total() > 0)
                {{ $recentPrices->links('components.pagination') }}
            @endif
        </div>
    </div>

</div>

{{-- ── PIE CHART ── --}}
<div class="table-card" style="margin-top: 1.5rem;">
    <div class="table-header">
        <div>
            <div class="table-title">Distribusi Rata-rata Harga per Kategori</div>
            <div class="table-subtitle">Perbandingan rata-rata harga komoditas berdasarkan kategori (data dari komoditas aktif).</div>
        </div>
    </div>

    @if(isset($chartLabels) && count($chartLabels) > 0)
    <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; flex-wrap: wrap; padding: 1.5rem 0;">
        <div style="position: relative; width: 300px; height: 300px; flex-shrink: 0;">
            <canvas id="categoryPieChart"></canvas>
        </div>
        <div id="chartLegend" style="display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 200px; max-width: 360px;"></div>
    </div>
    @else
    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
        <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 1rem;"></i>
        <p>Belum ada data kategori untuk ditampilkan</p>
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
        border: none;
        cursor: pointer;
    }

    .btn-detail:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        transform: translateY(-1px);
        color: white;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(59,130,246,0.4);
    }

    .btn-detail i {
        font-size: 11px;
    }

    .detail-cell {
        text-align: center;
        vertical-align: middle;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.3); }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
/* ── Pulse animation ── */
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.5; transform: scale(1.3); }
    }
`;
document.head.appendChild(style);

/* ── Live search tabel ── */
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
});

/* ── Auto-refresh tengah malam ── */
(function () {
    function msUntilMidnight() {
        const now  = new Date();
        const next = new Date(now);
        next.setHours(24, 0, 0, 0);
        return next - now;
    }

    setTimeout(function () {
        window.location.reload();
    }, msUntilMidnight());

    const countdownEl = document.createElement('div');
    countdownEl.id = 'refreshCountdown';
    countdownEl.style.cssText = `
        position: fixed; bottom: 16px; right: 16px;
        background: rgba(0,0,0,0.6); color: white;
        font-size: 11px; padding: 6px 12px;
        border-radius: 20px; z-index: 9999;
        display: none;
    `;
    document.body.appendChild(countdownEl);

    setInterval(function () {
        const ms  = msUntilMidnight();
        const min = Math.floor(ms / 60000);
        const sec = Math.floor((ms % 60000) / 1000);

        if (min < 5) {
            countdownEl.style.display = 'block';
            countdownEl.textContent = `🔄 Harga diperbarui dalam ${min}m ${sec}s`;
        } else {
            countdownEl.style.display = 'none';
        }
    }, 1000);
})();

/* ── Pie Chart ── */
(function () {
    const labels = @json($chartLabels ?? []);
    const values = @json($chartValues ?? []);

    if (!Array.isArray(labels) || !Array.isArray(values) || labels.length === 0 || values.length === 0) {
        return;
    }

    const colors = [
        '#f97316','#3b82f6','#10b981','#f59e0b','#6366f1',
        '#ec4899','#14b8a6','#8b5cf6','#ef4444','#84cc16',
        '#06b6d4','#a855f7','#fb923c','#22d3ee','#e11d48',
        '#4ade80','#facc15',
    ];

    const canvas = document.getElementById('categoryPieChart');
    if (!canvas) return;

    if (window.pieChartInstance) {
        window.pieChartInstance.destroy();
        window.pieChartInstance = null;
    }

    window.pieChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const val   = context.parsed.toLocaleString('id-ID');
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct   = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: Rp ${val} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    const legend = document.getElementById('chartLegend');
    if (!legend) return;

    legend.innerHTML = '';
    const total = values.reduce((a, b) => a + b, 0);

    labels.forEach(function (label, i) {
        const val   = values[i].toLocaleString('id-ID');
        const pct   = ((values[i] / total) * 100).toFixed(1);
        const color = colors[i % colors.length];

        const el = document.createElement('div');
        el.style.cssText = 'display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:10px;';
        el.innerHTML =
            '<span style="width:12px;height:12px;border-radius:3px;background:' + color + ';flex-shrink:0;"></span>' +
            '<span style="flex:1;color:#6b7280;font-weight:500;">' + label + '</span>' +
            '<span style="font-weight:600;color:#111;">Rp ' + val + '</span>' +
            '<span style="color:#9ca3af;font-size:11px;">(' + pct + '%)</span>';

        legend.appendChild(el);
    });
})();

/* ── Intercept filter bar submit ── */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.filter-bar')?.closest('form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const search   = form.querySelector('input[type=text]')?.value.trim() ?? '';
        const category = form.querySelector('select')?.value ?? '';
        const date     = form.querySelector('input[type=date]')?.value ?? '';

        if (!search && !category && !date) {
            e.preventDefault();
            window.location.href = window.location.pathname;
        }
    });
});
</script>
@endpush

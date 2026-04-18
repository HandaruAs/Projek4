@extends('layouts.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard SIMOPANG')
@section('page-sub', 'Selamat datang. Pantau harga komoditas terkini dan prediksi tren harga.')

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stats-grid">

    <div class="stat-card">
        <div>
            <div class="stat-label">Harga Terbaru ({{ $namaKomoditas ?? 'Beras' }})</div>
            <div class="stat-value">
                Rp {{ number_format($hargaTerbaru ?? 14500, 0, ',', '.') }}
                <span style="font-size:14px; font-weight:400">/kg</span>
            </div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i>
                <span class="stat-change-sub">Update terbaru</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-arrow-trend-up"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Perubahan Bulanan</div>
            <div class="stat-value">
                @if(($hargaChange ?? 0) >= 0)
                    + Rp {{ number_format($hargaChange ?? 0, 0, ',', '.') }}
                @else
                    - Rp {{ number_format(abs($hargaChange ?? 0), 0, ',', '.') }}
                @endif
            </div>
            <div class="stat-change {{ ($hargaChange ?? 0) >= 0 ? 'up' : 'down' }}">
                <i class="fas {{ ($hargaChange ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                <span class="stat-change-sub">
                    vs bulan lalu Rp {{ number_format($hargaKemarin ?? 0, 0, ',', '.') }}
                    ({{ ($hargaPercent ?? 0) >= 0 ? '+' : '' }}{{ number_format($hargaPercent ?? 0, 2) }}%)
                </span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-chart-line"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Status Volatilitas</div>
            <div class="stat-value">{{ $statusVolatilitas ?? 'Rendah' }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span class="stat-change-sub">Indeks: {{ $indexVolatilitas ?? '0.38' }} (normal)</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-wave-square"></i></div>
    </div>

</div>

{{-- ── FILTER BAR ── --}}
<form method="GET" action="{{ url()->current() }}">
    <x-filter-bar
        placeholder="Cari komoditas..."
        :categories="optional($categoryList)->toArray() ?? []"
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

{{-- ── RECENT PRICE TABLE ── --}}
<div class="table-card">

    <div class="table-header">
        <div>
            <div class="table-title">Riwayat Harga Terkini</div>
            <div class="table-subtitle">Menampilkan pembaruan harga komoditas terbaru.</div>
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
                    <th>Perubahan</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentPrices as $item)
                <tr>
                    <td class="commodity-name">{{ $item->commodity_name ?? '-' }}</td>
                    <td class="region-text">{{ $item->category ?? '-' }}</td>
                    <td class="price-text">Rp {{ number_format($item->harga_sekarang ?? 0, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $selisih = $item->selisih ?? 0;
                            $persen = $item->persen ?? 0;
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
                    <td class="date-text">
                        {{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}
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
        <small>Pastikan tabel komoditas memiliki data dengan status aktif dan kategori yang terisi, serta memiliki riwayat harga.</small>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
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

/* ── Pie Chart ── */
(function () {
    // Selalu array PHP plain — dijamin oleh controller
    const labels = @json($chartLabels ?? []);
    const values = @json($chartValues ?? []);

    // Guard: harus array dan tidak kosong
    if (!Array.isArray(labels) || !Array.isArray(values) || labels.length === 0 || values.length === 0) {
        console.warn('Pie chart: tidak ada data atau format data salah.', { labels, values });
        return;
    }

    const colors = [
        '#f97316','#3b82f6','#10b981','#f59e0b','#6366f1',
        '#ec4899','#14b8a6','#8b5cf6','#ef4444','#84cc16',
        '#06b6d4','#a855f7','#fb923c','#22d3ee','#e11d48',
        '#4ade80','#facc15',
    ];

    const canvas = document.getElementById('categoryPieChart');
    if (!canvas) {
        console.error('Canvas #categoryPieChart tidak ditemukan di DOM.');
        return;
    }

    // Destroy chart lama jika ada (mencegah duplikat saat hot-reload)
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

    // Custom legend
    const legend = document.getElementById('chartLegend');
    if (!legend) return;

    legend.innerHTML = '';
    const total = values.reduce((a, b) => a + b, 0);

    labels.forEach(function (label, i) {
        const val  = values[i].toLocaleString('id-ID');
        const pct  = ((values[i] / total) * 100).toFixed(1);
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

/* ── Intercept filter bar submit (reset ke clean URL jika kosong) ── */
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
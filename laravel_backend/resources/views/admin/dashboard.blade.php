@extends('layouts.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-sub', "Welcome back, Admin. Here is today's summary of commodity prices.")

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stats-grid">

    <div class="stat-card">
        <div>
            <div class="stat-label">Harga Tertinggi</div>
            <div class="stat-value">
                Rp {{ $hargaTertinggi ? number_format($hargaTertinggi->harga_sekarang, 0, ',', '.') : '-' }}
            </div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i>
                <span class="stat-change-sub">{{ $hargaTertinggi->commodity_name ?? '-' }}</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-arrow-trend-up"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Komoditas</div>
            <div class="stat-value">{{ $totalKomoditas }}</div>
            <div class="stat-change up">
                <i class="fas fa-boxes-stacked"></i>
                <span class="stat-change-sub">active commodities</span>
            </div>
        </div>
        <div class="stat-icon icon-orange">
            <i class="fas fa-boxes-stacked"></i>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Harga Terendah</div>
            <div class="stat-value">
                Rp {{ $hargaTerendah ? number_format($hargaTerendah->harga_sekarang, 0, ',', '.') : '-' }}
            </div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span class="stat-change-sub">{{ $hargaTerendah->commodity_name ?? '-' }}</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-arrow-trend-down"></i></div>
    </div>

</div>

{{-- ── RECENT PRICE UPDATES (dari Predictions) ── --}}
<div class="table-card">

    <div class="table-header">
        <div>
            <div class="table-title">Recent Price Updates</div>
            <div class="table-subtitle">
                Menampilkan 7 komoditas dengan harga prediksi tertinggi.
            </div>
        </div>
        <div class="table-actions">
            <div class="search-box">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" placeholder="Search logs...">
            </div>
            <a href="/admin/harga" class="view-all">View All History</a>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Commodity</th>
                <th>Category</th>
                <th>Price (IDR)</th>
                <th>Change</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPrices as $item)
            <tr>
                <td class="commodity-name">{{ $item->commodity_name }}</td>
                <td class="region-text">{{ $item->category }}</td>
                <td class="price-text">
                    Rp {{ number_format($item->harga_sekarang, 0, ',', '.') }}
                </td>
                <td>
                    @if($item->selisih > 0)
                        <span class="stat-change up" style="font-size:12px">
                            <i class="fas fa-arrow-up"></i> {{ number_format($item->persen, 2) }}%
                        </span>
                    @elseif($item->selisih < 0)
                        <span class="stat-change down" style="font-size:12px">
                            <i class="fas fa-arrow-down"></i> {{ number_format(abs($item->persen), 2) }}%
                        </span>
                    @else
                        <span class="stat-change neutral" style="font-size:12px">
                            <i class="fas fa-minus"></i> 0%
                        </span>
                    @endif
                </td>
                <td class="date-text">
                    {{ $item->date ? \Carbon\Carbon::parse($item->date)->format('M d, Y') : '-' }}
                </td>
                <td>
                    <span style="font-size:11px; padding:3px 10px;
                                 background:#eff6ff; color:#3b82f6;
                                 border-radius:999px; font-weight:600;">
                        Prediksi
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted)">
                    Belum ada data prediksi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">Showing top 7 highest predicted prices</span>
        <div class="pagination">
            <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

</div>

@endsection

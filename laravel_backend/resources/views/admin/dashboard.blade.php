@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-sub', "Welcome back, Admin. Here is today's summary of commodity prices.")

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stats-grid">

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Data Points</div>
            <div class="stat-value">12,450</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i> +12%
                <span class="stat-change-sub">vs last month</span>
            </div>
        </div>
        <div class="stat-icon icon-blue">
            <i class="fas fa-database"></i>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Komoditas</div>
            <div class="stat-value">34</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i> +2%
                <span class="stat-change-sub">active commodities</span>
            </div>
        </div>
        <div class="stat-icon icon-orange">
            <i class="fas fa-boxes-stacked"></i>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Daerah</div>
            <div class="stat-value">18</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i> 0%
                <span class="stat-change-sub">no changes detected</span>
            </div>
        </div>
        <div class="stat-icon icon-purple">
            <i class="fas fa-map-location-dot"></i>
        </div>
    </div>

</div>

{{-- ── RECENT PRICE UPDATES ── --}}
<div class="table-card">

    <div class="table-header">
        <div>
            <div class="table-title">Recent Price Updates</div>
            <div class="table-subtitle">Showing the latest commodity price logs across all regions.</div>
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
                <th>Region</th>
                <th>Price (IDR)</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            {{--
                Jika sudah terhubung ke database:
                @foreach($recentPrices as $item)
                <tr>
                    <td class="commodity-name">{{ $item->commodity->name }}</td>
                    <td class="region-text">{{ $item->market->name }}</td>
                    <td class="price-text">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="date-text">{{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}</td>
                    <td>
                        <a href="/admin/harga/{{ $item->id }}/edit" class="action-btn">
                            <i class="fas fa-pen"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            --}}
            <tr>
                <td class="commodity-name">Beras Premium</td>
                <td class="region-text">Jakarta Selatan</td>
                <td class="price-text">Rp 14,500</td>
                <td class="date-text">Oct 24, 2023</td>
                <td><a href="/admin/harga/1/edit" class="action-btn"><i class="fas fa-pen"></i></a></td>
            </tr>
            <tr>
                <td class="commodity-name">Cabai Merah Keriting</td>
                <td class="region-text">Bandung</td>
                <td class="price-text">Rp 45,000</td>
                <td class="date-text">Oct 24, 2023</td>
                <td><a href="/admin/harga/2/edit" class="action-btn"><i class="fas fa-pen"></i></a></td>
            </tr>
            <tr>
                <td class="commodity-name">Minyak Goreng Curah</td>
                <td class="region-text">Surabaya</td>
                <td class="price-text">Rp 13,200</td>
                <td class="date-text">Oct 23, 2023</td>
                <td><a href="/admin/harga/3/edit" class="action-btn"><i class="fas fa-pen"></i></a></td>
            </tr>
            <tr>
                <td class="commodity-name">Bawang Merah</td>
                <td class="region-text">Medan</td>
                <td class="price-text">Rp 32,400</td>
                <td class="date-text">Oct 23, 2023</td>
                <td><a href="/admin/harga/4/edit" class="action-btn"><i class="fas fa-pen"></i></a></td>
            </tr>
            <tr>
                <td class="commodity-name">Daging Ayam Ras</td>
                <td class="region-text">Makassar</td>
                <td class="price-text">Rp 38,000</td>
                <td class="date-text">Oct 22, 2023</td>
                <td><a href="/admin/harga/5/edit" class="action-btn"><i class="fas fa-pen"></i></a></td>
            </tr>
            <tr>
                <td class="commodity-name">Gula Pasir Lokal</td>
                <td class="region-text">Semarang</td>
                <td class="price-text">Rp 16,500</td>
                <td class="date-text">Oct 22, 2023</td>
                <td><a href="/admin/harga/6/edit" class="action-btn"><i class="fas fa-pen"></i></a></td>
            </tr>
            <tr>
                <td class="commodity-name">Telur Ayam Ras</td>
                <td class="region-text">Yogyakarta</td>
                <td class="price-text">Rp 27,800</td>
                <td class="date-text">Oct 21, 2023</td>
                <td><a href="/admin/harga/7/edit" class="action-btn"><i class="fas fa-pen"></i></a></td>
            </tr>
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">Showing top 7 of 12,450 records</span>
        <div class="pagination">
            <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

</div>

@endsection
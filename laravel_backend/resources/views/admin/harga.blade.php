@extends('layouts.admin')

@section('title', 'Data Harga')
@section('page-title', 'Data Harga')
@section('page-sub', 'Monitor and manage commodity price data from all registered regions.')

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Price Records</div>
            <div class="stat-value">12,450</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i> +12%
                <span class="stat-change-sub">vs last month</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-database"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Records Today</div>
            <div class="stat-value">87</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i> +5
                <span class="stat-change-sub">from yesterday</span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-calendar-day"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Avg. Rice Price</div>
            <div class="stat-value">14.2K</div>
            <div class="stat-change down">
                <i class="fas fa-arrow-trend-down"></i> -1.2%
                <span class="stat-change-sub">this week</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-coins"></i></div>
    </div>
</div>

{{-- FILTER BAR --}}
<div class="filter-bar">
    <div class="search-box" style="flex:1">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" placeholder="Search commodity or region...">
    </div>
    <span class="filter-label">Region:</span>
    <select class="form-select" style="width:160px">
        <option>All Regions</option>
        <option>Jakarta Selatan</option>
        <option>Bandung</option>
        <option>Surabaya</option>
        <option>Medan</option>
        <option>Makassar</option>
        <option>Semarang</option>
        <option>Yogyakarta</option>
    </select>
    <span class="filter-label">Date:</span>
    <input type="date" class="form-input-admin" style="width:150px; padding:8px 12px">
    <a href="/admin/harga/create" class="btn-primary">
        <i class="fas fa-plus"></i> Add Price Data
    </a>
</div>

{{-- PRICE TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Price History</div>
            <div class="table-subtitle">Complete commodity price records from all monitored regions.</div>
        </div>
        <a href="#" class="btn-secondary" style="font-size:12px; padding:7px 14px">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Commodity</th>
                <th>Region</th>
                <th>Price (IDR)</th>
                <th>Stock</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            {{--
                @foreach($priceHistories as $index => $item)
                <tr>
                    <td class="date-text">{{ $index + 1 }}</td>
                    <td class="commodity-name">{{ $item->commodity->name }}</td>
                    <td class="region-text">{{ $item->market->name }}</td>
                    <td class="price-text">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="region-text">{{ $item->stok ?? '-' }}</td>
                    <td class="date-text">{{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="/admin/harga/{{ $item->id }}/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                            <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            --}}
            <tr>
                <td class="date-text">1</td>
                <td class="commodity-name">Beras Premium</td>
                <td class="region-text">Jakarta Selatan</td>
                <td class="price-text">Rp 14,500</td>
                <td class="region-text">250 kg</td>
                <td class="date-text">Oct 24, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/harga/1/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">2</td>
                <td class="commodity-name">Cabai Merah Keriting</td>
                <td class="region-text">Bandung</td>
                <td class="price-text">Rp 45,000</td>
                <td class="region-text">80 kg</td>
                <td class="date-text">Oct 24, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/harga/2/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">3</td>
                <td class="commodity-name">Minyak Goreng Curah</td>
                <td class="region-text">Surabaya</td>
                <td class="price-text">Rp 13,200</td>
                <td class="region-text">500 liter</td>
                <td class="date-text">Oct 23, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/harga/3/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">4</td>
                <td class="commodity-name">Bawang Merah</td>
                <td class="region-text">Medan</td>
                <td class="price-text">Rp 32,400</td>
                <td class="region-text">120 kg</td>
                <td class="date-text">Oct 23, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/harga/4/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">5</td>
                <td class="commodity-name">Daging Ayam Ras</td>
                <td class="region-text">Makassar</td>
                <td class="price-text">Rp 38,000</td>
                <td class="region-text">90 kg</td>
                <td class="date-text">Oct 22, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/harga/5/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">6</td>
                <td class="commodity-name">Gula Pasir Lokal</td>
                <td class="region-text">Semarang</td>
                <td class="price-text">Rp 16,500</td>
                <td class="region-text">300 kg</td>
                <td class="date-text">Oct 22, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/harga/6/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">7</td>
                <td class="commodity-name">Telur Ayam Ras</td>
                <td class="region-text">Yogyakarta</td>
                <td class="price-text">Rp 27,800</td>
                <td class="region-text">150 kg</td>
                <td class="date-text">Oct 21, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/harga/7/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">Showing 7 of 12,450 records</span>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">...</button>
            <button class="page-btn">124</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

@endsection
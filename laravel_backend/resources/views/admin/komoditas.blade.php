@extends('layouts.admin')

@section('title', 'Kelola Komoditas')
@section('page-title', 'Kelola Komoditas')
@section('page-sub', 'Manage commodity data available in the SIMOPANG monitoring system.')

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Commodities</div>
            <div class="stat-value">34</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i> +2
                <span class="stat-change-sub">this month</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-boxes-stacked"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Active Commodities</div>
            <div class="stat-value">31</div>
            <div class="stat-change up">
                <i class="fas fa-circle-check"></i>
                <span class="stat-change-sub">have price data</span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-circle-check"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Categories</div>
            <div class="stat-value">6</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span class="stat-change-sub">no changes</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-layer-group"></i></div>
    </div>
</div>

{{-- FILTER + ADD --}}
<div class="filter-bar">
    <div class="search-box" style="flex:1">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" placeholder="Search commodity name...">
    </div>
    <span class="filter-label">Category:</span>
    <select class="form-select" style="width:160px">
        <option>All Categories</option>
        <option>Bahan Pokok</option>
        <option>Sayuran</option>
        <option>Daging & Ikan</option>
        <option>Bumbu</option>
    </select>
    <a href="/admin/komoditas/create" class="btn-primary">
        <i class="fas fa-plus"></i> Add Commodity
    </a>
</div>

{{-- TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Commodity List</div>
            <div class="table-subtitle">All commodities registered in the SIMOPANG system.</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Commodity Name</th>
                <th>Category</th>
                <th>Price Unit</th>
                <th>Stock Unit</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            {{--
                @foreach($commodities as $index => $item)
                <tr>
                    <td class="date-text">{{ $index + 1 }}</td>
                    <td class="commodity-name">{{ $item->name }}</td>
                    <td class="region-text">{{ $item->category->name }}</td>
                    <td class="region-text">{{ $item->unit }}</td>
                    <td class="region-text">{{ $item->stok_unit }}</td>
                    <td class="region-text">{{ $item->description ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $item->priceHistories->count() > 0 ? 'badge-green' : 'badge-gray' }}">
                            {{ $item->priceHistories->count() > 0 ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="/admin/komoditas/{{ $item->id }}/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                            <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            --}}
            <tr>
                <td class="date-text">1</td>
                <td class="commodity-name">Beras Premium</td>
                <td class="region-text">Bahan Pokok</td>
                <td class="region-text">kg</td>
                <td class="region-text">ton</td>
                <td class="region-text">Beras kualitas premium</td>
                <td><span class="badge badge-green">Active</span></td>
                <td>
                    <div class="action-group">
                        <a href="/admin/komoditas/1/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">2</td>
                <td class="commodity-name">Cabai Merah Keriting</td>
                <td class="region-text">Sayuran</td>
                <td class="region-text">kg</td>
                <td class="region-text">kg</td>
                <td class="region-text">Cabai merah jenis keriting</td>
                <td><span class="badge badge-green">Active</span></td>
                <td>
                    <div class="action-group">
                        <a href="/admin/komoditas/2/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">3</td>
                <td class="commodity-name">Minyak Goreng Curah</td>
                <td class="region-text">Bahan Pokok</td>
                <td class="region-text">liter</td>
                <td class="region-text">drum</td>
                <td class="region-text">Minyak goreng tanpa kemasan</td>
                <td><span class="badge badge-green">Active</span></td>
                <td>
                    <div class="action-group">
                        <a href="/admin/komoditas/3/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">4</td>
                <td class="commodity-name">Bawang Merah</td>
                <td class="region-text">Bumbu</td>
                <td class="region-text">kg</td>
                <td class="region-text">kg</td>
                <td class="region-text">-</td>
                <td><span class="badge badge-green">Active</span></td>
                <td>
                    <div class="action-group">
                        <a href="/admin/komoditas/4/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="date-text">5</td>
                <td class="commodity-name">Daging Ayam Ras</td>
                <td class="region-text">Daging & Ikan</td>
                <td class="region-text">kg</td>
                <td class="region-text">kg</td>
                <td class="region-text">Ayam broiler segar</td>
                <td><span class="badge badge-orange">Inactive</span></td>
                <td>
                    <div class="action-group">
                        <a href="/admin/komoditas/5/edit" class="action-btn"><i class="fas fa-pen"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">Showing 5 of 34 commodities</span>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

@endsection
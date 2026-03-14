@extends('layouts.admin')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
@section('page-sub', 'Generate commodity price predictions using machine learning models.')

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Predictions</div>
            <div class="stat-value">248</div>
            <div class="stat-change up">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span class="stat-change-sub">all predictions</span>
            </div>
        </div>
        <div class="stat-icon icon-purple"><i class="fas fa-wand-magic-sparkles"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">This Month</div>
            <div class="stat-value">34</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i> +8
                <span class="stat-change-sub">from last month</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-chart-line"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Avg. Accuracy</div>
            <div class="stat-value">94.2%</div>
            <div class="stat-change up">
                <i class="fas fa-circle-check"></i>
                <span class="stat-change-sub">active model</span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-bullseye"></i></div>
    </div>
</div>

{{-- GENERATE FORM --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-wand-magic-sparkles" style="color:var(--purple); margin-right:8px"></i>
            Generate New Prediction
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/prediksi/generate">
            @csrf
            <div class="form-grid" style="margin-bottom:18px">
                <div class="form-group-admin">
                    <label class="form-label-admin">Commodity</label>
                    <select class="form-select" name="commodity_id">
                        <option value="">-- Select Commodity --</option>
                        <option>Beras Premium</option>
                        <option>Cabai Merah Keriting</option>
                        <option>Minyak Goreng Curah</option>
                        <option>Bawang Merah</option>
                        <option>Daging Ayam Ras</option>
                        <option>Gula Pasir Lokal</option>
                        <option>Telur Ayam Ras</option>
                    </select>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Region</label>
                    <select class="form-select" name="region">
                        <option value="">-- Select Region --</option>
                        <option>Jakarta Selatan</option>
                        <option>Bandung</option>
                        <option>Surabaya</option>
                        <option>Medan</option>
                        <option>Makassar</option>
                        <option>Semarang</option>
                        <option>Yogyakarta</option>
                        <option>All Regions</option>
                    </select>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Prediction Period</label>
                    <select class="form-select" name="period">
                        <option>7 Days Ahead</option>
                        <option>14 Days Ahead</option>
                        <option>30 Days Ahead</option>
                        <option>90 Days Ahead</option>
                    </select>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Prediction Model</label>
                    <select class="form-select" name="model">
                        <option>ARIMA</option>
                        <option>LSTM</option>
                        <option>Linear Regression</option>
                        <option>Random Forest</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:0; padding-top:16px">
                <button type="reset" class="btn-secondary">
                    <i class="fas fa-rotate-left"></i> Reset
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-wand-magic-sparkles"></i> Generate Prediction
                </button>
            </div>
        </form>
    </div>
</div>

{{-- PREDICTION HISTORY TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Prediction History</div>
            <div class="table-subtitle">Previously generated predictions and their accuracy results.</div>
        </div>
        <div class="search-box">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Search predictions...">
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Commodity</th>
                <th>Region</th>
                <th>Model</th>
                <th>Predicted Price</th>
                <th>Period</th>
                <th>Accuracy</th>
                <th>Generated At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            {{--
                @foreach($predictions as $item)
                <tr>
                    <td class="commodity-name">{{ $item->commodity->name }}</td>
                    <td class="region-text">{{ $item->region }}</td>
                    <td><span class="badge badge-blue">{{ $item->model }}</span></td>
                    <td class="price-text">Rp {{ number_format($item->predicted_price, 0, ',', '.') }}</td>
                    <td class="region-text">{{ \Carbon\Carbon::parse($item->period_end)->format('M d, Y') }}</td>
                    <td><span class="badge {{ $item->accuracy >= 90 ? 'badge-green' : 'badge-orange' }}">{{ $item->accuracy }}%</span></td>
                    <td class="date-text">{{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="/admin/prediksi/{{ $item->id }}" class="action-btn"><i class="fas fa-eye"></i></a>
                            <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            --}}
            <tr>
                <td class="commodity-name">Beras Premium</td>
                <td class="region-text">Jakarta Selatan</td>
                <td><span class="badge badge-blue">ARIMA</span></td>
                <td class="price-text">Rp 15,200</td>
                <td class="region-text">Nov 30, 2023</td>
                <td><span class="badge badge-green">94.8%</span></td>
                <td class="date-text">Oct 24, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/prediksi/1" class="action-btn"><i class="fas fa-eye"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="commodity-name">Cabai Merah Keriting</td>
                <td class="region-text">All Regions</td>
                <td><span class="badge" style="background:#f5f3ff; color:#7c3aed">LSTM</span></td>
                <td class="price-text">Rp 42,000</td>
                <td class="region-text">Nov 30, 2023</td>
                <td><span class="badge badge-green">96.1%</span></td>
                <td class="date-text">Oct 23, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/prediksi/2" class="action-btn"><i class="fas fa-eye"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="commodity-name">Minyak Goreng Curah</td>
                <td class="region-text">Surabaya</td>
                <td><span class="badge badge-blue">ARIMA</span></td>
                <td class="price-text">Rp 13,000</td>
                <td class="region-text">Nov 15, 2023</td>
                <td><span class="badge badge-orange">88.5%</span></td>
                <td class="date-text">Oct 22, 2023</td>
                <td>
                    <div class="action-group">
                        <a href="/admin/prediksi/3" class="action-btn"><i class="fas fa-eye"></i></a>
                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">Showing 3 of 248 predictions</span>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

@endsection
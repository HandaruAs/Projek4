<!-- @extends('layouts.layout')

@section('title', 'Detail Prediksi')
@section('page-title', 'Detail Prediksi')
@section('page-sub', 'Hasil lengkap Holt-Winters Exponential Smoothing')

@push('styles')
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent);
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 1.5rem;
    }

    .info-header {
        margin-bottom: 1.5rem;
    }

    .info-header .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .info-header .card-title i {
        color: var(--accent);
        margin-right: 8px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .metric-card {
        text-align: center;
        padding: 1.2rem;
    }

    .metric-card .metric-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .metric-card .metric-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    .metric-card .metric-value.mape-good  { color: #10b981; }
    .metric-card .metric-value.mape-bad   { color: #ef4444; }

    .metric-card .metric-desc {
        font-size: 11px;
        color: var(--muted);
        margin-top: 4px;
    }

    .forecast-icon {
        margin-right: 6px;
        color: var(--accent);
    }

    .forecast-table-wrap {
        overflow-x: auto;
    }

    .price-cell {
        font-weight: 700;
        color: var(--text-primary);
    }

    .diff-up   { color: #ef4444; font-weight: 600; }
    .diff-down { color: #10b981; font-weight: 600; }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--muted);
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
        display: block;
    }

    .no-prediction {
        text-align: center;
        padding: 3rem;
        color: var(--muted);
    }

    .no-prediction i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        color: var(--muted);
    }
</style>
@endpush

@section('content')

@if($prediction)
@php
    $metrics = $prediction->metrics ?? [];
    $results = $prediction->results ?? [];
    $status  = $metrics['status'] ?? 'completed';
    $badgeClass = match($status) {
        'completed'     => 'badge-status-completed',
        'review_needed' => 'badge-status-review',
        'failed'        => 'badge-status-failed',
        default         => 'badge-status-completed',
    };
    $badgeLabel = match($status) {
        'completed'     => 'COMPLETED',
        'review_needed' => 'REVIEW NEEDED',
        'failed'        => 'FAILED',
        default         => strtoupper($status),
    };
@endphp

{{-- Back Button --}}
<a href="{{ route('user.prediksi') }}" class="back-link">
    <i class="fas fa-arrow-left"></i> Kembali ke Prediksi
</a>

{{-- ── INFO HEADER ── --}}
<div class="card info-header">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-chart-line"></i>
            {{ $prediction->commodity_name ?? '—' }}
        </div>
        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
    </div>
    <div class="card-body">
        <div class="info-grid">

            <div class="stat-mini">
                <div class="stat-mini-label">Tanggal Generate</div>
                <div class="stat-mini-value">
                    {{ $prediction->predicted_at ? \Carbon\Carbon::parse($prediction->predicted_at)->format('d M Y, H:i') : '-' }}
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-label">Horizon</div>
                <div class="stat-mini-value">{{ $prediction->horizon_days ?? '-' }} Hari</div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-label">Trend Type</div>
                <div class="stat-mini-value">{{ ucfirst($metrics['trend'] ?? '-') }}</div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-label">Seasonal Type</div>
                <div class="stat-mini-value">{{ ucfirst($metrics['seasonal'] ?? '-') }}</div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-label">Seasonal Periods</div>
                <div class="stat-mini-value">{{ $metrics['seasonal_periods'] ?? '-' }}</div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-label">Damped Trend</div>
                <div class="stat-mini-value">{{ ($metrics['damped'] ?? false) ? 'Ya' : 'Tidak' }}</div>
            </div>

        </div>
    </div>
</div>

{{-- ── METRICS CARDS ── --}}
@php
    $mapeClass = isset($metrics['mape']) && $metrics['mape'] > 20 ? 'mape-bad' : 'mape-good';
    $mapeLabel = !isset($metrics['mape']) ? '' : ($metrics['mape'] <= 10 ? 'Sangat Baik' : ($metrics['mape'] <= 20 ? 'Baik' : 'Perlu Review'));
@endphp

<div class="metrics-grid">

    <div class="card metric-card">
        <div class="metric-label">MAE</div>
        <div class="metric-value">{{ isset($metrics['mae']) ? number_format($metrics['mae'], 2) : '—' }}</div>
        <div class="metric-desc">Mean Absolute Error</div>
    </div>

    <div class="card metric-card">
        <div class="metric-label">RMSE</div>
        <div class="metric-value">{{ isset($metrics['rmse']) ? number_format($metrics['rmse'], 2) : '—' }}</div>
        <div class="metric-desc">Root Mean Squared Error</div>
    </div>

    <div class="card metric-card">
        <div class="metric-label">MAPE</div>
        <div class="metric-value {{ $mapeClass }}">
            {{ isset($metrics['mape']) ? number_format($metrics['mape'], 2).'%' : '—' }}
        </div>
        <div class="metric-desc">
            Mean Absolute Percentage Error
            @if(isset($metrics['mape'])) — {{ $mapeLabel }} @endif
        </div>
    </div>

    <div class="card metric-card">
        <div class="metric-label">ALPHA (α)</div>
        <div class="metric-value">{{ isset($metrics['alpha']) ? number_format($metrics['alpha'], 4) : '—' }}</div>
        <div class="metric-desc">Smoothing Level</div>
    </div>

    <div class="card metric-card">
        <div class="metric-label">BETA (β)</div>
        <div class="metric-value">{{ isset($metrics['beta']) ? number_format($metrics['beta'], 4) : '—' }}</div>
        <div class="metric-desc">Smoothing Trend</div>
    </div>

    <div class="card metric-card">
        <div class="metric-label">GAMMA (γ)</div>
        <div class="metric-value">{{ isset($metrics['gamma']) ? number_format($metrics['gamma'], 4) : '—' }}</div>
        <div class="metric-desc">Smoothing Seasonal</div>
    </div>

</div>

{{-- ── FORECAST TABLE ── --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-calendar-days forecast-icon"></i>
            Hasil Forecast ({{ count($results) }} hari)
        </div>
    </div>

    @if(count($results) > 0)
    <div class="forecast-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Harga Prediksi (Rp)</th>
                    <th>Batas Bawah (Rp)</th>
                    <th>Batas Atas (Rp)</th>
                    <th>Selisih dari Sebelumnya</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $i => $row)
                @php
                    $price     = $row['predicted_price'] ?? 0;
                    $lower     = $row['lower'] ?? null;
                    $upper     = $row['upper'] ?? null;
                    $prevPrice = $i > 0 ? ($results[$i-1]['predicted_price'] ?? 0) : null;
                    $diff      = $prevPrice !== null ? $price - $prevPrice : null;
                @endphp
                <tr>
                    <td class="date-text">{{ $i + 1 }}</td>
                    <td class="date-text">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td class="price-cell">Rp {{ number_format($price, 0, ',', '.') }}</td>
                    <td class="date-text">
                        {{ $lower !== null ? 'Rp '.number_format($lower, 0, ',', '.') : '—' }}
                    </td>
                    <td class="date-text">
                        {{ $upper !== null ? 'Rp '.number_format($upper, 0, ',', '.') : '—' }}
                    </td>
                    <td class="date-text">
                        @if($diff !== null)
                            <span class="{{ $diff >= 0 ? 'diff-up' : 'diff-down' }}">
                                {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                            </span>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            Tidak ada data forecast tersedia.
        </div>
    @endif
</div>

@else
<div class="no-prediction">
    <i class="fas fa-chart-line"></i>
    <h3>Belum ada hasil prediksi</h3>
    <p>Tidak ada data prediksi yang tersedia.</p>
</div>
@endif

@endsection -->
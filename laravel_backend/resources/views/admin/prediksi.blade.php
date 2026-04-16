@extends('layouts.layout')

@section('title', 'Detail Prediksi')
@section('page-title', 'Detail Prediksi')
@section('page-sub', 'Hasil lengkap Holt-Winters Exponential Smoothing')

@section('content')

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
<div style="margin-bottom:1.5rem">
    <a href="{{ route('prediksi.index') }}"
       style="display:inline-flex;align-items:center;gap:8px;color:var(--accent);
              font-size:13.5px;font-weight:600;text-decoration:none">
        <i class="fas fa-arrow-left"></i> Kembali ke Generate Prediksi
    </a>
</div>

{{-- ── INFO HEADER ── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div class="card-title">
            <i class="fas fa-chart-line" style="color:var(--accent);margin-right:8px"></i>
            {{ $prediction->commodity_name ?? '—' }}
        </div>
        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem">

            <div class="stat-mini">
                <div class="stat-mini-label">Tanggal Generate</div>
                <div class="stat-mini-value">
                    {{ \Carbon\Carbon::parse($prediction->predicted_at)->format('d M Y, H:i') }}
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-label">Horizon</div>
                <div class="stat-mini-value">{{ $prediction->horizon_days }} Hari</div>
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
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem">

    <div class="card" style="text-align:center;padding:1.2rem">
        <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);margin-bottom:6px">MAE</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary)">
            {{ isset($metrics['mae']) ? number_format($metrics['mae'], 2) : '—' }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Mean Absolute Error</div>
    </div>

    <div class="card" style="text-align:center;padding:1.2rem">
        <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);margin-bottom:6px">RMSE</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary)">
            {{ isset($metrics['rmse']) ? number_format($metrics['rmse'], 2) : '—' }}
        </div>
        <form method="POST" action="/admin/prediksi/generate">
            @csrf
            <div class="param-grid">
                <div class="form-group-admin">
                    <label class="form-label-admin">COMMODITY FOCUS</label>
                <select class="form-select" name="commodity_id">
                        @foreach($commodities as $commodity)
                            <option value="{{ $commodity->id }}">{{ $commodity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">PREDICTION HORIZON</label>
                    <select class="form-select" name="period">
                        <option>30 Days</option>
                        <option>7 Days</option>
                        <option>14 Days</option>
                        <option>90 Days</option>
                    </select>
                </div>
                <div class="form-group-admin param-full">
                    <label class="form-label-admin">UPDATE FREQUENCY</label>
                    <select class="form-select" name="frequency">
                        <option>Daily Update</option>
                        <option>Weekly Update</option>
                        <option>Monthly Update</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-run-model">
                <i class="fas fa-wand-magic-sparkles"></i> Run Prediction Model
            </button>
        </form>
    </div>

</div>

{{-- ── FORECAST TABLE ── --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-calendar-days" style="margin-right:6px;color:var(--accent)"></i>
            Hasil Forecast ({{ count($results) }} hari)
        </div>
        {{-- Export CSV --}}
        <a href="{{ route('prediksi.export', $prediction->_id) }}"
           style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;
                  color:var(--accent);text-decoration:none;padding:6px 14px;border:1.5px solid var(--accent);
                  border-radius:8px;transition:.2s"
           onmouseover="this.style.background='var(--accent)';this.style.color='#fff'"
           onmouseout="this.style.background='transparent';this.style.color='var(--accent)'">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Commodity</th>
                <th>Region</th>
                <th>Horizon</th>
                <th>Accuracy (MAE)</th>
                <th>Accuracy (RMSE)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
@foreach($predictions as $item)
    <tr>
        <td class="date-text">{{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y H:i') }}</td>
        <td class="commodity-name">{{ $item->commodity->name ?? $item->commodity_name ?? 'Unknown' }}</td>
        <td class="date-text">{{ $item->region ?? 'Jember' }}</td>
        <td class="date-text">{{ $item->horizon_days }} Days</td>
        <td class="date-text">{{ number_format($item->metrics['mae'] ?? 0, 2) }}</td>
        <td class="date-text">{{ number_format($item->metrics['rmse'] ?? 0, 2) }}</td>
        <td>
            <span class="badge badge-status-completed">COMPLETED</span>
        </td>
        <td>
            <div style="display:flex;flex-direction:column;gap:2px">
                <a href="/admin/prediksi/{{ $item->id }}" class="pred-action-link">View</a>
                <a href="/admin/prediksi/{{ $item->id }}/report" class="pred-action-link">Report</a>
                <form method="POST" action="/admin/prediksi/{{ $item->id }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="pred-action-link" onclick="return confirm('Hapus?')">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
@if($predictions->isEmpty())
    <tr>
        <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">
            Belum ada data prediksi. Generate yang pertama!
        </td>
    </tr>
@endif

            {{ $predictions->links() }}
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">Showing 4 of 128 results</span>
        <div class="pagination">
            <button class="page-btn">Previous</button>
            <button class="page-btn active">Next</button>
        </div>
    @endif
</div>

{{-- ── DELETE BUTTON ── --}}
<div style="margin-top:1.5rem;display:flex;justify-content:flex-end">
    <form method="POST" action="{{ route('prediksi.destroy', $prediction->_id) }}"
          onsubmit="return confirm('Yakin ingin menghapus data prediksi ini?')">
        @csrf
        @method('DELETE')
        <button type="submit"
                style="display:inline-flex;align-items:center;gap:8px;padding:9px 20px;
                       background:#ef4444;color:#fff;border:none;border-radius:10px;
                       font-size:13.5px;font-weight:600;cursor:pointer;transition:.2s"
                onmouseover="this.style.background='#dc2626'"
                onmouseout="this.style.background='#ef4444'">
            <i class="fas fa-trash-can"></i> Hapus Prediksi Ini
        </button>
    </form>
</div>

@endsection
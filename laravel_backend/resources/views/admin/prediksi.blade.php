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
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Root Mean Squared Error</div>
    </div>

    <div class="card" style="text-align:center;padding:1.2rem">
        <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);margin-bottom:6px">MAPE</div>
        <div style="font-size:1.5rem;font-weight:800;
             color:{{ isset($metrics['mape']) && $metrics['mape'] > 20 ? '#ef4444' : '#10b981' }}">
            {{ isset($metrics['mape']) ? number_format($metrics['mape'], 2).'%' : '—' }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">
            Mean Absolute Percentage Error
            @if(isset($metrics['mape']))
                — {{ $metrics['mape'] <= 10 ? 'Sangat Baik' : ($metrics['mape'] <= 20 ? 'Baik' : 'Perlu Review') }}
            @endif
        </div>
    </div>

    <div class="card" style="text-align:center;padding:1.2rem">
        <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);margin-bottom:6px">ALPHA (α)</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary)">
            {{ isset($metrics['alpha']) ? number_format($metrics['alpha'], 4) : '—' }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Smoothing Level</div>
    </div>

    <div class="card" style="text-align:center;padding:1.2rem">
        <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);margin-bottom:6px">BETA (β)</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary)">
            {{ isset($metrics['beta']) ? number_format($metrics['beta'], 4) : '—' }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Smoothing Trend</div>
    </div>

    <div class="card" style="text-align:center;padding:1.2rem">
        <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);margin-bottom:6px">GAMMA (γ)</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary)">
            {{ isset($metrics['gamma']) ? number_format($metrics['gamma'], 4) : '—' }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Smoothing Seasonal</div>
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

    @if(count($results) > 0)
    <div style="overflow-x:auto">
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
                    $price    = $row['predicted_price'] ?? 0;
                    $lower    = $row['lower'] ?? null;
                    $upper    = $row['upper'] ?? null;
                    $prevPrice = $i > 0 ? ($results[$i-1]['predicted_price'] ?? 0) : null;
                    $diff      = $prevPrice !== null ? $price - $prevPrice : null;
                @endphp
                <tr>
                    <td class="date-text">{{ $i + 1 }}</td>
                    <td class="date-text">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td style="font-weight:700;color:var(--text-primary)">
                        Rp {{ number_format($price, 0, ',', '.') }}
                    </td>
                    <td class="date-text">
                        {{ $lower !== null ? 'Rp '.number_format($lower, 0, ',', '.') : '—' }}
                    </td>
                    <td class="date-text">
                        {{ $upper !== null ? 'Rp '.number_format($upper, 0, ',', '.') : '—' }}
                    </td>
                    <td class="date-text">
                        @if($diff !== null)
                            <span style="color:{{ $diff >= 0 ? '#ef4444' : '#10b981' }};font-weight:600">
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
        <div style="text-align:center;padding:3rem;color:var(--muted)">
            <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:1rem;display:block"></i>
            Tidak ada data forecast tersedia.
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
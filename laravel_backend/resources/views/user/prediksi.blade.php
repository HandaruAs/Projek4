@extends('layouts.layout')

@section('title', 'Prediksi Harga')
@section('page-title', 'Prediksi Harga')
@section('page-sub', 'Hasil prediksi harga komoditas menggunakan Holt-Winters Exponential Smoothing.')

@section('content')

{{-- Filter Komoditas --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body">
        <form method="GET" action="{{ route('user.prediksi') }}"
              style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <label style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--muted);display:block;margin-bottom:6px">
                    FILTER KOMODITAS
                </label>
                <select class="form-select" name="commodity_id"
                        onchange="this.form.submit()">
                    <option value="">— Semua Komoditas —</option>
                    @foreach($commodities as $c)
                        <option value="{{ $c->_id }}"
                            {{ request('commodity_id') == $c->_id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('commodity_id'))
                <a href="{{ route('user.prediksi') }}"
                   style="padding:8px 16px;font-size:13px;color:var(--muted);text-decoration:none;
                          border:1px solid var(--border);border-radius:8px;white-space:nowrap">
                    <i class="fas fa-xmark"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Prediction History Table --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-chart-line" style="color:var(--accent);margin-right:8px"></i>
                Riwayat Prediksi
            </div>
            <div class="table-sub">Data prediksi harga komoditas pangan Jember</div>
        </div>
        <span class="view-all">{{ $predictions->total() }} total prediksi</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Komoditas</th>
                <th>Horizon</th>
                <th>Trend / Seasonal</th>
                <th>MAE</th>
                <th>RMSE</th>
                <th>MAPE (%)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($predictions as $item)
            @php
                $metrics  = $item->metrics ?? [];
                $status   = $metrics['status'] ?? 'completed';
                $badgeMap = [
                    'completed'     => 'badge-status-completed',
                    'review_needed' => 'badge-status-review',
                    'failed'        => 'badge-status-failed',
                ];
                $labelMap = [
                    'completed'     => 'COMPLETED',
                    'review_needed' => 'REVIEW NEEDED',
                    'failed'        => 'FAILED',
                ];
            @endphp
            <tr>
                <td class="date-text" style="white-space:nowrap">
                    {{ \Carbon\Carbon::parse($item->predicted_at)->format('d M Y') }}<br>
                    <span style="color:var(--muted)">
                        {{ \Carbon\Carbon::parse($item->predicted_at)->format('H:i') }}
                    </span>
                </td>
                <td class="commodity-name">{{ $item->commodity_name ?? '—' }}</td>
                <td class="date-text">{{ $item->horizon_days }} Hari</td>
                <td class="date-text" style="font-size:.72rem;white-space:nowrap">
                    T: <strong>{{ ucfirst($metrics['trend'] ?? '-') }}</strong><br>
                    S: <strong>{{ ucfirst($metrics['seasonal'] ?? '-') }}</strong>
                </td>
                <td class="date-text">
                    {{ isset($metrics['mae'])  ? number_format($metrics['mae'],  2) : '—' }}
                </td>
                <td class="date-text">
                    {{ isset($metrics['rmse']) ? number_format($metrics['rmse'], 2) : '—' }}
                </td>
                <td class="date-text">
                    {{ isset($metrics['mape']) ? number_format($metrics['mape'], 2).'%' : '—' }}
                </td>
                <td>
                    <span class="{{ $badgeMap[$status] ?? 'badge-status-completed' }}">
                        {{ $labelMap[$status] ?? strtoupper($status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('user.prediksi.show', $item->_id) }}"
                       class="pred-action-link">
                        <i class="fas fa-chart-line"></i> Lihat Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div style="text-align:center;padding:2rem;color:var(--muted)">
                        <i class="fas fa-clock-rotate-left" style="font-size:2rem;margin-bottom:.5rem;display:block"></i>
                        Belum ada riwayat prediksi
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($predictions->hasPages())
    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $predictions->firstItem() }}–{{ $predictions->lastItem() }}
            of {{ $predictions->total() }} results
        </span>
        <div>{{ $predictions->links() }}</div>
    </div>
    @endif
</div>

@endsection

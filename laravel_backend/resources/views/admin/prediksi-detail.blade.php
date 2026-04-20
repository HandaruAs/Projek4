@extends('layouts.layout')

@section('title', 'Detail Prediksi')
@section('page-title', 'Detail Prediksi')
@section('page-sub', 'Hasil lengkap Holt-Winters Exponential Smoothing')

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
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem">
    [... metrics cards unchanged ...]
</div>

{{-- ── FORECAST TABLE ── --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-calendar-days" style="margin-right:6px;color:var(--accent)"></i>
            Hasil Forecast ({{ count($results) }} hari)
        </div>
        <a href="{{ route('prediksi.export', $prediction->_id) }}" class="export-btn">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>
    [... table unchanged ...]
</div>

{{-- ── DELETE BUTTON ── --}}
<div style="margin-top:1.5rem;display:flex;justify-content:flex-end">
    <form method="POST" action="{{ route('prediksi.destroy', $prediction->_id) }}" onsubmit="return confirm('Yakin?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-danger">
            <i class="fas fa-trash"></i> Hapus
        </button>
    </form>
</div>
@else
<p>Belum ada hasil prediksi</p>
@endif

@endsection


@extends('layouts.layout')

@section('title', 'Detail Prediksi')
@section('page-title', $prediction->commodity_name ?? 'Prediksi')
@section('page-sub', 'Hasil lengkap Holt-Winters Exponential Smoothing')

@section('content')

{{--
    Skema dokumen MongoDB (identik Flask):
    - $prediction->commodity_name   → field langsung
    - $prediction->steps            → field langsung (bukan horizon_days)
    - $prediction->created_at       → field langsung (bukan predicted_at)
    - $prediction->accuracy_mae     → flat field langsung
    - $prediction->accuracy_rmse    → flat field langsung
    - $prediction->accuracy_mape    → flat field langsung
    - $prediction->payload          → nested object berisi semua data prediksi
      - payload.harga_terakhir
      - payload.satuan
      - payload.kategori
      - payload.tanggal_pred
      - payload.forecast
      - payload.ci_lower
      - payload.ci_upper
      - payload.accuracy.accuracy   → persen akurasi model
      - payload.accuracy.note
      - payload.from_cache
--}}

@php
    $payload  = $prediction->payload ?? [];
    $acc      = $payload['accuracy'] ?? [];
    $tanggal  = $payload['tanggal_pred'] ?? [];
    $forecast = $payload['forecast']     ?? [];
    $ciLower  = $payload['ci_lower']     ?? null;
    $ciUpper  = $payload['ci_upper']     ?? null;
@endphp

{{-- Back Button --}}
<div class="mb-4">
    <a href="{{ route('prediksi.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

{{-- Info Cards --}}
<div class="row mb-4 g-3">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                {{-- steps: field flat langsung di dokumen --}}
                <h5>{{ $prediction->steps ?? 'N/A' }} Hari</h5>
                <small class="text-muted">Horizon</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                {{-- accuracy_mape: flat field langsung di dokumen --}}
                <h5>{{ number_format($prediction->accuracy_mape ?? 0, 2) }}%</h5>
                <small class="text-muted">MAPE</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                {{-- accuracy_mae: flat field langsung di dokumen --}}
                <h5>{{ number_format($prediction->accuracy_mae ?? 0, 0) }}</h5>
                <small class="text-muted">MAE</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                {{-- accuracy_rmse: flat field langsung di dokumen --}}
                <h5>{{ number_format($prediction->accuracy_rmse ?? 0, 0) }}</h5>
                <small class="text-muted">RMSE</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                {{-- created_at: field langsung (bukan predicted_at) --}}
                <h5>{{ $prediction->created_at ? $prediction->created_at->format('d M Y H:i') : 'N/A' }}</h5>
                <small class="text-muted">Generated</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                {{-- harga_terakhir: dari dalam payload --}}
                <h5>Rp {{ number_format($payload['harga_terakhir'] ?? 0, 0) }}</h5>
                <small class="text-muted">Current Price</small>
            </div>
        </div>
    </div>
</div>

{{-- Accuracy & cache badge --}}
<div class="mb-4 d-flex align-items-center gap-3 flex-wrap">
    @if(isset($acc['accuracy']) && $acc['accuracy'] !== null)
        @php
            $pct = $acc['accuracy'];
            $badgeColor = $pct >= 90 ? 'success' : ($pct >= 80 ? 'warning' : 'danger');
        @endphp
        <span class="badge bg-{{ $badgeColor }} fs-6">
            Akurasi Model: {{ number_format($pct, 1) }}%
        </span>
    @endif

    @if($payload['from_cache'] ?? false)
        <span class="badge bg-secondary">
            <i class="fas fa-bolt me-1"></i> Dari Cache
        </span>
    @endif

    @if($acc['note'] ?? null)
        <small class="text-muted">{{ $acc['note'] }}</small>
    @endif
</div>

{{-- Export & Delete --}}
<div class="mb-4">
    <div class="btn-group" role="group">
        <a href="{{ route('prediksi.export', $prediction->id) }}" class="btn btn-success">
            <i class="fas fa-download me-2"></i>Export CSV
        </a>
        <form method="POST" action="{{ route('prediksi.destroy', $prediction->id) }}" class="d-inline"
              onsubmit="return confirm('Hapus prediksi ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash me-2"></i>Hapus
            </button>
        </form>
    </div>
</div>

{{-- Forecast Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Forecast Results — {{ $prediction->commodity_name }}
            <small class="text-muted">({{ $payload['satuan'] ?? 'kg' }})</small>
        </h5>
        <small class="text-muted">
            Data terakhir: {{ $payload['tanggal_terakhir'] ?? '—' }}
        </small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Predicted Price</th>
                        <th>Lower CI</th>
                        <th>Upper CI</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($tanggal) > 0)
                        @foreach($tanggal as $i => $tgl)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($tgl)->format('d M Y') }}</td>
                                <td>
                                    <strong>Rp {{ number_format($forecast[$i] ?? 0, 0) }}</strong>
                                </td>
                                <td>
                                    @if(is_array($ciLower) && isset($ciLower[$i]))
                                        Rp {{ number_format($ciLower[$i], 0) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(is_array($ciUpper) && isset($ciUpper[$i]))
                                        Rp {{ number_format($ciUpper[$i], 0) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($i > 0)
                                        @php
                                            $diff = ($forecast[$i] ?? 0) - ($forecast[$i - 1] ?? 0);
                                        @endphp
                                        <span class="{{ $diff > 0 ? 'text-danger' : ($diff < 0 ? 'text-success' : 'text-muted') }}">
                                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No forecast data available.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
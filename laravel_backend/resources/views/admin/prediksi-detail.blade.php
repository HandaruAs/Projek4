@extends('layouts.layout')

@section('title', 'Detail Prediksi')
@section('page-title', $prediction->commodity_name ?? 'Prediksi')
@section('page-sub', 'Hasil lengkap Holt-Winters Exponential Smoothing')

@section('content')

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
                <h5>{{ $prediction->horizon_days ?? 'N/A' }} Hari</h5>
                <small class="text-muted">Horizon</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>{{ number_format(($prediction->metrics['mape'] ?? 0), 2) }}%</h5>
                <small class="text-muted">MAPE</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>{{ number_format(($prediction->metrics['mae'] ?? 0), 0) }}</h5>
                <small class="text-muted">MAE</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>{{ number_format(($prediction->metrics['rmse'] ?? 0), 0) }}</h5>
                <small class="text-muted">RMSE</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>{{ $prediction->predicted_at ? $prediction->predicted_at->format('d M Y H:i') : 'N/A' }}</h5>
                <small class="text-muted">Generated</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h5>Rp {{ number_format($prediction->current_price ?? 0, 0) }}</h5>
                <small class="text-muted">Current Price</small>
            </div>
        </div>
    </div>
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
    <div class="card-header">
        <h5>Forecast Results</h5>
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
                    @if(isset($prediction->results) && count($prediction->results) > 0)
                        @foreach($prediction->results as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                                <td><strong>Rp {{ number_format($row['predicted_price'], 0) }}</strong></td>
                                <td>Rp {{ number_format($row['lower'] ?? 0, 0) }}</td>
                                <td>Rp {{ number_format($row['upper'] ?? 0, 0) }}</td>
                                <td>
                                    @if($i > 0)
                                        @php $diff = $row['predicted_price'] - $prediction->results[$i-1]['predicted_price']; @endphp
                                        <span class="{{ $diff > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0) }}
                                        </span>
                                    @else
                                        —
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


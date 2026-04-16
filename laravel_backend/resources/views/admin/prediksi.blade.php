@extends('layouts.layout')

@section('title', 'Prediksi Harga')
@section('page-title', 'Prediksi Harga Pangan')
@section('page-sub', 'Holt-Winters Model dari MongoDB')

@section('content')
<div class="page-header">
    <div class="header-left">
        <h1><i class="fas fa-chart-line me-2"></i>Prediksi Harga</h1>
        <p class="text-muted">Hasil model Holt-Winters Exponential Smoothing dari data real-time</p>
    </div>
</div>

{{-- Generate Form --}}
<form method="POST" action="{{ route('prediksi.generate') }}" class="generate-card">
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
                <option value="7">7 Days</option>
                <option value="14">14 Days</option>
                <option value="30" selected>30 Days</option>
                <option value="90">90 Days</option>
            </select>
        </div>
        <div class="form-group-admin">
            <label class="form-label-admin">REGION</label>
            <input type="text" class="form-input" name="region" value="Jember" required>
        </div>
        <div class="form-group-admin">
            <label class="form-label-admin">MODEL</label>
            <select class="form-select" name="model">
                <option value="holt_winters">Holt-Winters (Auto)</option>
                <option value="holt_linear">Holt Linear</option>
                <option value="simple_exp">Simple Exponential</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn-run-model">
        <i class="fas fa-wand-magic-sparkles me-2"></i>
        Run Prediction Model
    </button>
</form>

{{-- Predictions Table --}}
<div class="table-card mt-4">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-list me-2" style="color:var(--accent)"></i>
            Recent Predictions ({{ $predictions->total() }} total)
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Commodity</th>
                    <th>Region</th>
                    <th>Horizon</th>
                    <th>MAE</th>
                    <th>RMSE</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($predictions as $item)
                <tr>
                    <td>{{ $item->created_at->format('M d, Y H:i') }}</td>
                    <td class="font-semibold">{{ $item->commodity_name ?? $item->commodity->name ?? 'Unknown' }}</td>
                    <td>{{ $item->region ?? 'Jember' }}</td>
                    <td>{{ $item->horizon_days ?? 30 }} Days</td>
                    <td>{{ number_format($item->metrics['mae'] ?? 0, 1) }}</td>
                    <td>{{ number_format($item->metrics['rmse'] ?? 0, 1) }}</td>
                    <td>
                        @if(isset($item->metrics['mape']) && $item->metrics['mape'] <= 10)
                            <span class="badge badge-success">EXCELLENT</span>
                        @elseif(isset($item->metrics['mape']) && $item->metrics['mape'] <= 20)
                            <span class="badge badge-info">GOOD</span>
                        @else
                            <span class="badge badge-warning">REVIEW</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('prediksi.show', $item->_id) }}" class="btn-icon btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('prediksi.destroy', $item->_id) }}" 
                                  style="display:inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-8 text-muted">
                        <i class="fas fa-magic text-3xl mb-4 block"></i>
                        Belum ada prediksi. Gunakan form di atas untuk generate yang pertama!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        {{ $predictions->links() }}
    </div>
</div>

@endsection

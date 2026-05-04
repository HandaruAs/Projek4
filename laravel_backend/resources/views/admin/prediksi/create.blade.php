@extends('layouts.layout')

@section('title', 'Generate Prediksi - SIMOPANG Admin')
@section('page-title', 'Generate Prediksi')
@section('page-sub', 'Holt-Winters Exponential Smoothing Model')

@section('content')
<div class="prediksi-container">
    {{-- Header Section --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-wand-magic-sparkles text-primary me-2"></i>
                    Generate Prediction
                </h2>
                <p class="text-muted mb-0">
                    Run the Holt-Winters machine learning model to predict future commodity prices based on historical data.
                </p>
            </div>
            <a href="{{ route('prediksi.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Main Grid --}}
    <div class="row g-4">
        {{-- Left: Import Data (optional) --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cloud-upload text-primary me-2"></i>
                        Import Data Excel (Optional)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p class="mb-1">Click to upload or drag and drop</p>
                        <small class="text-muted">XLSX or CSV (MAX. 10MB)</small>
                        <input type="file" class="d-none" accept=".xlsx,.xlsx,.csv">
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-sm btn-outline-secondary">Cancel</button>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-upload me-1"></i>Upload File
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Generate Prediction Form --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Generate Prediction
                    </h5>
                </div>
                <div class="card-body">
action="{{ route('prediksi.generate') }}" id="prediksiForm">
                        @csrf

                        <div class="row g-3">
                            {{-- Commodity Type --}}
                            <div class="col-md-6">
                                <label class="form-label">Commodity Type *</label>
                                <select name="commodity_name" class="form-select @error('commodity_name') is-invalid @enderror" required>
                                    <option value="">Pilih komoditas...</option>
                                    @foreach($commodities as $name)
                                        <option value="{{ $name }}" {{ old('commodity_name') == $name ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commodity_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Forecast Period --}}
                            <div class="col-md-6">
                                <label class="form-label">Forecast Period *</label>
                                <select name="horizon_days" class="form-select @error('horizon_days') is-invalid @enderror" required>
                                    <option value="7" {{ old('horizon_days') == '7' ? 'selected' : '' }}>Next 7 Days</option>
                                    <option value="14" {{ old('horizon_days') == '14' ? 'selected' : '' }}>Next 14 Days</option>
                                    <option value="30" {{ old('horizon_days') == '30' ? 'selected' : '' }} selected>Next 30 Days</option>
                                    <option value="60" {{ old('horizon_days') == '60' ? 'selected' : '' }}>Next 60 Days</option>
                                    <option value="90" {{ old('horizon_days') == '90' ? 'selected' : '' }}>Next 90 Days</option>
                                </select>
                                @error('horizon_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Model Status Info --}}
                        <div class="model-status mt-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="status-icon">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Model Status: Ready</h6>
                                    <small class="text-muted">
                                        Last training completed 2 hours ago. Accuracy: 94.2%
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="mt-4">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100">
                                <span class="btn-text">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Run Holt-Winters Model
                                </span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Predictions --}}
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-history text-muted me-2"></i>
                Recent Predictions
            </h5>
            <a href="{{ route('prediksi.index') }}" class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Commodity</th>
                        <th>Forecast Period</th>
                        <th>Date</th>
                        <th>Accuracy</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($recentPredictions) && $recentPredictions->count() > 0)
                        @foreach($recentPredictions as $pred)
                        <tr>
                            <td>
                                <strong>{{ $pred->commodity_name }}</strong>
                            </td>
                            <td>{{ $pred->horizon_days }} Days</td>
                            <td>{{ \Carbon\Carbon::parse($pred->created_at)->format('M d, Y') }}</td>
                            <td>
                                @if(isset($pred->metrics['mape']))
                                    <span class="badge bg-success">{{ number_format($pred->metrics['mape'], 1) }}%</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $pred->status == 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($pred->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('prediksi.show', $pred->_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No recent predictions
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelector('#prediksiForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.querySelector('.btn-text').classList.add('d-none');
    btn.querySelector('.btn-loading').classList.remove('d-none');
});
</script>
@endsection

@push('styles')
<style>
.prediksi-container {
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.card-header {
    background: transparent;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 1.25rem;
}

.card-header h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
}

.card-body {
    padding: 1.25rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #475569;
    margin-bottom: 0.5rem;
}

.form-select {
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
}

.form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.upload-area {
    border: 2px dashed #cbd5e1;
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: center;
    background: #f8fafc;
    transition: all 0.2s;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.upload-icon {
    font-size: 2rem;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}

.model-status {
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 0.5rem;
    padding: 1rem;
}

.model-status .status-icon {
    font-size: 1.25rem;
}

.btn-primary {
    background: #137fec;
    border-color: #137fec;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.btn-primary:hover:not(:disabled) {
    background: #1d4ed8;
    border-color: #1d4ed8;
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.alert-danger {
    border-radius: 0.5rem;
    border: none;
    background: #fef2f2;
    color: #dc2626;
}

.table {
    font-size: 0.875rem;
}

.table thead th {
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

@media (max-width: 768px) {
    .row.g-4 > div {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
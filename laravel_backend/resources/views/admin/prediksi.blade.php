@extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
@section('page-sub', 'Upload data historis & jalankan model Holt-Winters')

@section('content')

{{-- ── FLASH MESSAGES ── --}}
@if (session('success'))
<div class="alert alert-success">
    <i class="fas fa-circle-check"></i>
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger">
    <i class="fas fa-circle-xmark"></i>
    {{ session('error') }}
</div>
@endif

@if(session('import_errors') && count(session('import_errors')) > 0)
<div class="alert alert-warning">
    <strong>Peringatan import:</strong>
    <ul>
        @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ── 1. UPLOAD HISTORICAL DATA ── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-upload me-2 text-primary"></i>Upload Historical Data</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('prediksi.upload') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-8">
                <label class="form-label">CSV/Excel File <span class="text-danger">*</span></label>
                <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-control" required>
                <small class="form-text text-muted">Format: commodity_name, harga_sekarang, date, harga_lama, satuan</small>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-upload me-2"></i>Upload
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── 2. GENERATE PREDICTION ── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-wand-magic-sparkles me-2 text-primary"></i>Generate Prediction</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('prediksi.generate') }}" class="row g-3">
            @csrf
            <div class="col-md-5">
                <label class="form-label">Commodity <span class="text-danger">*</span></label>
                <select name="commodity_id" class="form-select" required>
                    <option value="">Pilih Komoditas...</option>
                    @foreach($commodities as $c)
                        <option value="{{ $c->_id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Days <span class="text-danger">*</span></label>
                <select name="steps" class="form-select" required>
                    <option value="7">7 Hari</option>
                    <option value="14">14 Hari</option>
                    <option value="30" selected>30 Hari</option>
                    <option value="60">60 Hari</option>
                    <option value="90">90 Hari</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-wand-magic-sparkles me-2"></i>Run Holt-Winters
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── 3. PREDICTION HISTORY ── --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Prediction History ({{ $predictions->total() }})</h5>
    </div>
    <div class="card-body">
        @if($predictions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Komoditas</th>
                            <th>Generated</th>
                            <th>Days</th>
                            <th>Accuracy (MAPE)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($predictions as $pred)
                            @php
                                $metrics = $pred->metrics ?? [];
                                $mape = $metrics['mape'] ?? null;
                                $accuracyClass = $mape && $mape < 5 ? 'text-success' : ($mape && $mape < 10 ? 'text-warning' : 'text-danger');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $pred->commodity_name }}</strong></td>
                                <td>{{ $pred->predicted_at ? $pred->predicted_at->format('d M Y H:i') : 'N/A' }}</td>
                                <td>{{ $pred->horizon_days }} days</td>
                                <td>
                                    @if($mape !== null)
                                        <span class="{{ $accuracyClass }} fw-bold">{{ number_format($mape, 2) }}%</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <a href="{{ route('prediksi.show', $pred->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('prediksi.export', $pred->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-download"></i> CSV
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $predictions->links() }}
        @else
            <div class="text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada prediksi. Generate yang pertama!</p>
            </div>
        @endif
    </div>
</div>

@endsection


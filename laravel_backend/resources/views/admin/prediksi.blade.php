@extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
@section('page-sub', 'Generate commodity price predictions using machine learning models.')

@push('styles')
<style>
    .alert-box {
        margin-bottom: 1rem;
        padding: .75rem 1rem;
        border-radius: 10px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .alert-warning {
        margin-bottom: 1rem;
        padding: .75rem 1rem;
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
        border-radius: 10px;
        font-size: 13px;
    }
    .alert-warning-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .alert-warning ul {
        margin: 0;
        padding-left: 1.25rem;
    }

    .section-card { margin-bottom: 1.5rem; }
    .section-icon { color: var(--accent); margin-right: 8px; }

    .upload-form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
        padding: 1.5rem;
    }
    .upload-field { flex: 1; min-width: 260px; }
    .upload-hint {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 4px;
        display: block;
    }
    .upload-hint i { font-size: 10px; }
    .file-input { padding: 6px 10px; cursor: pointer; }

    .generate-form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
        padding: 1.5rem;
    }
    .generate-field-lg { flex: 2; min-width: 220px; }
    .generate-field-sm { flex: 1; min-width: 160px; }
    .btn-success { background: var(--success, #10b981); }

    .pred-count {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-muted);
        margin-left: 6px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        background: #d1fae5;
        color: #065f46;
    }
    .status-badge i { font-size: 6px; }

    .btn-export {
        background: #f0fdf4;
        color: #16a34a;
        border-color: #bbf7d0;
    }

    .mape-good  { font-weight: 600; color: #16a34a; }
    .mape-warn  { font-weight: 600; color: #d97706; }
    .mape-bad   { font-weight: 600; color: #dc2626; }
    .mape-muted { font-weight: 600; color: var(--text-muted); }

    .empty-pred {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
    }
    .empty-pred i {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 1rem;
        opacity: .35;
    }
    .empty-pred-title { font-weight: 600; margin-bottom: 4px; }
    .empty-pred-sub   { font-size: 13px; }
</style>
@endpush

@section('content')

{{-- TWO PANEL ROW --}}
<div class="prediksi-panels">

    {{-- PANEL 1: Import Historical Data --}}
    <div class="panel-card">
        <div class="panel-step-badge">1</div>
        <div class="panel-header">
            <div class="panel-title">Import Historical Data</div>
            <div class="panel-sub">Upload latest price records</div>
        </div>
        <form method="POST" action="/admin/prediksi/upload" enctype="multipart/form-data">
            @csrf
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-file-arrow-up upload-zone-icon"></i>
                <p class="upload-zone-text">Drop Excel or CSV file</p>
                <input type="file" id="fileInput" name="file" accept=".xlsx,.csv" hidden>
                <button type="button" class="btn-secondary" onclick="document.getElementById('fileInput').click()">
                    Browse Files
                </button>
            </div>
            <div class="panel-footer-row">
                <a href="#" class="template-link"><i class="fas fa-download"></i> Template</a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check-circle"></i> Validate & Upload
                </button>
            </div>
        </form>
    </div>

    {{-- PANEL 2: Model Parameters --}}
    <div class="panel-card">
        <div class="panel-step-badge">2</div>
        <div class="panel-header">
            <div class="panel-title">Model Parameters</div>
            <div class="panel-sub">Configure Holt-Winters forecasting settings</div>
        </div>
        <form method="POST" action="/admin/prediksi/generate">
            @csrf
            <div class="param-grid">
                <div class="form-group-admin">
                    <label class="form-label-admin">COMMODITY FOCUS</label>
                    <select class="form-select" name="komoditas">
                        @foreach($komoditasList as $nama)
                            <option value="{{ $nama }}" {{ $selectedNama === $nama ? 'selected' : '' }}>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">PREDICTION HORIZON</label>
                    <select class="form-select" name="steps">
                        <option value="7">7 Days</option>
                        <option value="14">14 Days</option>
                        <option value="30" selected>30 Days</option>
                        <option value="90">90 Days</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-run-model">
                <i class="fas fa-wand-magic-sparkles"></i> Run Prediction Model
            </button>
        </form>
    </div>

</div>

{{-- FLASH MESSAGES --}}
@if(session('success'))
    <div class="alert-box alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert-box alert-error">
        <i class="fas fa-times-circle"></i> {{ session('error') }}
    </div>
@endif

{{-- PREDICTION HISTORY TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Prediction History</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Commodity</th>
                <th>Horizon</th>
                <th>MAE</th>
                <th>RMSE</th>
                <th>MAPE</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($predictions as $pred)
                @php
                    $metrics  = $pred->metrics ?? [];
                    $mape     = $metrics['mape'] ?? null;
                    $mae      = $metrics['mae']  ?? null;
                    $rmse     = $metrics['rmse'] ?? null;

                    if ($mape === null)  { $mapeClass = 'mape-muted'; }
                    elseif ($mape < 5)  { $mapeClass = 'mape-good'; }
                    elseif ($mape < 10) { $mapeClass = 'mape-warn'; }
                    else                { $mapeClass = 'mape-bad'; }
                @endphp
                <tr>
                    <td class="date-text">
                        {{ \Carbon\Carbon::parse($pred->predicted_at)->format('M d, Y H:i') }}
                    </td>
                    <td class="commodity-name">{{ $pred->commodity_name }}</td>
                    <td class="date-text">{{ $pred->horizon_days }} Days</td>
                    <td class="date-text">{{ $mae ? number_format($mae, 2) : '—' }}</td>
                    <td class="date-text">{{ $rmse ? number_format($rmse, 2) : '—' }}</td>
                    <td>
                        @if($mape !== null)
                            <span class="{{ $mapeClass }}">{{ number_format($mape, 2) }}%</span>
                        @else
                            <span class="date-text">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:5px;
                                     padding:3px 10px; border-radius:20px; font-size:11.5px;
                                     font-weight:600; background:#d1fae5; color:#065f46">
                            <i class="fas fa-circle" style="font-size:6px"></i> Completed
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:2px">
                            <a href="/admin/prediksi/{{ urlencode($pred->commodity_name) }}"
                               class="pred-action-link">View</a>
                            <a href="/admin/prediksi/export/{{ $pred->id }}"
                               class="pred-action-link btn-export">Export</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-pred">
                            <i class="fas fa-chart-line"></i>
                            <div class="empty-pred-title">Belum ada prediksi</div>
                            <div class="empty-pred-sub">Generate prediksi pertama menggunakan form di atas</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $predictions->count() }} of {{ $predictions->total() }} results
        </span>
        <div class="pagination">
            {{ $predictions->links() }}
        </div>
    </div>
</div>

@endsection

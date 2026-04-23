<!-- @extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
@section('page-sub', 'Upload data historis & jalankan model Holt-Winters')

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

    .section-card {
        margin-bottom: 1.5rem;
    }
    .section-icon {
        color: var(--accent);
        margin-right: 8px;
    }

    .upload-form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
        padding: 1.5rem;
    }
    .upload-field {
        flex: 1;
        min-width: 260px;
    }
    .upload-hint {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 4px;
        display: block;
    }
    .upload-hint i {
        font-size: 10px;
    }
    .file-input {
        padding: 6px 10px;
        cursor: pointer;
    }

    .generate-form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
        padding: 1.5rem;
    }
    .generate-field-lg { flex: 2; min-width: 220px; }
    .generate-field-sm { flex: 1; min-width: 160px; }
    .btn-success {
        background: var(--success, #10b981);
    }

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
    .status-badge i {
        font-size: 6px;
    }

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
    .empty-pred-title {
        font-weight: 600;
        margin-bottom: 4px;
    }
    .empty-pred-sub {
        font-size: 13px;
    }
</style>
@endpush

@section('content')

{{-- ── FLASH MESSAGES ── --}}
@if (session('success'))
<div class="alert-success" style="margin-bottom:1rem; padding:.75rem 1rem;
    background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;
    border-radius:10px; font-size:13px; display:flex; align-items:center; gap:8px;">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

@if (session('error'))
<div style="margin-bottom:1rem; padding:.75rem 1rem;
    background:#fef2f2; color:#991b1b; border:1px solid #fecaca;
    border-radius:10px; font-size:13px; display:flex; align-items:center; gap:8px;">
    <i class="fas fa-circle-xmark"></i> {{ session('error') }}
</div>
@endif

@if(session('import_errors') && count(session('import_errors')) > 0)
<div style="margin-bottom:1rem; padding:.75rem 1rem;
    background:#fffbeb; color:#92400e; border:1px solid #fde68a;
    border-radius:10px; font-size:13px;">
    <div style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:6px">
        <i class="fas fa-triangle-exclamation"></i> Peringatan import:
    </div>
    <ul style="margin:0; padding-left:1.25rem">
        @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ── 1. UPLOAD HISTORICAL DATA ── --}}
<div class="table-card" style="margin-bottom:1.5rem">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-upload" style="color:var(--accent); margin-right:8px"></i>
                Upload Historical Data
            </div>
            <div class="table-subtitle">Import data harga historis dari file CSV atau Excel.</div>
        </div>
    </div>
    <div style="padding:1.5rem">
        <form method="POST" action="{{ route('prediksi.upload') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end">
                <div style="flex:1; min-width:260px">
                    <label class="form-label-admin">CSV/Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="file" accept=".csv,.xlsx,.xls"
                           class="form-input-admin" required
                           style="padding:6px 10px; cursor:pointer">
                    <span style="font-size:11.5px; color:var(--text-muted); margin-top:4px; display:block">
                        <i class="fas fa-info-circle" style="font-size:10px"></i>
                        Format kolom: tanggal,komoditas,satuan,harga_lama,harga_sekarang,selisih,persen
                    </span>
                </div>
                <div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── 2. GENERATE PREDICTION ── --}}
<div class="table-card" style="margin-bottom:1.5rem">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-wand-magic-sparkles" style="color:var(--accent); margin-right:8px"></i>
                Generate Prediction
            </div>
            <div class="table-subtitle">Pilih komoditas dan horizon prediksi, lalu jalankan model Holt-Winters.</div>
        </div>
    </div>
    <div style="padding:1.5rem">
        <form method="POST" action="{{ route('prediksi.generate') }}">
            @csrf
            <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end">

                <div style="flex:2; min-width:220px">
                    <label class="form-label-admin">Commodity <span class="text-danger">*</span></label>
                    <select name="commodity_id" class="form-select" required>
                        <option value="">Pilih Komoditas...</option>
                        @foreach($commodities as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:1; min-width:160px">
                    <label class="form-label-admin">Days <span class="text-danger">*</span></label>
                    <select name="steps" class="form-select" required>
                        <option value="7">7 Hari</option>
                        <option value="14">14 Hari</option>
                        <option value="30" selected>30 Hari</option>
                        <option value="60">60 Hari</option>
                        <option value="90">90 Hari</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn-primary" style="background:var(--success,#10b981)">
                        <i class="fas fa-wand-magic-sparkles"></i> Run Holt-Winters
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── 3. PREDICTION HISTORY ── --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-clock-rotate-left" style="color:var(--accent); margin-right:8px"></i>
                Prediction History
                <span style="font-size:13px; font-weight:500; color:var(--text-muted); margin-left:6px">
                    ({{ $predictions->total() }})
                </span>
            </div>
            <div class="table-subtitle">Riwayat semua prediksi yang telah di-generate.</div>
        </div>
    </div>

    @if($predictions->count() > 0)
    <table>
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
                    $metrics      = $pred->metrics ?? [];
                    $mape         = $metrics['mape'] ?? null;

                    if ($mape === null)    { $mapeClass = 'mape-muted'; }
                    elseif ($mape < 5)    { $mapeClass = 'mape-good'; }
                    elseif ($mape < 10)   { $mapeClass = 'mape-warn'; }
                    else                  { $mapeClass = 'mape-bad'; }
                @endphp
                <tr>
                    <td class="date-text">{{ $loop->iteration }}</td>
                    <td class="commodity-name">{{ $pred->commodity_name }}</td>
                    <td class="date-text">
                        {{ $pred->predicted_at ? $pred->predicted_at->format('d M Y H:i') : 'N/A' }}
                    </td>
                    <td class="date-text">{{ $pred->horizon_days ?? '-' }} days</td>
                    <td>
                        @if($mape !== null)
                            <span class="{{ $mapeClass }}">
                                {{ number_format($mape, 2) }}%
                            </span>
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
                        <div class="action-group">
                            <a href="{{ route('prediksi.show', $pred->id) }}" class="btn-action-edit">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('prediksi.export', $pred->id) }}"
                               class="btn-action-edit" style="background:#f0fdf4; color:#16a34a; border-color:#bbf7d0">
                                <i class="fas fa-download"></i> CSV
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $predictions->firstItem() }}–{{ $predictions->lastItem() }}
            of {{ $predictions->total() }} predictions
        </span>
        <x-pagination :paginator="$predictions" />
    </div>

    @else
    <div style="text-align:center; padding:3rem; color:var(--text-muted)">
        <i class="fas fa-chart-line" style="font-size:2.5rem; display:block; margin-bottom:1rem; opacity:.35"></i>
        <div style="font-weight:600; margin-bottom:4px">Belum ada prediksi</div>
        <div style="font-size:13px">Generate prediksi pertama menggunakan form di atas.</div>
    </div>
    @endif
</div>


<style>
.mape-good  { font-weight:600; color:#16a34a; }
.mape-warn  { font-weight:600; color:#d97706; }
.mape-bad   { font-weight:600; color:#dc2626; }
.mape-muted { font-weight:600; color:var(--text-muted); }
</style>

@endsection -->
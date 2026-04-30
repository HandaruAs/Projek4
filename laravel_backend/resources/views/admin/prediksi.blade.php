@extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
@section('page-sub', 'Generate commodity price predictions using machine learning models.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modal-warning.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/modal-warning.js') }}"></script>
@endpush

@section('content')

{{-- Banner sukses tetap muncul jika tidak ada warning --}}
@if(session('success'))
<div class="alert-box alert-success"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert-box alert-error"><i class="fas fa-circle-xmark"></i> {{ session('error') }}</div>
@endif

{{-- Warning sebagai popup modal --}}
@if(session('warning'))
<div id="warningModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-icon">⚠️</div>
        <div class="modal-title">Perhatian</div>
        <div class="modal-message">{{ session('warning') }}</div>
        <button class="btn-close-modal" onclick="document.getElementById('warningModal').remove()">Mengerti</button>
    </div>
</div>
@endif

{{-- Import error tetap menggunakan banner --}}
@if(session('import_errors') && count(session('import_errors')) > 0)
<div class="alert-warning">
    <div style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:6px">
        <i class="fas fa-triangle-exclamation"></i> Peringatan import:
    </div>
    <ul style="margin:0; padding-left:1.25rem">
        @foreach(session('import_errors') as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

{{-- ── UPLOAD HISTORICAL DATA ── --}}
<div class="table-card" style="margin-bottom:1.5rem">
    <div class="table-header">
        <div>
            <div class="table-title"><i class="fas fa-upload"></i> Upload Historical Data</div>
            <div class="table-subtitle">Import data harga historis dari file CSV atau Excel.</div>
        </div>
        <form method="POST" action="/admin/prediksi/upload" enctype="multipart/form-data">
            @csrf
            <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end">
                <div style="flex:1; min-width:260px">
                    <label class="form-label-admin">CSV/Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-input-admin" required>
                    <span style="font-size:11.5px; color:var(--text-muted); margin-top:4px; display:block">
                        Format kolom: tanggal,komoditas,satuan,harga_lama,harga_sekarang,selisih,persen
                    </span>
                </div>
                <div><button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Upload</button></div>
            </div>
        </form>
    </div>

{{-- ── GENERATE PREDICTION ── --}}
<div class="table-card" style="margin-bottom:1.5rem">
    <div class="table-header">
        <div>
            <div class="table-title"><i class="fas fa-wand-magic-sparkles"></i> Generate Prediction</div>
            <div class="table-subtitle">Pilih komoditas dan horizon prediksi, lalu jalankan model Holt-Winters.</div>
        </div>
        <form method="POST" action="/admin/prediksi/generate">
            @csrf
            <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end">
                <div style="flex:2; min-width:220px">
                    <label class="form-label-admin">Commodity <span class="text-danger">*</span></label>
                    <select name="komoditas" class="form-select" required>
                        <option value="">Pilih Komoditas...</option>
                        @foreach($commodities as $c)
                        <option value="{{ $c->id }}" {{ old('komoditas', $selectedNama) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1; min-width:160px">
                    <label class="form-label-admin">Days <span class="text-danger">*</span></label>
                    <select name="steps" class="form-select" required>
                        <option value="7" {{ old('steps', 30) == 7 ? 'selected' : '' }}>7 Hari</option>
                        <option value="14" {{ old('steps', 30) == 14 ? 'selected' : '' }}>14 Hari</option>
                        <option value="30" {{ old('steps', 30) == 30 ? 'selected' : '' }}>30 Hari</option>
                        <option value="60" {{ old('steps', 30) == 60 ? 'selected' : '' }}>60 Hari</option>
                        <option value="90" {{ old('steps', 30) == 90 ? 'selected' : '' }}>90 Hari</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-primary" style="background:var(--success,#10b981)">
                        <i class="fas fa-wand-magic-sparkles"></i> Run Holt-Winters
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-run-model">
                <i class="fas fa-wand-magic-sparkles"></i> Run Prediction Model
            </button>
        </form>
    </div>

</div>

{{-- ── PREDICTION HISTORY ── --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-clock-rotate-left"></i> Prediction History
                <span style="font-size:13px; font-weight:500; color:var(--text-muted); margin-left:6px">({{ $predictions->total() }})</span>
            </div>
            <div class="table-subtitle">Riwayat semua prediksi yang telah di-generate.</div>
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
            @foreach($predictions as $pred)
            @php
                $mape = $pred->accuracy_mape ?? null;
                if ($mape === null) { $mapeClass = 'mape-muted'; }
                elseif ($mape < 5) { $mapeClass = 'mape-good'; }
                elseif ($mape < 10) { $mapeClass = 'mape-warn'; }
                else { $mapeClass = 'mape-bad'; }
            @endphp
            <tr>
                <td class="date-text">{{ $predictions->firstItem() + $loop->index }}</td>
                <td class="commodity-name">{{ $pred->commodity_name }}</td>
                <td class="date-text">{{ $pred->created_at ? $pred->created_at->format('d M Y H:i') : 'N/A' }}</td>
                <td class="date-text">{{ $pred->steps ?? '-' }} days</td>
                <td class="date-text">
                    @if($pred->accuracy_mae !== null)
                        {{ number_format($pred->accuracy_mae, 0) }}
                    @else
                        <span style="cursor:help; border-bottom:1px dotted var(--text-muted)" title="Data historis tidak mencukupi">—</span>
                    @endif
                </td>
                <td class="date-text">
                    @if($pred->accuracy_rmse !== null)
                        {{ number_format($pred->accuracy_rmse, 0) }}
                    @else
                        <span style="cursor:help; border-bottom:1px dotted var(--text-muted)" title="Data historis tidak mencukupi">—</span>
                    @endif
                </td>
                <td>
                    @if($mape !== null)
                        <span class="{{ $mapeClass }}">{{ number_format($mape, 2) }}%</span>
                    @else
                        <span style="cursor:help; border-bottom:1px dotted var(--text-muted)" title="Data historis tidak mencukupi">—</span>
                    @endif
                </td>
                <td>
                    <span style="display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:600; background:#d1fae5; color:#065f46">
                        <i class="fas fa-circle" style="font-size:6px"></i> {{ $pred->status ?? 'Completed' }}
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
            Showing {{ $predictions->count() }} of {{ $predictions->total() }} results
        </span>
        <div class="pagination">
            {{ $predictions->links() }}
        </div>
    </div>
</div>

@endsection

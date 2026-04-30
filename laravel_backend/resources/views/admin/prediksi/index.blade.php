@extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi Harga Pangan')
@section('page-sub', 'Holt-Winters Exponential Smoothing • Admin Only')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line text-primary me-2"></i>
        Daftar Prediksi
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('prediksi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Generate Baru
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Komoditas</label>
                <select name="commodity" class="form-select">
                    <option value="">Semua Komoditas</option>
                    @foreach($commodities as $name)
                        <option value="{{ $name }}" {{ request('commodity') == $name ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="form-check">
                    <input class="btn btn-outline-secondary w-100" type="submit" value="Filter">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Stats --}}
@if($predictions->count() > 0)
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="text-primary">{{ $predictions->total() }}</h3>
                <small>Total Prediksi</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="text-success">{{ $predictions->where('status', 'completed')->count() }}</h3>
                <small>Berhasil</small>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Predictions Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Hasil Prediksi ({{ $predictions->total() }} total)</span>
        <div>
            @if($predictions->lastPage() > 1)
                {{ $predictions->appends(request()->query())->links() }}
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        @if($predictions->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="250">Komoditas</th>
                        <th width="100">Horizon</th>
                        <th width="160">Dibuat</th>
                        <th width="120">Akurasi</th>
                        <th width="140">Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($predictions as $pred)
                    @php
                        $mape = data_get($pred->metrics, 'mape');
                        $badgeClass = $mape <= 10 ? 'success' : ($mape <= 20 ? 'warning' : 'danger');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $pred->commodity_name }}</strong>
                            <br><small class="text-muted">{{ $pred->commodity?->category ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $pred->horizon_days }} hari</span>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($pred->created_at)->format('d M Y H:i') }}
                            <br><small>oleh {{ $pred->created_by ?? 'System' }}</small>
                        </td>
                        <td>
                            @if($mape)
                                <span class="badge bg-{{ $badgeClass }}">{{ number_format($mape,1) }}%</span>
                                <br><small>MAE: {{ number_format(data_get($pred->metrics,'mae'),0) }}</small>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $pred->status == 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($pred->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('prediksi.show', $pred->_id) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('prediksi.export', $pred->_id) }}" class="btn btn-outline-success">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('prediksi.destroy', $pred->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger" type="submit">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Belum ada prediksi</h5>
            <p class="text-muted mb-4">Generate prediksi pertama Anda!</p>
            <a href="{{ route('prediksi.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Generate Sekarang
            </a>
        </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
.badge { font-size: 0.75em; }
.table th { border-top: none; }
</style>
@endpush


@extends('layouts.layout')

@section('title', 'Generate Prediksi')
@section('page-title', 'Generate Prediksi')
@section('page-sub', 'Prediksi harga komoditas menggunakan model Holt-Winters Exponential Smoothing.')

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- TWO PANEL ROW --}}
<div class="prediksi-panels">

    {{-- PANEL 1: Import Data (tidak berubah dari sebelumnya) --}}
    <div class="panel-card">
        <div class="panel-step-badge">1</div>
        <div class="panel-header">
            <div class="panel-title">Import Historical Data</div>
            <div class="panel-sub">Upload data harga historis komoditas</div>
        </div>
        <form method="POST" action="/admin/harga/upload" enctype="multipart/form-data">
            @csrf
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-file-arrow-up upload-zone-icon"></i>
                <p class="upload-zone-text" id="uploadZoneText">Drop file Excel atau CSV di sini</p>
                <input type="file" id="fileInput" name="file" accept=".xlsx,.csv" hidden>
                <button type="button" class="btn-secondary"
                    onclick="document.getElementById('fileInput').click()">
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

    {{-- PANEL 2: Model Parameters Holt-Winters --}}
    <div class="panel-card">
        <div class="panel-step-badge">2</div>
        <div class="panel-header">
            <div class="panel-title">Model Parameters</div>
            <div class="panel-sub">Konfigurasi Holt-Winters Exponential Smoothing</div>
        </div>

        <form method="POST" action="{{ route('prediksi.generate') }}">
            @csrf
            <div class="param-grid">

                {{-- Commodity --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">COMMODITY FOCUS</label>
                    <select class="form-select" name="commodity_id" required>
                        <option value="">— Pilih Komoditas —</option>
                        @foreach($commodities as $c)
                            <option value="{{ $c->_id }}" {{ old('commodity_id') == $c->_id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('commodity_id')
                        <p class="text-danger small mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Horizon --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">PREDICTION HORIZON</label>
                    <select class="form-select" name="period" required>
                        <option value="7"  {{ old('period','30') == '7'  ? 'selected' : '' }}>7 Hari</option>
                        <option value="14" {{ old('period','30') == '14' ? 'selected' : '' }}>14 Hari</option>
                        <option value="30" {{ old('period','30') == '30' ? 'selected' : '' }} selected>30 Hari</option>
                        <option value="60" {{ old('period','30') == '60' ? 'selected' : '' }}>60 Hari</option>
                        <option value="90" {{ old('period','30') == '90' ? 'selected' : '' }}>90 Hari</option>
                    </select>
                </div>

                {{-- Trend Type --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">TREND TYPE</label>
                    <select class="form-select" name="trend">
                        <option value="add" selected>Additive</option>
                        <option value="mul">Multiplicative</option>
                        <option value="none">None</option>
                    </select>
                </div>

                {{-- Seasonal Type --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">SEASONAL TYPE</label>
                    <select class="form-select" name="seasonal">
                        <option value="add" selected>Additive</option>
                        <option value="mul">Multiplicative</option>
                        <option value="none">None</option>
                    </select>
                </div>

                {{-- Seasonal Periods --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">SEASONAL PERIODS</label>
                    <select class="form-select" name="seasonal_periods">
                        <option value="7" selected>7 (Weekly)</option>
                        <option value="12">12 (Monthly)</option>
                        <option value="30">30 (Monthly Days)</option>
                        <option value="365">365 (Yearly)</option>
                    </select>
                </div>

                {{-- Damped --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">DAMPED TREND</label>
                    <select class="form-select" name="damped">
                        <option value="0" selected>Tidak (Default)</option>
                        <option value="1">Ya (Damped)</option>
                    </select>
                </div>

            </div>

            {{-- Info box --}}
            <div class="hw-info-box">
                <i class="fas fa-circle-info"></i>
                <span>
                    <strong>Holt-Winters</strong> cocok untuk data dengan trend + musiman.
                    Gunakan <em>Additive</em> jika variasi musiman konstan,
                    <em>Multiplicative</em> jika variasi membesar seiring trend naik.
                </span>
            </div>

            <button type="submit" class="btn-run-model">
                <i class="fas fa-wand-magic-sparkles"></i> Run Holt-Winters Model
            </button>
        </form>
    </div>

</div>

{{-- PREDICTION HISTORY TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Prediction History</div>
            <div class="table-sub">Riwayat prediksi Holt-Winters</div>
        </div>
        <span class="view-all">{{ $predictions->total() }} total prediksi</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Commodity</th>
                <th>Horizon</th>
                <th>Trend / Seasonal</th>
                <th>MAE</th>
                <th>RMSE</th>
                <th>MAPE (%)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($predictions as $item)
            @php
                $metrics = $item->metrics ?? [];
                $status  = $metrics['status'] ?? 'completed';
                $badgeMap = [
                    'completed'     => 'badge-status-completed',
                    'review_needed' => 'badge-status-review',
                    'failed'        => 'badge-status-failed',
                ];
                $labelMap = [
                    'completed'     => 'COMPLETED',
                    'review_needed' => 'REVIEW NEEDED',
                    'failed'        => 'FAILED',
                ];
            @endphp
            <tr>
                <td class="date-text" style="white-space:nowrap">
                    {{ \Carbon\Carbon::parse($item->predicted_at)->format('d M Y') }}<br>
                    <span style="color:var(--muted)">
                        {{ \Carbon\Carbon::parse($item->predicted_at)->format('H:i') }}
                    </span>
                </td>
                <td class="commodity-name">{{ $item->commodity_name ?? '—' }}</td>
                <td class="date-text">{{ $item->horizon_days }} Hari</td>
                <td class="date-text" style="font-size:.72rem;white-space:nowrap">
                    T: <strong>{{ ucfirst($metrics['trend'] ?? 'add') }}</strong><br>
                    S: <strong>{{ ucfirst($metrics['seasonal'] ?? 'add') }}</strong>
                </td>
                <td class="date-text">
                    {{ isset($metrics['mae'])  ? number_format($metrics['mae'],  2) : '—' }}
                </td>
                <td class="date-text">
                    {{ isset($metrics['rmse']) ? number_format($metrics['rmse'], 2) : '—' }}
                </td>
                <td class="date-text">
                    {{ isset($metrics['mape']) ? number_format($metrics['mape'], 2).'%' : '—' }}
                </td>
                <td>
                    <span class="badge {{ $badgeMap[$status] ?? 'badge-status-completed' }}">
                        {{ $labelMap[$status] ?? strtoupper($status) }}
                    </span>
                </td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <a href="{{ route('prediksi.show', $item->_id) }}" class="pred-action-link">
                            <i class="fas fa-chart-line"></i> View
                        </a>
                        <form method="POST"
                            action="{{ route('prediksi.destroy', $item->_id) }}"
                            onsubmit="return confirm('Hapus prediksi ini?')"
                            style="margin:0">
                            @csrf @method('DELETE')
                            <button type="submit" class="pred-action-link retry"
                                style="background:none;border:none;padding:0;cursor:pointer">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state" style="text-align:center;padding:2rem;color:var(--muted)">
                        <i class="fas fa-clock-rotate-left" style="font-size:2rem;margin-bottom:.5rem;display:block"></i>
                        Belum ada riwayat prediksi
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $predictions->firstItem() }}–{{ $predictions->lastItem() }}
            of {{ $predictions->total() }} results
        </span>
        <div>
            {{ $predictions->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const fileInput      = document.getElementById('fileInput');
const uploadZoneText = document.getElementById('uploadZoneText');
const uploadZone     = document.getElementById('uploadZone');

fileInput?.addEventListener('change', function () {
    if (this.files.length > 0) {
        uploadZoneText.textContent = this.files[0].name;
        uploadZone.classList.add('has-file');
    }
});

['dragover','dragenter'].forEach(evt => {
    uploadZone?.addEventListener(evt, e => {
        e.preventDefault();
        uploadZone.classList.add('drag-over');
    });
});
uploadZone?.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone?.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        uploadZoneText.textContent = file.name;
        uploadZone.classList.add('has-file');
    }
});
</script>
@endpush
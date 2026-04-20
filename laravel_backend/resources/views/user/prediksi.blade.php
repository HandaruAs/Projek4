@extends('layouts.layout')

@section('title', 'Prediksi Harga')
@section('page-title', 'Prediksi Harga Komoditas')
@section('page-sub', 'Hasil prediksi harga komoditas menggunakan Holt-Winters Exponential Smoothing')

@section('content')

{{-- Filter Komoditas --}}
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>Pilih Komoditas
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('user.prediksi') }}" class="row g-3">
            <div class="col-md-6">
                <select name="commodity_id" class="form-select" onchange="this.form.submit()">
                    <option value="">— Semua Komoditas —</option>
                    @foreach($komoditasList as $item)
                        <option value="{{ $item['id'] }}" {{ $selectedId == $item['id'] ? 'selected' : '' }}>
                            {{ $item['nama'] }} ({{ $item['unit'] }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-search me-2"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

@if($prediksiData)
<div class="row g-4 mb-4">
    {{-- Prediction Summary --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">{{ $selectedKomoditas['nama'] ?? 'Komoditas' }}</h6>
            </div>
            <div class="card-body">
                @if($chartData)
                    <canvas id="prediksiChart" height="300"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Akurasi Model</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h5 fw-bold text-success">{{ $prediksiData['accuracy'] ?? 'N/A' }}%</div>
                            <small>Accuracy</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h6 fw-bold text-warning">{{ $prediksiData['mape'] ?? 'N/A' }}%</div>
                            <small>MAPE</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h6 fw-bold">{{ $prediksiData['recommendation'] ?? 'N/A' }}</div>
                            <small>Rekomendasi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@else
<div class="text-center py-5">
    <i class="fas fa-chart-line fa-4x text-muted mb-4"></i>
    <h5>Pilih komoditas untuk melihat prediksi</h5>
    <p class="text-muted">Generate prediksi di panel admin terlebih dahulu</p>
</div>
@endif

@endsection

@push('scripts')
@if($chartData)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('prediksiChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: [...{{ json_encode($chartData['hist_tanggal'] ?? []) }}, ...{{ json_encode($chartData['pred_tanggal'] ?? []) }}],
        datasets: [{
            label: 'Harga Aktual',
            data: {{ json_encode($chartData['hist_harga'] ?? []) }},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            pointRadius: 4,
            borderWidth: 2,
            fill: false
        }, {
            label: 'Prediksi',
            data: [null, null, {{ json_encode($chartData['pred_harga'] ?? []) }}],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            pointRadius: 4,
            borderDash: [5, 5],
            borderWidth: 2,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>
@endif
@endpush


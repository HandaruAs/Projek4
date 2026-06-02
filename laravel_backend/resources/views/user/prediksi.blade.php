{{--
  SIMOPANG — User Prediksi Harga Komoditas
  File : resources/views/user/prediksi.blade.php
  Data : dibaca dari MongoDB predictions (di-generate oleh admin)
         User TIDAK bisa generate — hanya melihat hasil admin.
--}}
@extends('layouts.layout')

@section('title', 'Prediksi Harga')
@section('page-title', 'Prediksi Harga')
@section('page-sub', 'Hasil prediksi harga komoditas menggunakan Holt-Winters Exponential Smoothing.')

@section('content')

{{-- ── FILTER KOMODITAS ── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body">
        <form method="GET" action="{{ route('user.prediksi') }}"
              style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <label style="font-size:11px;font-weight:700;letter-spacing:.05em;
                              color:var(--muted);display:block;margin-bottom:6px">
                    FILTER KOMODITAS
                </label>
                {{-- Dropdown berisi komoditas yang sudah di-generate admin --}}
                <select class="form-select" name="komoditas" onchange="this.form.submit()">
                    <option value="">— Semua Komoditas —</option>
                    @foreach($komoditasList as $nama)
                        <option value="{{ $nama }}"
                            {{ $selectedNama == $nama ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($selectedNama)
                <a href="{{ route('user.prediksi') }}"
                   style="border-radius:8px;white-space:nowrap">
                </a>
            @endif
        </form>
    </div>
</div>

{{-- ── STAT CARDS (tampil jika ada prediksi terpilih) ── --}}
@if($prediction)
@php
    $payload   = $prediction->payload ?? [];
    $acc       = $payload['accuracy'] ?? [];
    $hargaKini = $payload['harga_terakhir'] ?? 0;
    $satuan    = $payload['satuan'] ?? 'kg';
@endphp
<div class="u-pred-stat-row" style="margin-bottom:1.5rem">

    <div class="u-pred-stat-card u-pred-stat-card--blue">
        <div class="u-pred-stat-card__label">Estimasi Harga (30 Hari)</div>
        <div class="u-pred-stat-card__value">
            Rp {{ number_format($estimasiHarga ?? 0, 0, ',', '.') }}
            <span class="u-pred-stat-card__unit">/{{ $satuan }}</span>
        </div>
        <div class="u-pred-stat-card__note">
            Harga saat ini: Rp {{ number_format($hargaKini, 0, ',', '.') }}
        </div>
    </div>

    <div class="u-pred-stat-card u-pred-stat-card--rose">
        <div class="u-pred-stat-card__label">Tren Prediksi (30 Hari)</div>
        <div class="u-pred-stat-card__value {{ ($trenPersen ?? 0) >= 0 ? 'u-pred-stat-card__value--up' : 'u-pred-stat-card__value--down' }}">
            {{ ($trenPersen ?? 0) >= 0 ? '+' : '' }}{{ $trenPersen ?? '0' }}%
        </div>
        <div class="u-pred-stat-card__sub">
            {{ ($trenPersen ?? 0) >= 0 ? 'Harga diprediksi cenderung naik.' : 'Harga diprediksi cenderung turun.' }}
        </div>
    </div>

    <div class="u-pred-stat-card u-pred-stat-card--blue">
        <div class="u-pred-stat-card__label">Tingkat Kepercayaan AI</div>
        <div class="u-pred-stat-card__value u-pred-stat-card__value--conf">
            {{ $kepercayaan ? number_format($kepercayaan, 1).'%' : '—' }}
        </div>
        @if($kepercayaan)
        <div class="u-conf-bar-wrap">
            <div class="u-conf-bar">
                <div class="u-conf-bar__fill" style="width:{{ min($kepercayaan, 100) }}%"></div>
            </div>
        </div>
        @endif
        <div class="u-pred-stat-card__sub">
            MAPE: {{ $prediction->accuracy_mape !== null ? number_format($prediction->accuracy_mape, 2).'%' : '—' }}
        </div>
    </div>

</div>

{{-- ── TABEL MINGGUAN ── --}}
<div class="u-table-card" style="margin-bottom:1.5rem">
    <div class="u-table-card__header">
        <div class="u-table-card__title">
            Detail Prediksi Mingguan — {{ $selectedNama }}
            <small style="font-weight:400;color:var(--muted);font-size:12px">
                (Generate: {{ $prediction->created_at?->format('d M Y H:i') }})
            </small>
        </div>
    </div>
    <div class="u-table-wrap">
        <table class="u-table">
            <thead>
                <tr>
                    <th>Minggu</th>
                    <th>Periode</th>
                    <th>Estimasi Harga Rata-rata</th>
                    <th>Perubahan vs Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prediksiMingguan as $row)
                <tr>
                    <td class="u-pred-week">{{ $row['minggu'] }}</td>
                    <td class="u-table__date">{{ $row['periode'] }}</td>
                    <td class="u-table__harga">
                        Rp {{ number_format($row['estimasi'], 0, ',', '.') }}
                    </td>
                    <td>
                        <span style="font-weight:600;
                            color:{{ ($row['delta_pct'] ?? 0) >= 0 ? '#ef4444' : '#10b981' }}">
                            {{ ($row['delta_pct'] ?? 0) >= 0 ? '+' : '' }}{{ $row['delta_pct'] ?? 0 }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:2rem;color:var(--muted)">
                        Tidak ada data prediksi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else
{{-- Belum pilih komoditas atau belum ada prediksi --}}
<div style="text-align:center;padding:3rem;color:var(--muted)">
    <i class="fas fa-chart-line" style="font-size:2.5rem;display:block;margin-bottom:1rem;opacity:.3"></i>
    <div style="font-weight:600;margin-bottom:4px">
        {{ count($komoditasList) > 0 ? 'Pilih komoditas untuk melihat prediksi' : 'Belum ada prediksi' }}
    </div>
    <div style="font-size:13px">
        {{ count($komoditasList) > 0
            ? 'Gunakan filter di atas untuk memilih komoditas.'
            : 'Admin belum melakukan generate prediksi. Silakan hubungi administrator.' }}
    </div>
</div>
@endif

{{-- ── TABEL SEMUA RIWAYAT PREDIKSI (seluruh komoditas) ── --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">
                <i class="fas fa-clock-rotate-left" style="color:var(--accent);margin-right:8px"></i>
                Semua Riwayat Prediksi
            </div>
            <div class="table-sub">Data prediksi yang telah di-generate oleh admin</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Komoditas</th>
                <th>Generated</th>
                <th>Days</th>
                <th>MAE</th>
                <th>RMSE</th>
                <th>MAPE</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            {{-- $prediction (singular) adalah prediksi terpilih dari filter --}}
            {{-- Untuk tabel semua riwayat kita query ulang semua --}}
            @php
                $allPredictions = \App\Models\Prediction::orderBy('created_at', 'desc')->get();
            @endphp
            @forelse($allPredictions as $i => $item)
            @php
                $mape      = $item->accuracy_mape;
                $mapeClass = $mape === null ? 'mape-muted'
                           : ($mape < 5 ? 'mape-good' : ($mape < 10 ? 'mape-warn' : 'mape-bad'));
            @endphp
            <tr>
                <td class="date-text">{{ $i + 1 }}</td>
                <td class="commodity-name">{{ $item->commodity_name }}</td>
                <td class="date-text">
                    {{ $item->created_at?->format('d M Y H:i') ?? '—' }}
                </td>
                <td class="date-text">{{ $item->steps ?? '—' }} days</td>
                <td class="date-text">
                    {{ $item->accuracy_mae !== null ? number_format($item->accuracy_mae, 0) : '—' }}
                </td>
                <td class="date-text">
                    {{ $item->accuracy_rmse !== null ? number_format($item->accuracy_rmse, 0) : '—' }}
                </td>
                <td>
                    @if($mape !== null)
                        <span class="{{ $mapeClass }}">{{ number_format($mape, 2) }}%</span>
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;
                                 padding:3px 10px;border-radius:20px;font-size:11.5px;
                                 font-weight:600;background:#d1fae5;color:#065f46">
                        <i class="fas fa-circle" style="font-size:6px"></i>
                        {{ ucfirst($item->status ?? 'completed') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;padding:2rem;color:var(--muted)">
                    <i class="fas fa-clock-rotate-left" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
                    Belum ada riwayat prediksi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('styles')
<style>
.mape-good  { font-weight:600; color:#16a34a; }
.mape-warn  { font-weight:600; color:#d97706; }
.mape-bad   { font-weight:600; color:#dc2626; }
.mape-muted { font-weight:600; color:var(--muted,#9ca3af); }
</style>
@endpush

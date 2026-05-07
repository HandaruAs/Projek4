@extends('layouts.layout')

@section('title', 'Data Harga')
@section('page-title', 'Data Harga')
@section('page-sub', 'Monitor and manage commodity price data from all registered regions.')

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Price Records</div>
            <div class="stat-value">{{ number_format($totalRecords) }}</div>
            <div class="stat-change up">
                <i class="fas fa-database"></i>
                <span class="stat-change-sub">history + prediksi</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-database"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Records Today</div>
            <div class="stat-value">{{ number_format($todayRecords) }}</div>
            <div class="stat-change up">
                <i class="fas fa-calendar-day"></i>
                <span class="stat-change-sub">today</span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-calendar-day"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Commodities</div>
            <div class="stat-value">{{ number_format($totalKomoditas) }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span class="stat-change-sub">registered</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-boxes-stacked"></i></div>
    </div>
</div>

{{-- FILTER BAR --}}
<form method="GET" action="{{ url('/admin/harga') }}">
    <x-filter-bar
        placeholder="Search commodity name..."
        :categories="$categories"
        :with-date="true"
        search-id="searchInput"
        category-id="categoryFilter"
        date-id="dateFilter">
    </x-filter-bar>
</form>

{{-- PRICE TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Price History</div>
            <div class="table-subtitle">
                Complete commodity price records from all monitored regions.
                @if(!request('search') && !request('category') && !request('date'))
                    <span style="color: #3b82f6; font-weight: 600;">
                        — {{ $predictionRows->count() }} baris teratas adalah data prediksi terbaru.
                    </span>
                @endif
            </div>
        </div>
    </div>

    <table id="hargaTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Commodity</th>
                <th>Category</th>
                <th>Price (IDR)</th>
                <th>Satuan</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>

            {{-- ── Baris Prediksi (hanya tampil jika tidak ada filter aktif) ── --}}
            @if(!request('search') && !request('category') && !request('date'))
                @foreach($predictionRows as $pred)
                <tr style="background: rgba(59,130,246,0.04); border-left: 3px solid #3b82f6;">
                    <td class="date-text">—</td>
                    <td class="commodity-name">{{ $pred->commodity_name }}</td>
                    <td>
                        <span class="region-text">{{ $pred->category }}</span>
                    </td>
                    <td class="price-text">
                        Rp {{ number_format($pred->harga_sekarang, 0, ',', '.') }}
                    </td>
                    <td>
                        <span class="region-text">{{ $pred->satuan }}</span>
                    </td>
                    <td class="date-text">
                        {{ $pred->date ? \Carbon\Carbon::parse($pred->date)->format('M d, Y') : '-' }}
                    </td>
                    <td>
                        <span style="font-size:11px; padding:3px 10px;
                                     background:#eff6ff; color:#3b82f6;
                                     border-radius:999px; font-weight:600;">
                            Prediksi
                        </span>
                    </td>
                </tr>
                @endforeach
            @endif

            {{-- ── Baris Price History ── --}}
            @forelse($hargaList as $index => $item)
            <tr>
                <td class="date-text">{{ $hargaList->firstItem() + $index }}</td>
                <td class="commodity-name">{{ $item->commodity_name ?? '-' }}</td>
                <td>
                    @if(!empty($item->category))
                        <span class="region-text">{{ $item->category }}</span>
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>
                <td class="price-text">
                    Rp {{ number_format($item->harga_sekarang, 0, ',', '.') }}
                </td>
                <td>
                    @if(!empty($item->satuan))
                        <span class="region-text">{{ $item->satuan }}</span>
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>
                <td class="date-text">
                    {{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}
                </td>
                <td>
                    <span style="font-size:11px; padding:3px 10px;
                                 background:#f0fdf4; color:#16a34a;
                                 border-radius:999px; font-weight:600;">
                        Historis
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted)">
                    Belum ada data harga.
                </td>
            </tr>
            @endforelse

        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $hargaList->firstItem() }}–{{ $hargaList->lastItem() }}
            of {{ number_format($hargaList->total()) }} records
            @if(!request('search') && !request('category') && !request('date'))
                + {{ $predictionRows->count() }} prediksi
            @endif
        </span>
        <x-pagination :paginator="$hargaList" />
    </div>
</div>

@endsection

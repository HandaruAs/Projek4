@extends('layouts.layout')

@section('title', 'Data Harga Komoditas')
@section('page-title', 'Data Harga Komoditas')
@section('page-sub', 'Informasi transparan harga pasar harian untuk berbagai komoditas pangan utama.')

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Data Harga</div>
            <div class="stat-value">{{ number_format($totalRecords) }}</div>
            <div class="stat-change up">
                <i class="fas fa-database"></i>
                <span class="stat-change-sub">
                   {{ request('searchInput') || request('categoryFilter') || request('dateFilter') ? 'hasil filter' : 'all time' }}
                </span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-database"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Data Hari Ini</div>
            <div class="stat-value">{{ number_format($todayRecords) }}</div>
            <div class="stat-change up">
                <i class="fas fa-calendar-day"></i>
                <span class="stat-change-sub">{{ now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-calendar-day"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Komoditas</div>
            <div class="stat-value">{{ number_format($totalKomoditas) }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span class="stat-change-sub">
                    {{ request('searchInput') || request('categoryFilter') || request('dateFilter') ? 'hasil filter' : 'terdaftar' }}
                </span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-boxes-stacked"></i></div>
    </div>
</div>

{{-- FILTER BAR --}}
<form method="GET" action="{{ url('/harga') }}">
    <x-filter-bar
        placeholder="Cari nama komoditas..."
        :categories="$categoryList"
        :withDate="true"
        searchId="searchInput"
        categoryId="categoryFilter"
        dateId="dateFilter">

        <button type="submit" class="u-btn-filter">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Terapkan Filter
        </button>
    </x-filter-bar>
</form>

{{-- PRICE TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Riwayat Harga</div>
            <div class="table-subtitle">Data lengkap harga komoditas dari semua kategori.</div>
        </div>
    </div>

    <table id="hargaTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Komoditas</th>
                <th>Kategori</th>
                <th>Harga (Rp)</th>
                <th>Satuan</th>
                <th>Tren</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
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

                <td>
                    @php $selisih = $item->selisih ?? 0; @endphp
                    @if($selisih > 0)
                        <span style="color:#16a34a;font-weight:600;font-size:13px">
                            ↑ +Rp {{ number_format($selisih, 0, ',', '.') }}
                        </span>
                    @elseif($selisih < 0)
                        <span style="color:#dc2626;font-weight:600;font-size:13px">
                            ↓ Rp {{ number_format($selisih, 0, ',', '.') }}
                        </span>
                    @else
                        <span style="color:var(--text-muted);font-size:13px">→ 0</span>
                    @endif
                </td>

                <td class="date-text">
                    {{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}
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
            Menampilkan {{ $hargaList->firstItem() ?? 0 }} - {{ $hargaList->lastItem() ?? 0 }}
            dari {{ $hargaList->total() ?? 0 }} data
        </span>
        <div class="table-actions" style="gap:8px;">
            {{ $hargaList->links('components.pagination') }}
        </div>
    </div>
</div>

@endsection
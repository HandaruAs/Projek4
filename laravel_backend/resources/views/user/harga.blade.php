@extends('layouts.layout')

@section('title', 'Data Harga Komoditas')
@section('page-title', 'Data Harga Komoditas')
@section('page-sub', 'Informasi transparan harga pasar harian untuk berbagai komoditas pangan utama.')

@section('content')

{{-- ============================================================
     STAT CARDS
     ============================================================ --}}
<div class="stats-grid">

    {{-- Card 1: Rata-rata Harga Hari Ini --}}
    <div class="stat-card">
        <div>
            <div class="stat-label">Rata-rata Harga Hari Ini</div>
            <div class="stat-value">
                {{ $avgHargaHariIni ? 'Rp ' . number_format($avgHargaHariIni, 0, ',', '.') : '—' }}
            </div>
            <div class="stat-change neutral">
                <i class="fas fa-chart-line"></i>
                <span class="stat-change-sub">
                    @if(request('search') && request('category') && request('category') !== 'Semua')
                        "{{ request('search') }}" &bull; {{ request('category') }} &bull; {{ now()->translatedFormat('d F Y') }}
                    @elseif(request('search'))
                        "{{ request('search') }}" &bull; {{ now()->translatedFormat('d F Y') }}
                    @elseif(request('category') && request('category') !== 'Semua')
                        {{ request('category') }} &bull; {{ now()->translatedFormat('d F Y') }}
                    @else
                        {{ now()->translatedFormat('d F Y') }}
                    @endif
                </span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-chart-line"></i></div>
    </div>

    {{-- Card 2: Naik Tertinggi Hari Ini --}}
    <div class="stat-card">
        <div>
            <div class="stat-label">Naik Tertinggi Hari Ini</div>
            <div class="stat-value" style="font-size: 1.15rem; line-height: 1.3;">
                {{ $naikTertinggi->commodity_name ?? '—' }}
            </div>
            <div class="stat-change up">
                <i class="fas fa-arrow-trend-up"></i>
                <span class="stat-change-sub">
                    @if($naikTertinggi)
                        +Rp {{ number_format($naikTertinggi->selisih, 0, ',', '.') }}
                        &nbsp;({{ number_format($naikTertinggi->persen, 1) }}%)
                    @else
                        tidak ada data hari ini
                    @endif
                </span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-arrow-trend-up"></i></div>
    </div>

    {{-- Card 3: Total Komoditas Terpantau --}}
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Komoditas</div>
            <div class="stat-value">{{ number_format($totalKomoditas) }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-boxes-stacked"></i>
                <span class="stat-change-sub">
                    @if(request('search') && request('date'))
                        "{{ request('search') }}" &bull; {{ \Carbon\Carbon::parse(request('date'))->translatedFormat('d F Y') }}
                    @elseif(request('search') && request('category') && request('category') !== 'Semua')
                        "{{ request('search') }}" &bull; {{ request('category') }}
                    @elseif(request('search'))
                        pencarian: "{{ request('search') }}"
                    @elseif(request('category') && request('category') !== 'Semua' && request('date'))
                        {{ request('category') }} &bull; {{ \Carbon\Carbon::parse(request('date'))->translatedFormat('d F Y') }}
                    @elseif(request('category') && request('category') !== 'Semua')
                        kategori: {{ request('category') }}
                    @elseif(request('date'))
                        {{ \Carbon\Carbon::parse(request('date'))->translatedFormat('d F Y') }}
                    @else
                        semua komoditas terpantau
                    @endif
                </span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-boxes-stacked"></i></div>
    </div>

</div>

{{-- ============================================================
     FILTER BAR
     ============================================================ --}}
<form method="GET" action="{{ url('/harga') }}">
    <x-filter-bar
        placeholder="Cari nama komoditas..."
        :categories="$categoryList"
        :withDate="true">

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

{{-- ============================================================
     PRICE TABLE
     ============================================================ --}}
<div class="table-card">
    <div class="table-header">
    <div>
        <div class="table-title">Riwayat Harga</div>
        <div class="table-subtitle">
            Data lengkap harga komoditas
            @if(request('search') || (request('category') && request('category') !== 'Semua') || request('date'))
                &mdash; hasil filter:
                @if(request('search'))
                    <strong>"{{ request('search') }}"</strong>
                @endif
                @if(request('category') && request('category') !== 'Semua')
                    &bull; <strong>{{ request('category') }}</strong>
                @endif
                @if(request('date'))
                    &bull; <strong>{{ \Carbon\Carbon::parse(request('date'))->translatedFormat('d F Y') }}</strong>
                @endif
            @else
                dari semua kategori.
            @endif
        </div>
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
                        <span style="color:#16a34a; font-weight:600; font-size:13px;">
                            ↑ +Rp {{ number_format($selisih, 0, ',', '.') }}
                        </span>
                    @elseif($selisih < 0)
                        <span style="color:#dc2626; font-weight:600; font-size:13px;">
                            ↓ Rp {{ number_format($selisih, 0, ',', '.') }}
                        </span>
                    @else
                        <span style="color:var(--text-muted); font-size:13px;">→ 0</span>
                    @endif
                </td>
                <td class="date-text">
                    {{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">
                    @if(request('search') || request('category') || request('date'))
                        Tidak ada data yang cocok dengan filter yang dipilih.
                    @else
                        Belum ada data harga.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            @if($hargaList->total() > 0)
                Menampilkan {{ $hargaList->firstItem() }} – {{ $hargaList->lastItem() }}
                dari {{ $hargaList->total() }} data
            @else
                Tidak ada data ditemukan
            @endif
        </span>
        <div class="table-actions" style="gap:8px;">
            {{ $hargaList->links('components.pagination') }}
        </div>
    </div>
</div>

@endsection